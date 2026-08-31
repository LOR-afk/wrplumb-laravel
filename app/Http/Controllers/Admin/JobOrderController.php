<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobOrder;
use App\Models\QuotationRequest;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobOrderController extends Controller
{
    public function index(Request $request)
    {
        $allowedStatuses = ['scheduled', 'in_progress', 'completed', 'cancelled'];

        $search = trim((string) $request->input('search'));
        $status = $request->input('status');
        $serviceType = $request->input('service_type');
        $workerId = $request->input('worker_id');

        $allowedSorts = [
            'created_at',
            'job_order_no',
            'client',
            'service_type',
            'worker',
            'scheduled_date',
            'status',
            'progress',
        ];

        $sort = $request->input('sort', 'created_at');
        $direction = strtolower((string) $request->input('direction', 'desc'));

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        if ($status && !in_array($status, $allowedStatuses, true)) {
            $status = null;
        }

        $baseQuery = JobOrder::query()
            ->with(['quotationRequest', 'worker', 'creator'])
            ->withCount(['warrantyClaims', 'backJobs'])
            ->when($search !== '', function ($query) use ($search) {
                $terms = collect(preg_split('/\\s+/', $search, -1, PREG_SPLIT_NO_EMPTY))
                    ->map(fn ($term) => trim($term))
                    ->filter()
                    ->values();

                $query->where(function ($q) use ($search, $terms) {
                    // Exact phrase-style matching for normal searchable fields.
                    $q->where('job_order_no', 'like', "%{$search}%")
                        ->orWhere('service_type', 'like', "%{$search}%")
                        ->orWhere('service_flow', 'like', "%{$search}%")
                        ->orWhereHas('quotationRequest', function ($requestQuery) use ($search, $terms) {
                            $requestQuery->where(function ($clientQuery) use ($search, $terms) {
                                $clientQuery
                                    ->whereRaw(
                                        "TRIM(CONCAT_WS(' ', first_name, last_name)) LIKE ?",
                                        ["%{$search}%"]
                                    )
                                    ->orWhereRaw(
                                        "TRIM(CONCAT_WS(' ', first_name, middle_initial, last_name)) LIKE ?",
                                        ["%{$search}%"]
                                    )
                                    ->orWhereRaw(
                                        "TRIM(CONCAT_WS(' ', first_name, CONCAT(middle_initial, '.'), last_name)) LIKE ?",
                                        ["%{$search}%"]
                                    )
                                    ->orWhere('email', 'like', "%{$search}%")
                                    ->orWhere('phone', 'like', "%{$search}%");

                                // If the displayed client name contains initials/punctuation,
                                // require every typed token to occur somewhere in the client record.
                                if ($terms->count() > 1) {
                                    $clientQuery->orWhere(function ($tokenQuery) use ($terms) {
                                        foreach ($terms as $term) {
                                            $cleanTerm = trim($term, " .,-");
                                            $tokenQuery->where(function ($part) use ($cleanTerm) {
                                                $part->where('first_name', 'like', "%{$cleanTerm}%")
                                                    ->orWhere('middle_initial', 'like', "%{$cleanTerm}%")
                                                    ->orWhere('last_name', 'like', "%{$cleanTerm}%")
                                                    ->orWhere('email', 'like', "%{$cleanTerm}%");
                                            });
                                        }
                                    });
                                }
                            });
                        })
                        ->orWhereHas('worker', function ($workerQuery) use ($search) {
                            $workerQuery->where(function ($worker) use ($search) {
                                $worker->where('name', 'like', "%{$search}%")
                                    ->orWhereRaw(
                                        "TRIM(CONCAT_WS(' ', first_name, last_name)) LIKE ?",
                                        ["%{$search}%"]
                                    )
                                    ->orWhere('email', 'like', "%{$search}%");
                            });
                        });
                });
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($serviceType, fn ($query) => $query->where('service_type', $serviceType))
            ->when($workerId, fn ($query) => $query->where('worker_id', $workerId));

        $summary = [
            'total' => (clone $baseQuery)->count(),
            'scheduled' => (clone $baseQuery)->where('status', 'scheduled')->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
            'cancelled' => (clone $baseQuery)->where('status', 'cancelled')->count(),
        ];

        $jobOrdersQuery = clone $baseQuery;

        switch ($sort) {
            case 'client':
                $jobOrdersQuery->orderBy(
                    QuotationRequest::selectRaw(
                        "TRIM(CONCAT_WS(' ', first_name, middle_initial, last_name))"
                    )
                        ->whereColumn('quotation_requests.id', 'job_orders.quotation_request_id')
                        ->limit(1),
                    $direction
                );
                break;

            case 'worker':
                $jobOrdersQuery->orderBy(
                    User::selectRaw(
                        "COALESCE(NULLIF(name, ''), TRIM(CONCAT_WS(' ', first_name, last_name)), email)"
                    )
                        ->whereColumn('users.id', 'job_orders.worker_id')
                        ->limit(1),
                    $direction
                );
                break;

            case 'progress':
                $jobOrdersQuery->orderByRaw(
                    "CASE status
                        WHEN 'completed' THEN 100
                        WHEN 'in_progress' THEN 60
                        WHEN 'scheduled' THEN 25
                        WHEN 'cancelled' THEN 0
                        ELSE 10
                    END {$direction}"
                );
                break;

            default:
                $jobOrdersQuery->orderBy($sort, $direction);
                break;
        }

        // Stable secondary sort keeps pagination predictable.
        if ($sort !== 'id') {
            $jobOrdersQuery->orderBy('id', 'desc');
        }

        $jobOrders = $jobOrdersQuery
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'list_html' => view('admin.job-orders.partials.job-list', [
                    'jobOrders' => $jobOrders,
                ])->render(),

                'stats_html' => view('admin.job-orders.partials.stats', [
                    'jobOrders' => $jobOrders,
                    'summary' => $summary,
                ])->render(),
            ]);
        }

        $serviceTypes = JobOrder::query()
            ->whereNotNull('service_type')
            ->where('service_type', '<>', '')
            ->distinct()
            ->orderBy('service_type')
            ->pluck('service_type');

        $workers = User::query()
            ->where('role', 'worker')
            ->orderBy('name')
            ->orderBy('first_name')
            ->get(['id', 'name', 'first_name', 'last_name', 'email']);

        return view('admin.job-orders.index', compact(
            'jobOrders',
            'summary',
            'serviceTypes',
            'workers',
            'search',
            'status',
            'serviceType',
            'workerId',
            'sort',
            'direction'
        ));
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

        $jobOrder->refresh()->load(['quotationRequest', 'worker', 'creator']);

        AuditLogService::log(
            'Admin Created Job Order',
            'Job Orders',
            $jobOrder,
            null,
            $this->jobOrderAuditSnapshot($jobOrder),
            "Admin created job order {$jobOrder->job_order_no} for quotation request #{$quotation->id}."
        );

        return redirect()
            ->route('admin.job-orders.show', $jobOrder)
            ->with('success', 'Job order created successfully.');
    }

    public function show(JobOrder $jobOrder)
    {
        $jobOrder->load(['quotationRequest', 'worker', 'creator']);

        $availableWorkers = User::query()
            ->whereIn('role', ['worker', 'inspector'])
            ->where('is_active', true)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'name', 'first_name', 'last_name', 'email', 'role']);

        return view('admin.job-orders.show', compact('jobOrder', 'availableWorkers'));
    }

    public function reschedule(Request $request, JobOrder $jobOrder)
    {
        if (in_array($jobOrder->status, ['completed', 'cancelled'], true)) {
            return back()->withErrors([
                'job_order' => 'Completed or cancelled job orders can no longer be rescheduled.'
            ]);
        }

        $validated = $request->validate([
            'scheduled_date' => ['required', 'date'],
            'scheduled_time' => ['nullable', 'string', 'max:20'],
        ]);

        $oldValues = $this->jobOrderAuditSnapshot($jobOrder);

        $jobOrder->update([
            'scheduled_date' => $validated['scheduled_date'],
            'scheduled_time' => $validated['scheduled_time'] ?? null,
        ]);

        $this->syncQuotationRequestFromJobOrder($jobOrder->fresh());

        $jobOrder->refresh()->load(['quotationRequest', 'worker', 'creator']);

        AuditLogService::log(
            'Admin Rescheduled Job Order',
            'Job Orders',
            $jobOrder,
            $oldValues,
            $this->jobOrderAuditSnapshot($jobOrder),
            "Admin rescheduled job order {$jobOrder->job_order_no}."
        );

        return back()->with('success', 'Job order schedule updated successfully.');
    }

    public function reassign(Request $request, JobOrder $jobOrder)
    {
        if (in_array($jobOrder->status, ['completed', 'cancelled'], true)) {
            return back()->withErrors([
                'job_order' => 'Completed or cancelled job orders can no longer be reassigned.'
            ]);
        }

        $validated = $request->validate([
            'worker_id' => ['required', 'exists:users,id'],
        ]);

        $worker = User::query()
            ->whereKey($validated['worker_id'])
            ->whereIn('role', ['worker', 'inspector'])
            ->where('is_active', true)
            ->firstOrFail();

        $oldValues = $this->jobOrderAuditSnapshot($jobOrder);

        $jobOrder->update([
            'worker_id' => $worker->id,
        ]);

        $this->syncQuotationRequestFromJobOrder($jobOrder->fresh());

        $jobOrder->refresh()->load(['quotationRequest', 'worker', 'creator']);

        AuditLogService::log(
            'Admin Reassigned Job Order',
            'Job Orders',
            $jobOrder,
            $oldValues,
            $this->jobOrderAuditSnapshot($jobOrder),
            "Admin reassigned job order {$jobOrder->job_order_no} to {$worker->name}."
        );

        return back()->with('success', 'Job order worker reassigned successfully.');
    }

    public function start(JobOrder $jobOrder)
    {
        if (!in_array($jobOrder->status, ['scheduled'])) {
            return back()->withErrors([
                'job_order' => 'Only scheduled job orders can be started.'
            ]);
        }

        $oldValues = $this->jobOrderAuditSnapshot($jobOrder);

        $jobOrder->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        $this->syncQuotationRequestFromJobOrder($jobOrder->fresh());

        $jobOrder->refresh()->load(['quotationRequest', 'worker', 'creator']);

        AuditLogService::log(
            'Admin Started Job Order',
            'Job Orders',
            $jobOrder,
            $oldValues,
            $this->jobOrderAuditSnapshot($jobOrder),
            "Admin started job order {$jobOrder->job_order_no}."
        );

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

        $oldValues = $this->jobOrderAuditSnapshot($jobOrder);

        $jobOrder->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completion_notes' => $validated['completion_notes'] ?? $jobOrder->completion_notes,
        ]);

        $this->syncQuotationRequestFromJobOrder($jobOrder->fresh());

        $jobOrder->refresh()->load(['quotationRequest', 'worker', 'creator']);

        AuditLogService::log(
            'Admin Completed Job Order',
            'Job Orders',
            $jobOrder,
            $oldValues,
            $this->jobOrderAuditSnapshot($jobOrder),
            "Admin completed job order {$jobOrder->job_order_no}."
        );

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

        $oldValues = $this->jobOrderAuditSnapshot($jobOrder);

        $jobOrder->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'completion_notes' => $validated['completion_notes'] ?? $jobOrder->completion_notes,
        ]);

        $this->syncQuotationRequestFromJobOrder($jobOrder->fresh());

        $jobOrder->refresh()->load(['quotationRequest', 'worker', 'creator']);

        AuditLogService::log(
            'Admin Cancelled Job Order',
            'Job Orders',
            $jobOrder,
            $oldValues,
            $this->jobOrderAuditSnapshot($jobOrder),
            "Admin cancelled job order {$jobOrder->job_order_no}."
        );

        return back()->with('success', 'Job order cancelled successfully.');
    }

    public function updateRemarks(Request $request, JobOrder $jobOrder)
    {
        $validated = $request->validate([
            'work_remarks' => ['nullable', 'string'],
            'admin_notes' => ['nullable', 'string'],
        ]);

        $oldValues = $this->jobOrderAuditSnapshot($jobOrder);

        $jobOrder->update([
            'work_remarks' => $validated['work_remarks'] ?? null,
            'admin_notes' => $validated['admin_notes'] ?? null,
        ]);

        $jobOrder->refresh()->load(['quotationRequest', 'worker', 'creator']);

        AuditLogService::log(
            'Admin Updated Job Order Remarks',
            'Job Orders',
            $jobOrder,
            $oldValues,
            $this->jobOrderAuditSnapshot($jobOrder),
            "Admin updated remarks for job order {$jobOrder->job_order_no}."
        );

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

    protected function jobOrderAuditSnapshot(JobOrder $jobOrder): array
    {
        return [
            'job_order_no' => $jobOrder->job_order_no,
            'quotation_request_id' => $jobOrder->quotation_request_id,
            'worker_id' => $jobOrder->worker_id,
            'worker_name' => $jobOrder->worker?->name,
            'service_flow' => $jobOrder->service_flow,
            'service_type' => $jobOrder->service_type,
            'project_type' => $jobOrder->project_type,
            'scheduled_date' => $this->formatDateValue($jobOrder->scheduled_date),
            'scheduled_time' => $jobOrder->scheduled_time,
            'status' => $jobOrder->status,
            'scope_of_work' => $jobOrder->scope_of_work,
            'admin_notes' => $jobOrder->admin_notes,
            'work_remarks' => $jobOrder->work_remarks,
            'completion_notes' => $jobOrder->completion_notes,
            'started_at' => $this->formatDateTimeValue($jobOrder->started_at),
            'completed_at' => $this->formatDateTimeValue($jobOrder->completed_at),
            'cancelled_at' => $this->formatDateTimeValue($jobOrder->cancelled_at),
            'created_by' => $jobOrder->created_by,
            'created_by_name' => $jobOrder->creator?->name,
        ];
    }

    protected function formatDateValue($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        return date('Y-m-d', strtotime((string) $value));
    }

    protected function formatDateTimeValue($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        return date('Y-m-d H:i:s', strtotime((string) $value));
    }

    protected function generateJobOrderNumber(): string
    {
        $nextId = (JobOrder::max('id') ?? 0) + 1;

        return 'JO-' . now()->format('Y') . '-' . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
    }
}