<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $invoices = Invoice::with(['quotation.request', 'items'])
            ->whereHas('quotation.request', function ($query) use ($user) {
                $query->where('email', $user->email);
            })
            ->latest()
            ->paginate(10);

        return view('client.invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        $user = Auth::user();

        $invoice->load(['quotation.request', 'items', 'creator']);

        abort_if($invoice->quotation->request->email !== $user->email, 403);

        return view('client.invoices.show', compact('invoice'));
    }
}