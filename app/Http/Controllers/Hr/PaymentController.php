<?php
namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentSchedule;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $paidSubquery = "(select coalesce(sum(amount), 0) from payments where payments.invoice_id = invoices.id and payments.status = 'confirmed')";

        $invoiceQuery = Invoice::with([
            'quotation.request',
            'paymentSchedules',
            'payments' => function ($query) {
                $query->latest('payment_date')->latest('id');
            },
            'payments.paymentSchedule',
            'payments.receiver',
            'payments.receipt',
        ]);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $invoiceQuery->where(function ($query) use ($search) {
                $query->where('invoice_no', 'like', "%{$search}%")
                    ->orWhereHas('payments', function ($paymentQuery) use ($search) {
                        $paymentQuery->where('payment_no', 'like', "%{$search}%")
                            ->orWhere('reference_number', 'like', "%{$search}%");
                    })
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
            $invoiceQuery->whereHas('payments', function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            });
        }

        if ($request->filled('method')) {
            $invoiceQuery->whereHas('payments', function ($query) use ($request) {
                $query->where('payment_method', $request->input('method'));
            });
        }

        if ($request->filled('schedule')) {
            $schedule = trim((string) $request->input('schedule'));

            $invoiceQuery->where(function ($query) use ($schedule) {
                $query->whereHas('payments', function ($paymentQuery) use ($schedule) {
                    $paymentQuery->where('payment_type', 'like', "%{$schedule}%");
                })->orWhereHas('paymentSchedules', function ($scheduleQuery) use ($schedule) {
                    $scheduleQuery->where('label', 'like', "%{$schedule}%");
                });
            });
        }

        if ($request->filled('date_from')) {
            $invoiceQuery->whereHas('payments', function ($query) use ($request) {
                $query->whereDate('payment_date', '>=', $request->input('date_from'));
            });
        }

        if ($request->filled('date_to')) {
            $invoiceQuery->whereHas('payments', function ($query) use ($request) {
                $query->whereDate('payment_date', '<=', $request->input('date_to'));
            });
        }

        $tab = $request->input('tab', 'all');

        if ($tab === 'needs_payment') {
            $invoiceQuery->whereRaw("$paidSubquery <= 0");
        } elseif ($tab === 'partial') {
            $invoiceQuery->whereRaw("$paidSubquery > 0 and $paidSubquery < invoices.total_amount");
        } elseif ($tab === 'fully_paid') {
            $invoiceQuery->whereRaw("$paidSubquery >= invoices.total_amount and invoices.total_amount > 0");
        }

        $matchingInvoices = $invoiceQuery
            ->latest('updated_at')
            ->get();

