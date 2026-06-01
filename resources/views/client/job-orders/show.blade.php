@extends('client.layouts.app')

@section('title', 'Job Order Details')

@section('content')
@php
    $status = $jobOrder->status ?? 'scheduled';

    $warrantyClaims = $jobOrder->warrantyClaims()
        ->with('backJob')
        ->latest()
        ->get();

    $latestWarrantyClaim = $warrantyClaims->first();

    $activeWarrantyClaim = $warrantyClaims->first(function ($claim) {
        return in_array($claim->status, ['pending', 'approved']);
    });

    $warrantyExpiresAt = $jobOrder->completed_at
        ? $jobOrder->completed_at->copy()->addDays(30)
        : null;

    $isCompleted = $status === 'completed' && !empty($jobOrder->completed_at);
    $isWithinWarranty = $warrantyExpiresAt
        ? now()->lessThanOrEqualTo($warrantyExpiresAt)
        : false;

    $canSubmitWarrantyClaim = $isCompleted && $isWithinWarranty && !$activeWarrantyClaim;

    $warrantyStatusText = match (true) {
        !$isCompleted => 'Available after job completion',
        $isWithinWarranty => 'Within 30-day warranty period',
        default => 'Warranty period ended',
    };

    $warrantyStatusClass = match (true) {
        !$isCompleted => 'secondary',
        $isWithinWarranty => 'success',
        default => 'danger',
    };
@endphp

