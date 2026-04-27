<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $payments = Payment::with(['invoice.quotation.request', 'paymentSchedule', 'receipt'])
            ->whereHas('invoice.quotation.request', function ($query) use ($user) {
                $query->where('email', $user->email);
            })
            ->latest('payment_date')
            ->paginate(10);

        return view('client.payments.index', compact('payments'));
    }

    public function show(Payment $payment)
    {
        $user = Auth::user();

       $payment->load(['invoice.quotation.request', 'paymentSchedule', 'receiver', 'submitter', 'verifier', 'receipt']);

        abort_if($payment->invoice->quotation->request->email !== $user->email, 403);

        return view('client.payments.show', compact('payment'));
    }

    public function create(Invoice $invoice)
    {
        $user = Auth::user();

        $invoice->load(['quotation.request', 'paymentSchedules', 'payments']);

        abort_if($invoice->quotation->request->email !== $user->email, 403);

        return view('client.payments.create', compact('invoice'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => ['required', 'exists:invoices,id'],
            'payment_schedule_id' => ['required', 'exists:payment_schedules,id'],
            'payment_method' => ['required', 'string', 'max:100'],
            'reference_number' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $user = Auth::user();

        $invoice = Invoice::with(['quotation.request', 'paymentSchedules'])->findOrFail($validated['invoice_id']);

        abort_if($invoice->quotation->request->email !== $user->email, 403);

        $schedule = PaymentSchedule::where('invoice_id', $invoice->id)
            ->findOrFail($validated['payment_schedule_id']);

        if (in_array($schedule->status, ['paid'])) {
            return back()->withErrors([
                'payment_schedule_id' => 'This payment schedule is already fully paid.'
            ])->withInput();
        }

        $proofPath = $request->file('proof')->store('payments', 'public');

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'payment_schedule_id' => $schedule->id,
            'submitted_by' => $user->id,
            'received_by' => null,
            'verified_by' => null,
            'payment_no' => $this->generatePaymentNumber(),
            'payment_type' => $schedule->label,
            'payment_method' => $validated['payment_method'],
            'reference_number' => $validated['reference_number'],
            'proof_path' => $proofPath,
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'status' => 'pending_verification',
            'notes' => $validated['notes'] ?? null,
            'verified_at' => null,
            'verification_notes' => null,
        ]);

        return redirect()
            ->route('client.payments.show', $payment)
            ->with('success', 'Payment submitted successfully and is now pending verification.');
    }

    protected function generatePaymentNumber(): string
    {
        $nextId = (Payment::max('id') ?? 0) + 1;

        return 'PAY-' . now()->format('Y') . '-' . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
    }
}