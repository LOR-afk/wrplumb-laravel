<?php

namespace App\Http\Controllers;

use App\Models\Quotation;

class PublicQuotationAcceptanceController extends Controller
{
    public function show(string $token)
    {
        $quotation = Quotation::with(['request', 'items', 'preparedBy'])
            ->where('acceptance_token', $token)
            ->firstOrFail();

        return view('public.quotations.show', compact('quotation'));
    }

    public function accept(string $token)
    {
        $quotation = Quotation::where('acceptance_token', $token)->firstOrFail();

        if ($quotation->client_response === 'accepted') {
            return back()->with('info', 'This quotation has already been accepted.');
        }

        if ($quotation->client_response === 'declined') {
            return back()->with('info', 'This quotation has already been declined.');
        }

        $quotation->update([
            'status' => 'accepted',
            'client_response' => 'accepted',
            'accepted_at' => now(),
            'declined_at' => null,
        ]);

        return back()->with('success', 'Quotation accepted successfully. Our team will contact you for the next step.');
    }

    public function decline(string $token)
    {
        $quotation = Quotation::where('acceptance_token', $token)->firstOrFail();

        if ($quotation->client_response === 'accepted') {
            return back()->with('info', 'This quotation has already been accepted.');
        }

        if ($quotation->client_response === 'declined') {
            return back()->with('info', 'This quotation has already been declined.');
        }

        $quotation->update([
            'status' => 'declined',
            'client_response' => 'declined',
            'declined_at' => now(),
            'accepted_at' => null,
        ]);

        return back()->with('success', 'Quotation declined. Thank you for your response.');
    }
}