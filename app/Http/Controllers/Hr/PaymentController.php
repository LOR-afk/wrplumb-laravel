<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $invoiceQuery = Invoice::with([
                'quotation.request',
                'paymentSchedules',
                'payments' => function ($query) {
                    $query->latest('payment_date')->latest('id');
                },
                'payments.paymentSchedule',
                'payments.receiver',
                'payments.receipt',
            ])
            ->whereHas('payments');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));

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
                            ->orWhere('phone', 'like', "%{$search}%");
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
            $schedule = trim($request->input('schedule'));

            $invoiceQuery->whereHas('payments', function ($query) use ($schedule) {
                $query->where('payment_type', 'like', "%{$schedule}%")
                    ->orWhereHas('paymentSchedule', function ($scheduleQuery) use ($schedule) {
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

        $paymentInvoices = $invoiceQuery
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();

        $paymentStats = [
            'total_payments' => Payment::count(),
            'confirmed_count' => Payment::where('status', 'confirmed')->count(),
            'pending_count' => Payment::whereIn('status', ['pending', 'pending_verification'])->count(),
            'rejected_count' => Payment::where('status', 'rejected')->count(),
            'collected_amount' => Payment::where('status', 'confirmed')->sum('amount'),
        ];

        return view('hr.payments.index', compact('paymentInvoices', 'paymentStats'));
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
            'payment_type' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'status' => ['required', 'in:pending,confirmed,rejected,pending_verification'],
            'notes' => ['nullable', 'string'],
        ]);

        $invoice = Invoice::with(['paymentSchedules', 'payments'])->findOrFail($validated['invoice_id']);

        $schedule = null;
        if (!empty($validated['payment_schedule_id'])) {
            $schedule = PaymentSchedule::where('invoice_id', $invoice->id)
                ->findOrFail($validated['payment_schedule_id']);
        }

        DB::transaction(function () use ($validated, $invoice, $schedule, &$payment) {
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'payment_schedule_id' => $schedule?->id,
                'received_by' => Auth::id(),
                'payment_no' => $this->generatePaymentNumber(),
                'payment_type' => $validated['payment_type'] ?? ($schedule?->label),
                'payment_method' => $validated['payment_method'] ?? null,
                'reference_number' => $validated['reference_number'] ?? null,
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]);

            if ($schedule && $payment->status === 'confirmed') {
                $newPaidAmount = round((float) $schedule->amount_paid + (float) $payment->amount, 2);
                $amountDue = (float) $schedule->amount_due;

                $schedule->update([
                    'amount_paid' => $newPaidAmount,
                    'status' => $newPaidAmount >= $amountDue ? 'paid' : 'partial',
                ]);
            }

            $this->syncInvoiceStatus($invoice->fresh()->load('paymentSchedules'));
        });

        return redirect()
            ->route('hr.payments.show', $payment)
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

                $newPaidAmount = round((float) $schedule->amount_paid + (float) $payment->amount, 2);
                $amountDue = (float) $schedule->amount_due;

                $schedule->update([
                    'amount_paid' => $newPaidAmount,
                    'status' => $newPaidAmount >= $amountDue ? 'paid' : 'partial',
                ]);
            }

            $this->syncInvoiceStatus($payment->invoice->fresh()->load('paymentSchedules'));
        });

        return redirect()
            ->route('hr.payments.show', $payment)
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
            ->route('hr.payments.show', $payment)
            ->with('success', 'Payment rejected successfully.');
    }

    protected function generatePaymentNumber(): string
    {
        $nextId = (Payment::max('id') ?? 0) + 1;

        return 'PAY-' . now()->format('Y') . '-' . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
    }

    protected function syncInvoiceStatus(Invoice $invoice): void
    {
        $schedules = $invoice->paymentSchedules;

        if ($schedules->isEmpty()) {
            return;
        }

        $paidCount = $schedules->where('status', 'paid')->count();
        $partialCount = $schedules->where('status', 'partial')->count();

        if ($paidCount === $schedules->count()) {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
            return;
        }

        if ($paidCount > 0 || $partialCount > 0) {
            $invoice->update([
                'status' => 'partial',
            ]);
            return;
        }

        $invoice->update([
            'status' => 'unpaid',
        ]);
    }
}
