<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobOrder;
use App\Models\QuotationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobOrderController extends Controller
{
    public function index()
    {
        $jobOrders = JobOrder::with(['quotationRequest', 'worker', 'creator'])
            ->latest()
            ->paginate(10);

        return view('admin.job-orders.index', compact('jobOrders'));
    }

    public function create(QuotationRequest $quotation)
    {
        $quotation->load(['worker', 'jobOrder']);

        if ($quotation->jobOrder) {
            return redirect()
                ->route('admin.job-orders.show', $quotation->jobOrder)
                ->with('info', 'A job order already exists for this request.');
        }

        return view('admin.job-orders.create', compact('quotation'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'quotation_request_id' => ['required', 'exists:quotation_requests,id'],
            'worker_id' => ['nullable', 'exists:users,id'],
            'scheduled_date' => ['nullable', 'date'],
            'scheduled_time' => ['nullable', 'string', 'max:20'],
            'scope_of_work' => ['nullable', 'string'],
            'admin_notes' => ['nullable', 'string'],
        ]);

        $quotation = QuotationRequest::with(['worker', 'jobOrder'])->findOrFail($validated['quotation_request_id']);

        if ($quotation->jobOrder) {
            return back()->withErrors([
                'quotation_request_id' => 'This request already has a job order.'
            ])->withInput();
        }

        $jobOrder = JobOrder::create([
            'quotation_request_id' => $quotation->id,
            'worker_id' => $validated['worker_id'] ?? $quotation->worker_id,
            'job_order_no' => $this->generateJobOrderNumber(),
            'service_flow' => $quotation->service_flow,
            'service_type' => $quotation->service_type,
            'project_type' => $quotation->project_type,
            'scheduled_date' => $validated['scheduled_date'] ?? $quotation->appointment_date ?? $quotation->preferred_date,
            'scheduled_time' => $validated['scheduled_time'] ?? $quotation->appointment_time ?? $quotation->preferred_time,
            'status' => 'scheduled',
            'scope_of_work' => $validated['scope_of_work'] ?? $quotation->details,
            'admin_notes' => $validated['admin_notes'] ?? $quotation->admin_notes,
            'created_by' => Auth::id(),
        ]);

        $this->syncQuotationRequestFromJobOrder($jobOrder);

        return redirect()
            ->route('admin.job-orders.show', $jobOrder)
            ->with('success', 'Job order created successfully.');
    }

    public function show(JobOrder $jobOrder)
    {
        $jobOrder->load(['quotationRequest', 'worker', 'creator']);

        return view('admin.job-orders.show', compact('jobOrder'));
    }

    public function start(JobOrder $jobOrder)
    {
        if (!in_array($jobOrder->status, ['scheduled'])) {
            return back()->withErrors([
                'job_order' => 'Only scheduled job orders can be started.'
            ]);
        }

        $jobOrder->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        $this->syncQuotationRequestFromJobOrder($jobOrder->fresh());

        return back()->with('success', 'Job order marked as in progress.');
    }

    public function complete(Request $request, JobOrder $jobOrder)
    {
        $validated = $request->validate([
            'completion_notes' => ['nullable', 'string'],
        ]);

        if (!in_array($jobOrder->status, ['in_progress', 'scheduled'])) {
            return back()->withErrors([
                'job_order' => 'Only scheduled or in-progress job orders can be completed.'
            ]);
        }

        $jobOrder->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completion_notes' => $validated['completion_notes'] ?? $jobOrder->completion_notes,
        ]);

        $this->syncQuotationRequestFromJobOrder($jobOrder->fresh());

        return back()->with('success', 'Job order marked as completed.');
    }

    public function cancel(Request $request, JobOrder $jobOrder)
    {
        $validated = $request->validate([
            'completion_notes' => ['nullable', 'string'],
        ]);

        if ($jobOrder->status === 'completed') {
            return back()->withErrors([
                'job_order' => 'Completed job orders can no longer be cancelled.'
            ]);
        }

        $jobOrder->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'completion_notes' => $validated['completion_notes'] ?? $jobOrder->completion_notes,
        ]);

        $this->syncQuotationRequestFromJobOrder($jobOrder->fresh());

        return back()->with('success', 'Job order cancelled successfully.');
    }

    public function updateRemarks(Request $request, JobOrder $jobOrder)
    {
        $validated = $request->validate([
            'work_remarks' => ['nullable', 'string'],
            'admin_notes' => ['nullable', 'string'],
        ]);

        $jobOrder->update([
            'work_remarks' => $validated['work_remarks'] ?? null,
            'admin_notes' => $validated['admin_notes'] ?? null,
        ]);

        return back()->with('success', 'Job order remarks updated successfully.');
    }

    protected function syncQuotationRequestFromJobOrder(JobOrder $jobOrder): void
    {
        $jobOrder->loadMissing('quotationRequest');

        $quotation = $jobOrder->quotationRequest;

        if (!$quotation) {
            return;
        }

        $payload = [];

        if (!empty($jobOrder->worker_id)) {
            $payload['worker_id'] = $jobOrder->worker_id;
        }

        if (!empty($jobOrder->scheduled_date)) {
            $payload['appointment_date'] = $jobOrder->scheduled_date;
        }

        if (!empty($jobOrder->scheduled_time)) {
            $payload['appointment_time'] = $jobOrder->scheduled_time;
        }

        if ($jobOrder->status === 'cancelled') {
            $payload['appointment_status'] = 'cancelled';
            $payload['status'] = 'pending';

            if (empty($quotation->cancelled_at)) {
                $payload['cancelled_at'] = now();
            }
        } else {
            if (empty($quotation->appointment_status) || $quotation->appointment_status === 'pending') {
                $payload['appointment_status'] = 'approved';

                if (empty($quotation->approved_at)) {
                    $payload['approved_at'] = now();
                }
            }

            if ($jobOrder->status === 'scheduled') {
                $payload['status'] = 'assigned';
            } elseif ($jobOrder->status === 'in_progress') {
                $payload['status'] = 'in_progress';
            } elseif ($jobOrder->status === 'completed') {
                $payload['status'] = 'completed';

                if (empty($quotation->completed_at)) {
                    $payload['completed_at'] = now();
                }
            }
        }

        if (!empty($payload)) {
            $quotation->update($payload);
        }
    }

    protected function generateJobOrderNumber(): string
    {
        $nextId = (JobOrder::max('id') ?? 0) + 1;

        return 'JO-' . now()->format('Y') . '-' . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
    }
}