<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BackJob;
use App\Models\User;
use App\Models\WarrantyClaim;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarrantyClaimController extends Controller
{
    public function index(Request $request)
    {
        $query = WarrantyClaim::with(['jobOrder.quotationRequest', 'client', 'reviewer', 'backJob'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('eligibility_status')) {
            $query->where('eligibility_status', $request->eligibility_status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('claim_no', 'like', "%{$search}%")
                    ->orWhere('issue_description', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($clientQuery) use ($search) {
                        $clientQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('jobOrder', function ($jobQuery) use ($search) {
                        $jobQuery->where('job_order_no', 'like', "%{$search}%");
                    });
            });
        }

        $claims = $query->paginate(10)->withQueryString();

        return view('admin.warranty-claims.index', compact('claims'));
    }

    public function show(WarrantyClaim $warrantyClaim)
    {
        $warrantyClaim->load(['jobOrder.quotationRequest', 'client', 'reviewer', 'backJob.worker']);

        $workers = User::whereIn('role', ['worker', 'inspector'])
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        return view('admin.warranty-claims.show', compact('warrantyClaim', 'workers'));
    }

    public function approve(Request $request, WarrantyClaim $warrantyClaim)
    {
        if ($warrantyClaim->status !== 'pending') {
            return back()->withErrors([
                'claim' => 'Only pending warranty claims can be approved.',
            ]);
        }

        if ($warrantyClaim->eligibility_status !== 'eligible') {
            return back()->withErrors([
                'claim' => 'This warranty claim is not eligible for approval.',
            ]);
        }

        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string'],
        ]);

        $oldValues = $this->claimAuditSnapshot($warrantyClaim);

        $warrantyClaim->update([
            'status' => 'approved',
            'admin_notes' => $validated['admin_notes'] ?? $warrantyClaim->admin_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'approved_at' => now(),
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);

        $warrantyClaim->refresh()->load(['jobOrder', 'client', 'reviewer']);

        AuditLogService::log(
            'Warranty Claim Approved',
            'Warranty Claims',
            $warrantyClaim,
            $oldValues,
            $this->claimAuditSnapshot($warrantyClaim),
            "Admin approved warranty claim {$warrantyClaim->claim_no}."
        );

        return back()->with('success', 'Warranty claim approved successfully.');
    }

    public function reject(Request $request, WarrantyClaim $warrantyClaim)
    {
        if ($warrantyClaim->status !== 'pending') {
            return back()->withErrors([
                'claim' => 'Only pending warranty claims can be rejected.',
            ]);
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string'],
            'admin_notes' => ['nullable', 'string'],
        ]);

        $oldValues = $this->claimAuditSnapshot($warrantyClaim);

        $warrantyClaim->update([
            'status' => 'rejected',
            'admin_notes' => $validated['admin_notes'] ?? $warrantyClaim->admin_notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'rejected_at' => now(),
            'approved_at' => null,
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        $warrantyClaim->refresh()->load(['jobOrder', 'client', 'reviewer']);

        AuditLogService::log(
            'Warranty Claim Rejected',
            'Warranty Claims',
            $warrantyClaim,
            $oldValues,
            $this->claimAuditSnapshot($warrantyClaim),
            "Admin rejected warranty claim {$warrantyClaim->claim_no}."
        );

        return back()->with('success', 'Warranty claim rejected successfully.');
    }

    public function createBackJob(Request $request, WarrantyClaim $warrantyClaim)
    {
        if ($warrantyClaim->status !== 'approved') {
            return back()->withErrors([
                'claim' => 'Only approved warranty claims can create a backjob.',
            ]);
        }

        if ($warrantyClaim->backJob) {
            return redirect()
                ->route('admin.backjobs.show', $warrantyClaim->backJob)
                ->with('info', 'A backjob already exists for this warranty claim.');
        }

        $validated = $request->validate([
            'worker_id' => ['nullable', 'exists:users,id'],
            'reason' => ['required', 'string'],
            'scheduled_date' => ['nullable', 'date'],
            'scheduled_time' => ['nullable', 'string', 'max:20'],
            'admin_notes' => ['nullable', 'string'],
        ]);

        $backJob = BackJob::create([
            'warranty_claim_id' => $warrantyClaim->id,
            'original_job_order_id' => $warrantyClaim->job_order_id,
            'worker_id' => $validated['worker_id'] ?? $warrantyClaim->jobOrder?->worker_id,
            'backjob_no' => $this->generateBackJobNumber(),
            'reason' => $validated['reason'],
            'scheduled_date' => $validated['scheduled_date'] ?? $warrantyClaim->preferred_date,
            'scheduled_time' => $validated['scheduled_time'] ?? $warrantyClaim->preferred_time,
            'status' => 'scheduled',
            'admin_notes' => $validated['admin_notes'] ?? null,
            'created_by' => Auth::id(),
            'scheduled_by' => Auth::id(),
        ]);

        $backJob->load(['warrantyClaim', 'originalJobOrder', 'worker', 'creator']);

        AuditLogService::log(
            'Backjob Created',
            'Backjobs',
            $backJob,
            null,
            $this->backJobAuditSnapshot($backJob),
            "Admin created backjob {$backJob->backjob_no} from warranty claim {$warrantyClaim->claim_no}."
        );

        return redirect()
            ->route('admin.backjobs.show', $backJob)
            ->with('success', 'Backjob created successfully.');
    }

    protected function claimAuditSnapshot(WarrantyClaim $claim): array
    {
        return [
            'claim_no' => $claim->claim_no,
            'job_order_id' => $claim->job_order_id,
            'job_order_no' => $claim->jobOrder?->job_order_no,
            'client_id' => $claim->client_id,
            'client_name' => $claim->client?->name,
            'issue_description' => $claim->issue_description,
            'preferred_date' => optional($claim->preferred_date)->format('Y-m-d'),
            'preferred_time' => $claim->preferred_time,
            'status' => $claim->status,
            'eligibility_status' => $claim->eligibility_status,
            'warranty_expires_at' => optional($claim->warranty_expires_at)->toDateTimeString(),
            'admin_notes' => $claim->admin_notes,
            'reviewed_by' => $claim->reviewed_by,
            'reviewed_by_name' => $claim->reviewer?->name,
            'reviewed_at' => optional($claim->reviewed_at)->toDateTimeString(),
            'approved_at' => optional($claim->approved_at)->toDateTimeString(),
            'rejected_at' => optional($claim->rejected_at)->toDateTimeString(),
            'rejection_reason' => $claim->rejection_reason,
        ];
    }

    protected function backJobAuditSnapshot(BackJob $backJob): array
    {
        return [
            'backjob_no' => $backJob->backjob_no,
            'warranty_claim_id' => $backJob->warranty_claim_id,
            'original_job_order_id' => $backJob->original_job_order_id,
            'worker_id' => $backJob->worker_id,
            'worker_name' => $backJob->worker?->name,
            'reason' => $backJob->reason,
            'scheduled_date' => optional($backJob->scheduled_date)->format('Y-m-d'),
            'scheduled_time' => $backJob->scheduled_time,
            'status' => $backJob->status,
            'admin_notes' => $backJob->admin_notes,
            'created_by' => $backJob->created_by,
            'created_by_name' => $backJob->creator?->name,
        ];
    }

    protected function generateBackJobNumber(): string
    {
        $nextId = (BackJob::max('id') ?? 0) + 1;

        return 'BJ-' . now()->format('Y') . '-' . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
    }
}