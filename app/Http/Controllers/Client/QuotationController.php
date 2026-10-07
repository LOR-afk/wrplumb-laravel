<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\JobOrder;
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
                'service_job_order_id' => $serviceJobOrder->id,
                'service_job_order_no' => $serviceJobOrder->job_order_no,
            ],
            "Client accepted quotation #{$quotation->id} through the client portal."
        );

        return redirect()
            ->route('client.quotations.show', $quotation)
            ->with('success', 'Quotation accepted successfully. Your service is now awaiting work assignment and scheduling.');
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
                'created_by' => Auth::id(),
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
