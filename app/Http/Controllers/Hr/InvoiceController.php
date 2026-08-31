<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\PaymentSchedule;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with([
            'quotation.request',
            'items',
            'creator',
            'paymentSchedules',
            'payments',
        ]);

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                    ->orWhereHas('quotation.request', function ($requestQuery) use ($search) {
                        $requestQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('service_type', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('invoice_date_from')) {
            $query->whereDate('invoice_date', '>=', $request->invoice_date_from);
        }

        if ($request->filled('invoice_date_to')) {
            $query->whereDate('invoice_date', '<=', $request->invoice_date_to);
        }

        $summaryInvoices = (clone $query)
            ->with(['payments'])
            ->get();

        $summary = [
            'total_invoices' => $summaryInvoices->count(),
            'total_amount' => $summaryInvoices->sum(fn ($invoice) => (float) $invoice->total_amount),
            'total_paid' => $summaryInvoices->sum(fn ($invoice) => (float) $invoice->paid_amount),
            'total_remaining' => $summaryInvoices->sum(fn ($invoice) => (float) $invoice->remaining_balance),
            'paid_invoices' => $summaryInvoices->filter(function ($invoice) {
                return strtolower((string) $invoice->status) === 'paid'
                    || (
                        (float) $invoice->total_amount > 0
                        && $invoice->paid_amount >= (float) $invoice->total_amount
                    );
            })->count(),
        ];

        $invoices = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('hr.invoices.index', compact('invoices', 'summary'));
    }

    public function create(Quotation $quotation)
    {
        $quotation->load(['request', 'items', 'invoice']);

        if ($quotation->invoice) {
            return redirect()
                ->route('hr.invoices.show', $quotation->invoice)
                ->with('info', 'An invoice already exists for this quotation.');
        }

        return view('hr.invoices.create', compact('quotation'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'quotation_id' => ['required', 'exists:quotations,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'description' => ['nullable', 'string'],
        ]);

        $quotation = Quotation::with(['request', 'items', 'invoice'])
            ->findOrFail($validated['quotation_id']);

        if ($quotation->invoice()->exists()) {
            return back()->withErrors([
                'quotation_id' => 'This quotation already has an invoice.',
            ])->withInput();
        }

        if (!$quotation->items()->exists()) {
            return back()->withErrors([
                'quotation_id' => 'This quotation has no items to invoice.',
            ])->withInput();
        }

        $invoice = null;

        DB::transaction(function () use ($validated, $quotation, &$invoice) {
            $quotation->load('items');

            $invoice = Invoice::create([
                'quotation_id' => $quotation->id,
                'invoice_no' => $this->generateInvoiceNumber(),
                'status' => 'unpaid',
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? null,
                'description' => $validated['description'] ?? null,
                'total_amount' => (float) ($quotation->grand_total ?? 0),
                'created_by' => Auth::id(),
            ]);

            foreach ($quotation->items as $item) {
                $invoice->items()->create([
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                ]);
            }

            $this->generatePaymentSchedules($invoice, $quotation);
        });

        return redirect()
            ->route('hr.invoices.show', $invoice)
            ->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load([
            'quotation.request',
            'items',
            'creator',
            'paymentSchedules',
            'payments.paymentSchedule',
            'payments.receipt',
        ]);

        $invoice->syncPaymentStatus();

        $invoice->refresh()->load([
            'quotation.request',
            'items',
            'creator',
            'paymentSchedules',
            'payments.paymentSchedule',
            'payments.receipt',
        ]);

        return view('hr.invoices.show', compact('invoice'));
    }

    public function billingStatement(Request $request, Invoice $invoice)
    {
        $invoice->load([
            'quotation.request',
            'quotation.items',
            'items',
            'creator',
            'paymentSchedules',
            'payments.paymentSchedule',
        ]);

        $scheduleId = $request->integer('schedule');

        $selectedSchedule = $scheduleId
            ? $invoice->paymentSchedules->firstWhere('id', $scheduleId)
            : null;

        if (!$selectedSchedule) {
            $selectedSchedule = $invoice->paymentSchedules
                ->first(fn ($schedule) => strtolower((string) $schedule->status) !== 'paid');
        }

        if (!$selectedSchedule) {
            $selectedSchedule = $invoice->paymentSchedules->last();
        }

        abort_if(!$selectedSchedule, 404, 'No billing schedule is available.');

        if (!$selectedSchedule->is_ready_for_billing) {
            return redirect()
                ->route('hr.invoices.show', $invoice)
                ->withErrors([
                    'schedule' => "{$selectedSchedule->label} is not yet ready for billing.",
                ]);
        }

        $previousSchedules = $invoice->paymentSchedules
            ->filter(fn ($schedule) =>
                (int) $schedule->sort_order < (int) $selectedSchedule->sort_order
            );

        $previousBilledAmount = round(
            (float) $previousSchedules->sum('amount_due'),
            2
        );

        $previousPaidAmount = round(
            (float) $invoice->payments
                ->where('status', 'confirmed')
                ->filter(function ($payment) use ($selectedSchedule) {
                    if (!$payment->paymentSchedule) {
                        return true;
                    }

                    return (int) $payment->paymentSchedule->sort_order
                        < (int) $selectedSchedule->sort_order;
                })
                ->sum('amount'),
            2
        );

        $currentBillingAmount = round(
            (float) $selectedSchedule->amount_due,
            2
        );

        $currentPaidAmount = round(
            (float) $selectedSchedule->amount_paid,
            2
        );

        $currentRemainingAmount = round(
            max(0, $currentBillingAmount - $currentPaidAmount),
            2
        );

        $currentPhasePercent = round(
            (float) $selectedSchedule->percent,
            2
        );

        $cumulativePercent = round(
            (float) $invoice->paymentSchedules
                ->filter(fn ($schedule) =>
                    (int) $schedule->sort_order <= (int) $selectedSchedule->sort_order
                )
                ->sum('percent'),
            2
        );

        $cumulativeBilledAmount = round(
            (float) $invoice->paymentSchedules
                ->filter(fn ($schedule) =>
                    (int) $schedule->sort_order <= (int) $selectedSchedule->sort_order
                )
                ->sum('amount_due'),
            2
        );

        $retentionSchedule = $invoice->paymentSchedules
            ->first(fn ($schedule) =>
                str_contains(strtolower((string) $schedule->label), 'retention')
            );

        $retentionAmount = $retentionSchedule
            ? round((float) $retentionSchedule->amount_due, 2)
            : 0;

        return view('hr.invoices.billing-statement', compact(
            'invoice',
            'selectedSchedule',
            'previousBilledAmount',
            'previousPaidAmount',
            'currentBillingAmount',
            'currentPaidAmount',
            'currentRemainingAmount',
            'currentPhasePercent',
            'cumulativePercent',
            'cumulativeBilledAmount',
            'retentionAmount'
        ));
    }

public function markScheduleReady(
    Request $request,
    Invoice $invoice,
    PaymentSchedule $paymentSchedule
) {
    if ((int) $paymentSchedule->invoice_id !== (int) $invoice->id) {
        abort(404);
    }

    $validated = $request->validate([
        'milestone_notes' => ['nullable', 'string', 'max:1000'],
    ]);

    if (
        in_array(
            $paymentSchedule->milestone_status,
            ['ready_for_billing', 'billed', 'paid'],
            true
        )
    ) {
        return back()->with(
            'info',
            'This payment phase has already been processed.'
        );
    }

    $previousSchedule = $invoice->paymentSchedules()
        ->where('sort_order', '<', $paymentSchedule->sort_order)
        ->orderByDesc('sort_order')
        ->first();

    if ($previousSchedule && $previousSchedule->status !== 'paid') {
        return back()->withErrors([
            'schedule' =>
                "Complete payment for {$previousSchedule->label} before enabling {$paymentSchedule->label}.",
        ]);
    }

    $isAlreadyPaid = $paymentSchedule->status === 'paid';

    $paymentSchedule->update([
        'milestone_status' => $isAlreadyPaid
            ? 'paid'
            : 'ready_for_billing',

        'ready_for_billing_at' => now(),

        'ready_for_billing_by' => Auth::id(),

        'milestone_notes' =>
            $validated['milestone_notes']
            ?? (
                $isAlreadyPaid
                    ? "Existing paid phase synchronized as completed."
                    : "Milestone confirmed for {$paymentSchedule->label}."
            ),
    ]);

    $message = $isAlreadyPaid
        ? "{$paymentSchedule->label} was synchronized as completed and paid."
        : "{$paymentSchedule->label} is now ready for billing.";

    return back()->with('success', $message);
}

    protected function generateInvoiceNumber(): string
    {
        $nextId = (Invoice::max('id') ?? 0) + 1;

        return 'INV-' . now()->format('Y') . '-' .
            str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
    }

    protected function generatePaymentSchedules(
        Invoice $invoice,
        Quotation $quotation
    ): void {
        $paymentTerms = $quotation->payment_terms_json ?? [];

        if (
            empty($paymentTerms['phases'])
            || !is_array($paymentTerms['phases'])
        ) {
            return;
        }

        $invoiceDate = $invoice->invoice_date
            ? \Carbon\Carbon::parse($invoice->invoice_date)
            : now();

        foreach ($paymentTerms['phases'] as $index => $phase) {
            $phaseLabel = $phase['label'] ?? ('Phase ' . ($index + 1));

            $isInitialBillingPhase = $index === 0
                || str_contains(strtolower($phaseLabel), 'downpayment')
                || str_contains(strtolower($phaseLabel), 'full payment');

            $invoice->paymentSchedules()->create([
                'label' => $phaseLabel,
                'percent' => (float) ($phase['percent'] ?? 0),
                'amount_due' => (float) ($phase['amount'] ?? 0),
                'amount_paid' => 0,
                'due_date' => $invoiceDate
                    ->copy()
                    ->addDays($index * 15)
                    ->toDateString(),
                'status' => 'pending',
                'milestone_status' => $isInitialBillingPhase
                    ? 'ready_for_billing'
                    : 'not_ready',
                'ready_for_billing_at' => $isInitialBillingPhase ? now() : null,
                'ready_for_billing_by' => $isInitialBillingPhase ? Auth::id() : null,
                'milestone_notes' => $isInitialBillingPhase
                    ? (
                        str_contains(strtolower($phaseLabel), 'full payment')
                            ? 'Full payment schedule ready for billing.'
                            : 'Initial downpayment phase.'
                    )
                    : null,
                'notes' => null,
                'sort_order' => $index + 1,
            ]);
        }
    }
}