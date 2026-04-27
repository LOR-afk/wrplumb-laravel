<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Receipt;
use Illuminate\Support\Facades\Auth;

class ReceiptController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $receipts = Receipt::with(['payment.invoice.quotation.request', 'issuer'])
            ->whereHas('payment.invoice.quotation.request', function ($query) use ($user) {
                $query->where('email', $user->email);
            })
            ->latest()
            ->paginate(10);

        return view('client.receipts.index', compact('receipts'));
    }

    public function show(Receipt $receipt)
    {
        $user = Auth::user();

        $receipt->load(['payment.invoice.quotation.request', 'payment.paymentSchedule', 'issuer']);

        abort_if($receipt->payment->invoice->quotation->request->email !== $user->email, 403);

        return view('client.receipts.show', compact('receipt'));
    }
}