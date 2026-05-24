<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Services\AuditLogService;

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
        $quotation = Quotation::with('request')
            ->where('acceptance_token', $token)
            ->firstOrFail();

        if ($quotation->client_response === 'accepted') {
            return back()->with('info', 'This quotation has already been accepted.');
        }

        if ($quotation->client_response === 'declined') {
            return back()->with('info', 'This quotation has already been declined.');
        }

        $oldValues = $quotation->only([
            'status',
            'client_response',
            'accepted_at',
            'declined_at',
        ]);

        $quotation->update([
            'status' => 'accepted',
            'client_response' => 'accepted',
            'accepted_at' => now(),
            'declined_at' => null,
        ]);

        $quotation->refresh();

        AuditLogService::log(
            'Client Accepted Quotation',
            'Quotation Acceptance',
            $quotation,
            $oldValues,
            [
                'quotation_id' => $quotation->id,
                'quotation_no' => $quotation->quotation_no ?? null,
                'client_email' => $quotation->request?->email,
                'status' => $quotation->status,
                'client_response' => $quotation->client_response,
                'accepted_at' => optional($quotation->accepted_at)->toDateTimeString(),
                'declined_at' => optional($quotation->declined_at)->toDateTimeString(),
            ],
            "Client accepted quotation #{$quotation->id} through the public quotation acceptance link."
        );

        return back()->with('success', 'Quotation accepted successfully. Our team will contact you for the next step.');
    }

    public function decline(string $token)
    {
        $quotation = Quotation::with('request')
            ->where('acceptance_token', $token)
            ->firstOrFail();

        if ($quotation->client_response === 'accepted') {
            return back()->with('info', 'This quotation has already been accepted.');
        }

        if ($quotation->client_response === 'declined') {
            return back()->with('info', 'This quotation has already been declined.');
        }

        $oldValues = $quotation->only([
            'status',
            'client_response',
            'accepted_at',
            'declined_at',
        ]);

        $quotation->update([
            'status' => 'declined',
            'client_response' => 'declined',
            'declined_at' => now(),
            'accepted_at' => null,
        ]);

        $quotation->refresh();

        AuditLogService::log(
            'Client Declined Quotation',
            'Quotation Acceptance',
            $quotation,
            $oldValues,
            [
                'quotation_id' => $quotation->id,
                'quotation_no' => $quotation->quotation_no ?? null,
                'client_email' => $quotation->request?->email,
                'status' => $quotation->status,
                'client_response' => $quotation->client_response,
                'accepted_at' => optional($quotation->accepted_at)->toDateTimeString(),
                'declined_at' => optional($quotation->declined_at)->toDateTimeString(),
            ],
            "Client declined quotation #{$quotation->id} through the public quotation acceptance link."
        );

        return back()->with('success', 'Quotation declined. Thank you for your response.');
    }
}
