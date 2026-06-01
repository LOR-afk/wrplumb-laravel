<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BackJob;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BackJobController extends Controller
{
    public function index(Request $request)
    {
        $query = BackJob::with(['warrantyClaim.client', 'originalJobOrder', 'worker', 'creator'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('backjob_no', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhereHas('originalJobOrder', function ($jobQuery) use ($search) {
                        $jobQuery->where('job_order_no', 'like', "%{$search}%");
                    })
                    ->orWhereHas('warrantyClaim.client', function ($clientQuery) use ($search) {
                        $clientQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $backJobs = $query->paginate(10)->withQueryString();

        return view('admin.backjobs.index', compact('backJobs'));
    }

    public function show(BackJob $backJob)
    {
        $backJob->load([
            'warrantyClaim.client',
            'originalJobOrder.quotationRequest',
            'worker',
            'creator',
            'scheduler',
        ]);

        $workers = User::whereIn('role', ['worker', 'inspector'])
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        return view('admin.backjobs.show', compact('backJob', 'workers'));
    }

    public function schedule(Request $request, BackJob $backJob)
    {
        if (in_array($backJob->status, ['resolved', 'cancelled'])) {
            return back()->withErrors([
                'backjob' => 'Resolved or cancelled backjobs can no longer be scheduled.',
            ]);
        }

        $validated = $request->validate([
            'worker_id' => ['required', 'exists:users,id'],
            'scheduled_date' => ['required', 'date'],
            'scheduled_time' => ['required', 'string', 'max:20'],
            'admin_notes' => ['nullable', 'string'],
        ]);

        $oldValues = $this->backJobAuditSnapshot($backJob);

        $backJob->update([
            'worker_id' => $validated['worker_id'],
            'scheduled_date' => $validated['scheduled_date'],
            'scheduled_time' => $validated['scheduled_time'],
            'admin_notes' => $validated['admin_notes'] ?? $backJob->admin_notes,
            'status' => 'scheduled',
            'scheduled_by' => Auth::id(),
        ]);

        $backJob->refresh()->load(['warrantyClaim', 'originalJobOrder', 'worker', 'creator', 'scheduler']);

        AuditLogService::log(
            'Backjob Scheduled',
            'Backjobs',
            $backJob,
            $oldValues,
            $this->backJobAuditSnapshot($backJob),
            "Admin scheduled backjob {$backJob->backjob_no}."
        );

        return back()->with('success', 'Backjob schedule updated successfully.');
    }

    public function start(BackJob $backJob)
    {
        if ($backJob->status !== 'scheduled') {
            return back()->withErrors([
                'backjob' => 'Only scheduled backjobs can be started.',
            ]);
        }

        $oldValues = $this->backJobAuditSnapshot($backJob);

        $backJob->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        $backJob->refresh()->load(['warrantyClaim', 'originalJobOrder', 'worker', 'creator', 'scheduler']);

        AuditLogService::log(
            'Backjob Started',
            'Backjobs',
            $backJob,
            $oldValues,
            $this->backJobAuditSnapshot($backJob),
            "Admin started backjob {$backJob->backjob_no}."
        );

        return back()->with('success', 'Backjob marked as in progress.');
    }

    public function resolve(Request $request, BackJob $backJob)
    {
        if (!in_array($backJob->status, ['scheduled', 'in_progress'])) {
            return back()->withErrors([
                'backjob' => 'Only scheduled or in-progress backjobs can be resolved.',
            ]);
        }

        $validated = $request->validate([
            'resolution_notes' => ['required', 'string'],
        ]);

        $oldValues = $this->backJobAuditSnapshot($backJob);

        $backJob->update([
            'status' => 'resolved',
            'resolution_notes' => $validated['resolution_notes'],
            'resolved_at' => now(),
        ]);

        $backJob->refresh()->load(['warrantyClaim', 'originalJobOrder', 'worker', 'creator', 'scheduler']);

        AuditLogService::log(
            'Backjob Resolved',
            'Backjobs',
            $backJob,
            $oldValues,
            $this->backJobAuditSnapshot($backJob),
            "Admin resolved backjob {$backJob->backjob_no}."
        );

        return back()->with('success', 'Backjob resolved successfully.');
    }

    public function cancel(Request $request, BackJob $backJob)
    {
        if ($backJob->status === 'resolved') {
            return back()->withErrors([
                'backjob' => 'Resolved backjobs can no longer be cancelled.',
            ]);
        }

        $validated = $request->validate([
            'resolution_notes' => ['nullable', 'string'],
        ]);

        $oldValues = $this->backJobAuditSnapshot($backJob);

        $backJob->update([
            'status' => 'cancelled',
            'resolution_notes' => $validated['resolution_notes'] ?? $backJob->resolution_notes,
            'cancelled_at' => now(),
        ]);

        $backJob->refresh()->load(['warrantyClaim', 'originalJobOrder', 'worker', 'creator', 'scheduler']);

        AuditLogService::log(
            'Backjob Cancelled',
            'Backjobs',
            $backJob,
            $oldValues,
            $this->backJobAuditSnapshot($backJob),
            "Admin cancelled backjob {$backJob->backjob_no}."
        );

        return back()->with('success', 'Backjob cancelled successfully.');
    }

    protected function backJobAuditSnapshot(BackJob $backJob): array
    {
        return [
            'backjob_no' => $backJob->backjob_no,
            'warranty_claim_id' => $backJob->warranty_claim_id,
            'claim_no' => $backJob->warrantyClaim?->claim_no,
            'original_job_order_id' => $backJob->original_job_order_id,
            'original_job_order_no' => $backJob->originalJobOrder?->job_order_no,
            'worker_id' => $backJob->worker_id,
            'worker_name' => $backJob->worker?->name,
            'reason' => $backJob->reason,
            'scheduled_date' => optional($backJob->scheduled_date)->format('Y-m-d'),
            'scheduled_time' => $backJob->scheduled_time,
            'status' => $backJob->status,
            'resolution_notes' => $backJob->resolution_notes,
            'admin_notes' => $backJob->admin_notes,
            'created_by' => $backJob->created_by,
            'created_by_name' => $backJob->creator?->name,
            'scheduled_by' => $backJob->scheduled_by,
            'scheduled_by_name' => $backJob->scheduler?->name,
            'started_at' => optional($backJob->started_at)->toDateTimeString(),
            'resolved_at' => optional($backJob->resolved_at)->toDateTimeString(),
            'cancelled_at' => optional($backJob->cancelled_at)->toDateTimeString(),
        ];
    }
}