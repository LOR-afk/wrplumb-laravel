<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReceiptController extends Controller
{
    public function index(Request $request)
    {
        $query = Receipt::query()
            ->with(['payment.invoice.quotation.request', 'issuer'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;

                $q->where(function ($sub) use ($search) {
                    $sub->where('receipt_no', 'like', "%{$search}%")
                        ->orWhereHas('payment', function ($paymentQuery) use ($search) {
                            $paymentQuery->where('payment_no', 'like', "%{$search}%")
                                ->orWhere('reference_number', 'like', "%{$search}%");
                        })
                        ->orWhereHas('payment.invoice', function ($invoiceQuery) use ($search) {
                            $invoiceQuery->where('invoice_no', 'like', "%{$search}%");
                        })
                        ->orWhereHas('payment.invoice.quotation.request', function ($requestQuery) use ($search) {
                            $requestQuery->where('full_name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('date_from'), function ($q) use ($request) {
                $q->whereDate('receipt_date', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($q) use ($request) {
                $q->whereDate('receipt_date', '<=', $request->date_to);
            });

        $summaryQuery = clone $query;

        $summary = [
            'total_receipts' => (clone $summaryQuery)->count(),
            'total_amount' => (clone $summaryQuery)->sum('amount_received'),
            'this_month_amount' => Receipt::whereYear('receipt_date', now()->year)
                ->whereMonth('receipt_date', now()->month)
                ->sum('amount_received'),
            'latest_receipt_date' => Receipt::max('receipt_date'),
        ];

        $receipts = $query
            ->latest('receipt_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('hr.receipts.index', compact('receipts', 'summary'));
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
        $receipt->load([
            'payment.invoice.quotation.request',
            'payment.paymentSchedule',
            'issuer',
        ]);

        return view('hr.receipts.show', compact('receipt'));
    }

    protected function generateReceiptNumber(): string
    {
        $nextId = (Receipt::max('id') ?? 0) + 1;

        return 'RCPT-' . now()->format('Y') . '-' . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
    }
}