<style>
    .client-job-card {
        border: 0;
        border-radius: 20px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .client-info-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .client-info-box {
        border: 1px solid #edf2f7;
        background: #f8fbff;
        border-radius: 16px;
        padding: 14px 16px;
        min-height: 76px;
    }

    .client-info-label {
        display: block;
        color: #64748b;
        font-size: 0.74rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 6px;
    }

    .client-info-value {
        color: #0f172a;
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .warranty-card {
        border: 0;
        border-radius: 20px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
        overflow: hidden;
        margin-top: 18px;
    }

    .warranty-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        padding: 18px 20px;
        border-bottom: 1px solid #e5edf6;
        background:
            radial-gradient(circle at top right, rgba(29, 155, 240, 0.12), transparent 35%),
            linear-gradient(135deg, #ffffff, #f8fbff);
    }

    .warranty-title {
        color: #0f172a;
        font-weight: 900;
        font-size: 1.04rem;
        margin-bottom: 4px;
    }

    .warranty-subtitle {
        color: #64748b;
        font-size: 0.86rem;
        margin-bottom: 0;
    }

    .warranty-body {
        padding: 18px 20px;
    }

    .warranty-note {
        padding: 13px 15px;
        border-radius: 16px;
        border: 1px dashed #bfdbfe;
        background: #f8fbff;
        color: #475569;
        font-size: 0.88rem;
        font-weight: 650;
        line-height: 1.45;
        margin-bottom: 16px;
    }

    .warranty-note.success {
        border-color: #bbf7d0;
        background: #ecfdf3;
        color: #166534;
    }

    .warranty-note.warning {
        border-color: #fed7aa;
        background: #fff7ed;
        color: #9a3412;
    }

    .warranty-note.danger {
        border-color: #fecaca;
        background: #fff1f2;
        color: #991b1b;
    }

    .warranty-meta-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 16px;
    }

    .warranty-meta {
        border: 1px solid #edf2f7;
        background: #f8fbff;
        border-radius: 16px;
        padding: 13px 15px;
        min-height: 72px;
    }

    .warranty-meta span {
        display: block;
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 5px;
    }

    .warranty-meta strong {
        color: #0f172a;
        font-size: 0.9rem;
        font-weight: 850;
    }

    .claim-status-card {
        border: 1px solid #edf2f7;
        background: #ffffff;
        border-radius: 16px;
        padding: 14px 15px;
        margin-bottom: 16px;
    }

    .claim-status-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 8px;
    }

    .claim-no {
        color: #0f172a;
        font-weight: 900;
    }

    .claim-desc {
        color: #64748b;
        font-size: 0.86rem;
        line-height: 1.45;
        margin-bottom: 0;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 900;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .status-pill.pending,
    .status-pill.scheduled,
    .status-pill.in_progress {
        background: #fff7ed;
        color: #c2410c;
    }

    .status-pill.approved,
    .status-pill.resolved {
        background: #ecfdf3;
        color: #15803d;
    }

    .status-pill.rejected,
    .status-pill.cancelled {
        background: #fee2e2;
        color: #b91c1c;
    }

    .status-pill.eligible {
        background: #e8f5ff;
        color: #0f4c81;
    }

    .warranty-form textarea {
        min-height: 112px;
        resize: vertical;
    }

    @media (max-width: 992px) {
        .client-info-grid,
        .warranty-meta-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 576px) {
        .client-info-grid,
        .warranty-meta-grid {
            grid-template-columns: 1fr;
        }

        .warranty-header {
            flex-direction: column;
        }
    }
</style>

<div class="page-header-card mb-4">
    <h2 class="mb-1">Job Order Details</h2>
    <p class="text-muted mb-0">View your service execution details.</p>
</div>

<div class="card client-job-card">
    <div class="card-body">
        <div class="client-info-grid">
            <div class="client-info-box">
                <span class="client-info-label">Job Order No.</span>
                <div class="client-info-value">{{ $jobOrder->job_order_no }}</div>
            </div>

            <div class="client-info-box">
                <span class="client-info-label">Status</span>
                <div class="client-info-value text-uppercase">{{ str_replace('_', ' ', $jobOrder->status) }}</div>
            </div>

            <div class="client-info-box">
                <span class="client-info-label">Service Flow</span>
                <div class="client-info-value text-uppercase">{{ str_replace('_', ' ', $jobOrder->service_flow ?? '—') }}</div>
            </div>

            <div class="client-info-box">
                <span class="client-info-label">Service Type</span>
                <div class="client-info-value">{{ $jobOrder->service_type ?? '—' }}</div>
            </div>

            <div class="client-info-box">
                <span class="client-info-label">Assigned Worker</span>
                <div class="client-info-value">{{ $jobOrder->worker->name ?? $jobOrder->worker->first_name ?? 'Not assigned' }}</div>
            </div>

            <div class="client-info-box">
                <span class="client-info-label">Schedule</span>
                <div class="client-info-value">
                    @if ($jobOrder->scheduled_date)
                        {{ optional($jobOrder->scheduled_date)->format('Y-m-d') }}
                        @if ($jobOrder->scheduled_time)
                            • {{ \Carbon\Carbon::parse($jobOrder->scheduled_time)->format('h:i A') }}
                        @endif
                    @else
                        —
                    @endif
                </div>
            </div>

            <div class="client-info-box">
                <span class="client-info-label">Project Type</span>
                <div class="client-info-value">{{ $jobOrder->project_type ?? '—' }}</div>
            </div>

            <div class="client-info-box">
                <span class="client-info-label">Scope of Work</span>
                <div class="client-info-value">{{ $jobOrder->scope_of_work ?? '—' }}</div>
            </div>

            <div class="client-info-box">
                <span class="client-info-label">Completion Date</span>
                <div class="client-info-value">{{ optional($jobOrder->completed_at)->format('Y-m-d h:i A') ?? 'Not completed yet' }}</div>
            </div>
        </div>

        @if ($jobOrder->work_remarks)
            <div class="mt-3">
                <div class="small text-muted fw-bold text-uppercase">Work Remarks</div>
                <div class="fw-semibold">{{ $jobOrder->work_remarks }}</div>
            </div>
        @endif

        @if ($jobOrder->completion_notes)
            <div class="mt-3">
                <div class="small text-muted fw-bold text-uppercase">Completion Notes</div>
                <div class="fw-semibold">{{ $jobOrder->completion_notes }}</div>
            </div>
        @endif

        <div class="mt-4">
            <a href="{{ route('client.job-orders.index') }}" class="btn btn-outline-secondary">
                Back to My Job Orders
            </a>
        </div>
    </div>
</div>

<div class="card warranty-card">
    <div class="warranty-header">
        <div>
            <div class="warranty-title">
                <i class="fas fa-shield-halved me-2 text-primary"></i>
                Warranty Claim
            </div>
            <p class="warranty-subtitle">Report a recurring issue related to this completed service.</p>
        </div>

        <span class="badge bg-{{ $warrantyStatusClass }} rounded-pill px-3 py-2">
            {{ $warrantyStatusText }}
        </span>
    </div>

    <div class="warranty-body">
        <div class="warranty-meta-grid">
            <div class="warranty-meta">
                <span>Completed Date</span>
                <strong>{{ optional($jobOrder->completed_at)->format('Y-m-d h:i A') ?? 'Not completed yet' }}</strong>
            </div>

            <div class="warranty-meta">
                <span>Warranty Until</span>
                <strong>{{ $warrantyExpiresAt ? $warrantyExpiresAt->format('Y-m-d h:i A') : 'Not available' }}</strong>
            </div>

            <div class="warranty-meta">
                <span>Total Claims</span>
                <strong>{{ $warrantyClaims->count() }}</strong>
            </div>
        </div>

        @if ($latestWarrantyClaim)
            <div class="claim-status-card">
                <div class="claim-status-top">
                    <div>
                        <div class="claim-no">{{ $latestWarrantyClaim->claim_no }}</div>
                        <p class="claim-desc">
                            {{ \Illuminate\Support\Str::limit($latestWarrantyClaim->issue_description, 140) }}
                        </p>
                    </div>

                    <span class="status-pill {{ $latestWarrantyClaim->status }}">
                        {{ ucfirst($latestWarrantyClaim->status) }}
                    </span>
                </div>

                <div class="small text-muted">
                    Submitted {{ optional($latestWarrantyClaim->created_at)->format('Y-m-d h:i A') }}

                    @if ($latestWarrantyClaim->backJob)
                        <br>
                        Backjob: {{ $latestWarrantyClaim->backJob->backjob_no }}
                        — {{ ucwords(str_replace('_', ' ', $latestWarrantyClaim->backJob->status)) }}
                    @endif

                    @if ($latestWarrantyClaim->rejection_reason)
                        <br>
                        Rejection reason: {{ $latestWarrantyClaim->rejection_reason }}
                    @endif
                </div>
            </div>
        @endif

        @if ($canSubmitWarrantyClaim)
            <div class="warranty-note success">
                This job is still within the warranty period. If the same issue returned after completion, submit a warranty claim for admin review.
            </div>

            <form method="POST" action="{{ route('client.warranty-claims.store', $jobOrder) }}" class="warranty-form">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Issue Description</label>
                    <textarea
                        name="issue_description"
                        class="form-control"
                        placeholder="Describe the recurring issue. Example: The repaired leak returned in the same area."
                        required
                    >{{ old('issue_description') }}</textarea>
                    <small class="text-muted">Please describe what happened after the service was completed.</small>
                </div>

                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label">Preferred Date</label>
                        <input type="date" name="preferred_date" class="form-control" value="{{ old('preferred_date') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Preferred Time</label>
                        <input type="time" name="preferred_time" class="form-control" value="{{ old('preferred_time') }}">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-3">
                    <i class="fas fa-paper-plane me-1"></i>
                    Submit Warranty Claim
                </button>
            </form>
        @elseif ($activeWarrantyClaim)
            <div class="warranty-note warning">
                You already have an active warranty claim for this job order. Please wait for admin review or backjob updates.
            </div>
        @elseif (!$isCompleted)
            <div class="warranty-note">
                Warranty claim submission will be available after this job order is marked as completed.
            </div>
        @else
            <div class="warranty-note danger">
                This job order is already outside the 30-day warranty period. Please contact support for further assistance.
            </div>
        @endif
    </div>
</div>
@endsection
