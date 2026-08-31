<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuotationController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $quotations = Quotation::with(['request', 'items'])
            ->whereHas('request', function ($query) use ($user) {
                $query->where('email', $user->email);
            })
            ->latest()
            ->paginate(10);

        return view('client.quotations.index', compact('quotations'));
    }

    public function show(Quotation $quotation)
    {
        $user = Auth::user();

        $quotation->load(['request', 'items', 'preparedBy']);

        abort_if($quotation->request->email !== $user->email, 403);

        return view('client.quotations.show', compact('quotation'));
    }

    public function accept(Request $request, Quotation $quotation)
    {
        $user = Auth::user();

        $quotation->load('request');

        abort_if($quotation->request->email !== $user->email, 403);

        if ($quotation->client_response === 'accepted') {
            return back()->with('info', 'This quotation has already been accepted.');
        }

        if ($quotation->client_response === 'declined') {
            return back()->with('info', 'This quotation has already been declined.');
        }

        $validated = $request->validate([
            'payment_plan' => ['required', 'in:full,5050,30303010'],
        ]);

        $oldValues = $quotation->only([
            'status',
            'client_response',
            'payment_plan',
            'payment_terms_json',
            'accepted_at',
            'declined_at',
        ]);

        $paymentTerms = $this->buildPaymentTerms(
            (float) $quotation->grand_total,
            $validated['payment_plan']
        );

        $quotation->update([
            'status' => 'accepted',
            'client_response' => 'accepted',
            'payment_plan' => $validated['payment_plan'],
            'payment_terms_json' => $paymentTerms,
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
                'quotation_no' => $quotation->quotation_no,
                'client_email' => $quotation->request?->email,
                'payment_plan' => $quotation->payment_plan,
                'payment_terms_json' => $quotation->payment_terms_json,
                'status' => $quotation->status,
                'client_response' => $quotation->client_response,
                'accepted_at' => optional($quotation->accepted_at)->toDateTimeString(),
            ],
            "Client accepted quotation #{$quotation->id} through the client portal."
        );

        return redirect()
            ->route('client.quotations.show', $quotation)
            ->with('success', 'Quotation accepted successfully with your selected payment plan.');
    }

    protected function buildPaymentTerms(float $total, string $planKey): array
    {
        if ($planKey === 'full') {
            $phases = [
                ['label' => 'Full Payment', 'percent' => 100],
            ];
        } elseif ($planKey === '30303010') {
            $phases = [
                ['label' => 'Downpayment', 'percent' => 30],
                ['label' => 'Progress 1', 'percent' => 30],
                ['label' => 'Progress 2', 'percent' => 30],
                ['label' => 'Retention', 'percent' => 10],
            ];
        } else {
            $phases = [
                ['label' => 'Downpayment', 'percent' => 50],
                ['label' => 'Final', 'percent' => 50],
            ];
        }

        $running = 0;

        foreach ($phases as $index => &$phase) {
            if ($index === count($phases) - 1) {
                $phase['amount'] = round(max(0, $total - $running), 2);
            } else {
                $phase['amount'] = round(($total * $phase['percent']) / 100, 2);
                $running += $phase['amount'];
            }
        }

        unset($phase);

        return [
            'plan' => $planKey,
            'total' => round($total, 2),
            'currency' => 'PHP',
            'phases' => $phases,
        ];
    }
}