$clientGroups = $matchingInvoices
    ->groupBy(function (Invoice $invoice) {
        return 'invoice-' . $invoice->id;
    })
            ->map(function ($invoices, $groupKey) {
                $firstInvoice = $invoices->first();
                $requestData = $firstInvoice?->quotation?->request;

                $clientName = $requestData?->full_name
                    ?? trim(
                        ($requestData?->first_name ?? '') . ' ' .
                        ($requestData?->last_name ?? '')
                    )
                    ?: 'Unknown Client';

                $payments = $invoices
                    ->flatMap(fn (Invoice $invoice) => $invoice->payments)
                    ->sortByDesc(function (Payment $payment) {
                        return optional($payment->payment_date)->timestamp
                            ?? optional($payment->created_at)->timestamp
                            ?? 0;
                    })
                    ->values();

                $totalAmount = round((float) $invoices->sum('total_amount'), 2);
                $paidAmount = round((float) $payments
                    ->where('status', 'confirmed')
                    ->sum('amount'), 2);
                $remainingAmount = round(max(0, $totalAmount - $paidAmount), 2);
                $progress = $totalAmount > 0
                    ? round(min(($paidAmount / $totalAmount) * 100, 100), 2)
                    : 0;

                $hasRejected = $payments->where('status', 'rejected')->isNotEmpty();

                $statusKey = $hasRejected
                    ? 'needs_review'
                    : ($remainingAmount <= 0
                        ? 'fully_paid'
                        : ($paidAmount > 0 ? 'partial' : 'needs_payment'));

                $statusLabel = match ($statusKey) {
                    'fully_paid' => 'Fully Paid',
                    'partial' => 'Partial',
                    'needs_review' => 'Needs Review',
                    default => 'Needs Payment',
                };

                $latestPayment = $payments->first();
                $nextInvoice = $invoices->first(
                    fn (Invoice $invoice) => (float) $invoice->remaining_balance > 0
                );

                return [
                    'key' => md5((string) $groupKey),
                    'client_name' => $clientName,
                    'client_email' => $requestData?->email ?? 'No email',
                    'client_phone' => $requestData?->phone,
                    'service_types' => $invoices
                        ->map(fn (Invoice $invoice) => $invoice->quotation?->request?->service_type)
                        ->filter()
                        ->unique()
                        ->values(),
                    'invoice_count' => $invoices->count(),
                    'payment_count' => $payments->count(),
                    'total_amount' => $totalAmount,
                    'paid_amount' => $paidAmount,
                    'remaining_amount' => $remainingAmount,
                    'progress' => $progress,
                    'status_key' => $statusKey,
                    'status_label' => $statusLabel,
                    'latest_payment' => $latestPayment,
                    'next_invoice' => $nextInvoice,
                    'invoices' => $invoices->values(),
                    'payments' => $payments,
                ];
            })
            ->sortByDesc(function (array $group) {
                return optional($group['latest_payment']?->payment_date)->timestamp
                    ?? $group['invoices']->max(fn (Invoice $invoice) => optional($invoice->updated_at)->timestamp)
                    ?? 0;
            })
            ->values();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 8;

        $clientPaymentGroups = new LengthAwarePaginator(
            $clientGroups->forPage($page, $perPage)->values(),
            $clientGroups->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $invoiceStats = Invoice::query()
            ->selectRaw('count(*) as total_invoices')
            ->selectRaw("sum(case when $paidSubquery >= invoices.total_amount and invoices.total_amount > 0 then 1 else 0 end) as fully_paid_count")
            ->selectRaw("sum(case when $paidSubquery > 0 and $paidSubquery < invoices.total_amount then 1 else 0 end) as partial_count")
            ->selectRaw("sum(case when $paidSubquery <= 0 then 1 else 0 end) as needs_payment_count")
            ->first();

        $paymentStats = [
            'total_invoices' => (int) ($invoiceStats->total_invoices ?? 0),
            'fully_paid_count' => (int) ($invoiceStats->fully_paid_count ?? 0),
            'partial_count' => (int) ($invoiceStats->partial_count ?? 0),
            'needs_payment_count' => (int) ($invoiceStats->needs_payment_count ?? 0),
            'total_payments' => Payment::count(),
            'confirmed_count' => Payment::where('status', 'confirmed')->count(),
            'pending_count' => Payment::whereIn('status', ['pending', 'pending_verification'])->count(),
            'rejected_count' => Payment::where('status', 'rejected')->count(),
            'collected_amount' => (float) Payment::where('status', 'confirmed')->sum('amount'),
        ];

        return view('hr.payments.index', compact(
            'clientPaymentGroups',
            'paymentStats',
            'tab'
        ));
    }

    public function create(Invoice $invoice)
    {
        $invoice->load(['quotation.request', 'items', 'paymentSchedules', 'payments']);

        return view('hr.payments.create', compact('invoice'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => ['required', 'exists:invoices,id'],
            'payment_schedule_id' => ['nullable', 'exists:payment_schedules,id'],
            'payment_method' => ['required', 'string', 'max:100'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'status' => ['required', 'in:pending,confirmed,rejected,pending_verification'],
            'notes' => ['nullable', 'string'],
        ]);

        $invoice = Invoice::with(['paymentSchedules', 'payments'])
            ->findOrFail($validated['invoice_id']);

        if (
            $validated['status'] === 'confirmed'
            && (float) $validated['amount'] > ((float) $invoice->remaining_balance + 0.01)
        ) {
            return back()->withErrors([
                'amount' => 'Payment amount cannot exceed the remaining balance.',
            ])->withInput();
        }

        $schedule = null;

        if (!empty($validated['payment_schedule_id'])) {
            $schedule = PaymentSchedule::where('invoice_id', $invoice->id)
                ->findOrFail($validated['payment_schedule_id']);
        }

        $payment = null;

        DB::transaction(function () use ($validated, $invoice, $schedule, &$payment) {
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'payment_schedule_id' => $schedule?->id,
                'received_by' => Auth::id(),
                'verified_by' => $validated['status'] === 'confirmed' ? Auth::id() : null,
                'verified_at' => $validated['status'] === 'confirmed' ? now() : null,
                'payment_no' => $this->generatePaymentNumber(),
                'payment_type' => $schedule?->label ?? 'Unscheduled Payment',
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'] ?? null,
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]);

            if ($schedule && $payment->status === 'confirmed') {
                $newPaidAmount = round(
                    (float) $schedule->amount_paid + (float) $payment->amount,
                    2
                );
                $amountDue = (float) $schedule->amount_due;
                $isPaid = $newPaidAmount >= $amountDue;

                $schedule->update([
                    'amount_paid' => $newPaidAmount,
                    'status' => $isPaid ? 'paid' : 'partial',
                    'milestone_status' => $isPaid
                        ? 'paid'
                        : $schedule->milestone_status,
                ]);
            }

            $this->syncInvoiceStatus($invoice->fresh()->load('payments'));
        });

        return redirect()
            ->route('hr.payments.index')
            ->with('success', 'Payment recorded successfully.');
    }

    public function show(Payment $payment)
    {
        $payment->load([
            'invoice.quotation.request',
            'paymentSchedule',
            'receiver',
            'submitter',
            'verifier',
            'receipt',
        ]);

        return view('hr.payments.show', compact('payment'));
    }

    public function confirm(Request $request, Payment $payment)
    {
        if ($payment->status !== 'pending_verification') {
            return back()->withErrors([
                'payment' => 'Only payments pending verification can be confirmed.',
            ]);
        }

        $payment->load(['invoice.paymentSchedules', 'paymentSchedule']);

        DB::transaction(function () use ($request, $payment) {
            $payment->update([
                'status' => 'confirmed',
                'received_by' => Auth::id(),
                'verified_by' => Auth::id(),
                'verified_at' => now(),
                'verification_notes' => $request->input('verification_notes'),
            ]);

            if ($payment->paymentSchedule) {
                $schedule = $payment->paymentSchedule->fresh();

                $newPaidAmount = round(
                    (float) $schedule->amount_paid + (float) $payment->amount,
                    2
                );
                $amountDue = (float) $schedule->amount_due;
                $isPaid = $newPaidAmount >= $amountDue;

                $schedule->update([
                    'amount_paid' => $newPaidAmount,
                    'status' => $isPaid ? 'paid' : 'partial',
                    'milestone_status' => $isPaid
                        ? 'paid'
                        : $schedule->milestone_status,
                ]);
            }

            $this->syncInvoiceStatus($payment->invoice->fresh()->load('payments'));
        });

        return redirect()
            ->route('hr.payments.index')
            ->with('success', 'Payment confirmed successfully.');
    }

    public function reject(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'verification_notes' => ['required', 'string'],
        ]);

        if ($payment->status !== 'pending_verification') {
            return back()->withErrors([
                'payment' => 'Only payments pending verification can be rejected.',
            ]);
        }

        $payment->update([
            'status' => 'rejected',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'verification_notes' => $validated['verification_notes'],
        ]);

        return redirect()
            ->route('hr.payments.index')
            ->with('success', 'Payment rejected successfully.');
    }

    protected function generatePaymentNumber(): string
    {
        $nextId = (Payment::max('id') ?? 0) + 1;

        return 'PAY-' . now()->format('Y') . '-' .
            str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
    }

    protected function syncInvoiceStatus(Invoice $invoice): void
    {
        $paid = round((float) $invoice->payments()
            ->where('status', 'confirmed')
            ->sum('amount'), 2);

        $total = round((float) $invoice->total_amount, 2);

        if ($paid <= 0) {
            $invoice->update([
                'status' => 'unpaid',
                'paid_at' => null,
            ]);

            return;
        }

        if ($paid < $total) {
            $invoice->update([
                'status' => 'partially_paid',
                'paid_at' => null,
            ]);

            return;
        }

        $invoice->update([
            'status' => 'paid',
            'paid_at' => $invoice->paid_at ?? now(),
        ]);
    }
}