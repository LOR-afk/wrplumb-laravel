<?php

namespace App\Http\Controllers;

use App\Models\JobOrder;
use App\Models\Quotation;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class PublicQuotationAcceptanceController extends Controller
{
    public function show(string $token)
    {
        $quotation = Quotation::with(['request', 'items', 'preparedBy'])
            ->where('acceptance_token', $token)
            ->firstOrFail();

        return view('public.quotations.show', compact('quotation'));
    }

    public function accept(Request $request, string $token)
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

        $quotation->refresh()->load('request');
        $serviceJobOrder = $this->ensureServiceJobOrder($quotation);

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
                'declined_at' => optional($quotation->declined_at)->toDateTimeString(),
                'service_job_order_id' => $serviceJobOrder->id,
                'service_job_order_no' => $serviceJobOrder->job_order_no,
            ],
            "Client accepted quotation #{$quotation->id} through the public quotation acceptance link."
        );

        return back()->with(
            'success',
            'Quotation accepted successfully. Your service is now awaiting work assignment and scheduling.'
        );
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

    protected function ensureServiceJobOrder(Quotation $quotation): JobOrder
    {
        $serviceRequest = $quotation->request;

        return JobOrder::firstOrCreate(
            [
                'quotation_request_id' => $serviceRequest->id,
                'job_type' => 'service',
            ],
            [
                'worker_id' => null,
                'job_order_no' => $this->generateJobOrderNumber(),
                'service_flow' => $serviceRequest->service_flow,
                'service_type' => $serviceRequest->service_type,
                'project_type' => $serviceRequest->project_type,
                'scheduled_date' => null,
                'scheduled_time' => null,
                'status' => 'pending',
                'scope_of_work' => $serviceRequest->details,
                'admin_notes' => 'Service job order created automatically after quotation acceptance.',
                'created_by' => null,
            ]
        );
    }

    protected function generateJobOrderNumber(): string
    {
        $nextId = (JobOrder::max('id') ?? 0) + 1;

        do {
            $jobOrderNo = 'JO-' . now()->format('Y') . '-' . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
            $nextId++;
        } while (JobOrder::where('job_order_no', $jobOrderNo)->exists());

        return $jobOrderNo;
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
