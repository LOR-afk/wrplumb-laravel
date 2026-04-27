<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReceiptController extends Controller
{
    public function index()
    {
        $receipts = Receipt::with(['payment.invoice.quotation.request', 'issuer'])
            ->latest()
            ->paginate(10);

        return view('hr.receipts.index', compact('receipts'));
    }

    public function create(Payment $payment)
    {
        $payment->load(['invoice.quotation.request', 'paymentSchedule', 'receipt']);

        if ($payment->status !== 'confirmed') {
            return redirect()
                ->route('hr.payments.show', $payment)
                ->withErrors(['payment' => 'Only confirmed payments can have receipts generated.']);
        }

        if ($payment->receipt) {
            return redirect()
                ->route('hr.receipts.show', $payment->receipt)
                ->with('info', 'A receipt already exists for this payment.');
        }

        return view('hr.receipts.create', compact('payment'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'payment_id' => ['required', 'exists:payments,id'],
            'receipt_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $payment = Payment::with(['invoice.quotation.request', 'paymentSchedule', 'receipt'])->findOrFail($validated['payment_id']);

        if ($payment->status !== 'confirmed') {
            return back()->withErrors([
                'payment_id' => 'Only confirmed payments can have receipts generated.'
            ])->withInput();
        }

        if ($payment->receipt) {
            return back()->withErrors([
                'payment_id' => 'This payment already has a receipt.'
            ])->withInput();
        }

        $receipt = Receipt::create([
            'payment_id' => $payment->id,
            'receipt_no' => $this->generateReceiptNumber(),
            'receipt_date' => $validated['receipt_date'],
            'amount_received' => (float) $payment->amount,
            'payment_method' => $payment->payment_method,
            'reference_number' => $payment->reference_number,
            'notes' => $validated['notes'] ?? null,
            'issued_by' => Auth::id(),
            'issued_at' => now(),
        ]);

        return redirect()
            ->route('hr.receipts.show', $receipt)
            ->with('success', 'Receipt generated successfully.');
    }

    public function show(Receipt $receipt)
    {
        $receipt->load(['payment.invoice.quotation.request', 'payment.paymentSchedule', 'issuer']);

        return view('hr.receipts.show', compact('receipt'));
    }

    protected function generateReceiptNumber(): string
    {
        $nextId = (Receipt::max('id') ?? 0) + 1;

        return 'RCPT-' . now()->format('Y') . '-' . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
    }
}