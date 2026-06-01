@extends('admin.layouts.app')

@section('title', 'Warranty Claim Details - WRPlumb')
@section('topbar_title', 'Warranty Claim Details')
@section('topbar_subtitle', 'Review warranty claim eligibility, approve or reject, and create a backjob.')

@push('styles')
<style>
    .wc-show-page {
        max-width: 1240px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .wc-card {
        background: #ffffff;
        border: 1px solid #dbe7f3;
        border-radius: 22px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.055);
        overflow: hidden;
    }

    .wc-hero {
        padding: 22px;
        background:
            radial-gradient(circle at top right, rgba(29, 155, 240, 0.12), transparent 36%),
            linear-gradient(135deg, #ffffff, #f8fbff);
    }

    .wc-hero-title {
        color: #0f172a;
        font-size: 1.35rem;
        font-weight: 950;
        margin-bottom: 5px;
    }

    .wc-hero-subtitle {
        color: #64748b;
        font-weight: 650;
        margin-bottom: 0;
    }

    .wc-badge-row {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        align-items: center;
    }

    .wc-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 7px 11px;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 950;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .wc-badge.pending {
        background: #fff7ed;
        color: #c2410c;
    }

    .wc-badge.approved {
        background: #ecfdf3;
        color: #15803d;
    }

    .wc-badge.rejected {
        background: #fee2e2;
        color: #b91c1c;
    }

    .wc-badge.eligible {
        background: #e8f5ff;
        color: #0f4c81;
    }

    .wc-badge.expired,
    .wc-badge.ineligible {
        background: #f1f5f9;
        color: #475569;
    }

    .wc-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        padding: 18px;
    }

    .wc-summary-item {
        min-height: 82px;
        border: 1px solid #edf2f7;
        background: #f8fbff;
        border-radius: 18px;
        padding: 13px 15px;
    }

    .wc-label {
        display: block;
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 6px;
    }

    .wc-value {
        color: #0f172a;
        font-weight: 850;
        overflow-wrap: anywhere;
    }

    .wc-main-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(340px, 0.9fr);
        gap: 16px;
        align-items: start;
    }

    .wc-section {
        padding: 18px;
    }

    .wc-section + .wc-section {
        border-top: 1px solid #dbe7f3;
    }

    .wc-section-title {
        display: flex;
        align-items: center;
        gap: 9px;
        color: #0f172a;
        font-weight: 950;
        font-size: 1rem;
        margin-bottom: 13px;
    }

    .wc-icon-pill {
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        border-radius: 12px;
        background: #e8f5ff;
        color: #0f4c81;
        flex: 0 0 auto;
    }

    .wc-read-box {
        border: 1px solid #edf2f7;
        background: #f8fbff;
        border-radius: 16px;
        padding: 14px 15px;
        color: #334155;
        font-weight: 650;
        line-height: 1.55;
        min-height: 58px;
    }

    .wc-read-box + .wc-read-box {
        margin-top: 10px;
    }

    .wc-actions {
        display: flex;
        gap: 9px;
        flex-wrap: wrap;
        margin-top: 16px;
    }

    .wc-action-card {
        padding: 18px;
    }

    .wc-note {
        padding: 12px 14px;
        border-radius: 15px;
        background: #f8fbff;
        border: 1px dashed #bfdbfe;
        color: #475569;
        font-size: 0.84rem;
        font-weight: 650;
        line-height: 1.45;
        margin-bottom: 14px;
    }

    .wc-danger-note {
        background: #fff1f2;
        border-color: #fecaca;
        color: #991b1b;
    }

    .wc-success-note {
        background: #ecfdf3;
        border-color: #bbf7d0;
        color: #166534;
    }

    .wc-form textarea {
        min-height: 112px;
        resize: vertical;
    }

    @media (max-width: 1200px) {
        .wc-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 992px) {
        .wc-main-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 576px) {
        .wc-summary-grid {
            grid-template-columns: 1fr;
        }

        .wc-hero {
            padding: 18px;
        }
    }
</style>
@endpush

@section('content')
@php
    $claim = $warrantyClaim;
    $jobOrder = $claim->jobOrder;
    $client = $claim->client;
    $backJob = $claim->backJob;
    $requestRecord = $jobOrder?->quotationRequest;

    $clientName = $client->name
        ?? $requestRecord?->full_name
        ?? trim(($requestRecord->first_name ?? '') . ' ' . ($requestRecord->last_name ?? ''));

    $canApprove = $claim->status === 'pending' && $claim->eligibility_status === 'eligible';
    $canReject = $claim->status === 'pending';
    $canCreateBackJob = $claim->status === 'approved' && !$backJob;
@endphp

<div class="wc-show-page">
    <div class="wc-card wc-hero">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <div class="wc-hero-title">
                    <i class="fas fa-shield-halved me-2 text-primary"></i>
                    {{ $claim->claim_no }}
                </div>
                <p class="wc-hero-subtitle">
                    Warranty request for {{ $jobOrder->job_order_no ?? 'job order' }} by {{ $clientName ?: 'Unknown client' }}.
                </p>
            </div>

            <div class="wc-badge-row">
                <span class="wc-badge {{ $claim->status }}">{{ ucfirst($claim->status) }}</span>
                <span class="wc-badge {{ $claim->eligibility_status }}">{{ ucfirst(str_replace('_', ' ', $claim->eligibility_status)) }}</span>
            </div>
        </div>

        <div class="wc-actions">
            <a href="{{ route('admin.warranty-claims.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Warranty Claims
            </a>

            @if ($jobOrder)
                <a href="{{ route('admin.job-orders.show', $jobOrder) }}" class="btn btn-outline-primary">
                    <i class="fas fa-clipboard-list me-1"></i> View Original Job Order
                </a>
            @endif

            @if ($backJob)
                <a href="{{ route('admin.backjobs.show', $backJob) }}" class="btn btn-success">
                    <i class="fas fa-rotate-left me-1"></i> View Backjob
                </a>
            @endif
        </div>
    </div>

    <div class="wc-card">
        <div class="wc-summary-grid">
            <div class="wc-summary-item">
                <span class="wc-label">Claim No.</span>
                <div class="wc-value">{{ $claim->claim_no }}</div>
            </div>

            <div class="wc-summary-item">
                <span class="wc-label">Original Job Order</span>
                <div class="wc-value">{{ $jobOrder->job_order_no ?? 'Not available' }}</div>
            </div>

            <div class="wc-summary-item">
                <span class="wc-label">Client</span>
                <div class="wc-value">{{ $clientName ?: 'Unknown client' }}</div>
            </div>

            <div class="wc-summary-item">
                <span class="wc-label">Warranty Until</span>
                <div class="wc-value">
                    @if ($claim->warranty_expires_at)
                        {{ optional($claim->warranty_expires_at)->format('Y-m-d') }}
                        <div class="text-muted small">{{ optional($claim->warranty_expires_at)->diffForHumans() }}</div>
                    @else
                        Not set
                    @endif
                </div>
            </div>

            <div class="wc-summary-item">
                <span class="wc-label">Preferred Date</span>
                <div class="wc-value">{{ optional($claim->preferred_date)->format('Y-m-d') ?? 'Not set' }}</div>
            </div>

            <div class="wc-summary-item">
                <span class="wc-label">Preferred Time</span>
                <div class="wc-value">{{ $claim->preferred_time ?? 'Not set' }}</div>
            </div>

            <div class="wc-summary-item">
                <span class="wc-label">Reviewed By</span>
                <div class="wc-value">{{ $claim->reviewer->name ?? 'Not reviewed' }}</div>
            </div>

            <div class="wc-summary-item">
                <span class="wc-label">Review Date</span>
                <div class="wc-value">{{ optional($claim->reviewed_at)->format('Y-m-d h:i A') ?? 'Not reviewed' }}</div>
            </div>
        </div>
    </div>

    <div class="wc-main-grid">
        <div class="wc-card">
            <div class="wc-section">
                <h3 class="wc-section-title">
                    <span class="wc-icon-pill"><i class="fas fa-triangle-exclamation"></i></span>
                    Client Reported Issue
                </h3>

                <div class="wc-read-box">
                    <span class="wc-label">Issue Description</span>
                    {{ $claim->issue_description }}
                </div>

                @if ($claim->rejection_reason)
                    <div class="wc-read-box">
                        <span class="wc-label">Rejection Reason</span>
                        {{ $claim->rejection_reason }}
                    </div>
                @endif

                @if ($claim->admin_notes)
                    <div class="wc-read-box">
                        <span class="wc-label">Admin Notes</span>
                        {{ $claim->admin_notes }}
                    </div>
                @endif
            </div>

            <div class="wc-section">
                <h3 class="wc-section-title">
                    <span class="wc-icon-pill"><i class="fas fa-circle-info"></i></span>
                    Original Job Summary
                </h3>

                <div class="wc-read-box">
                    <span class="wc-label">Service Type</span>
                    {{ $jobOrder->service_type ?? 'Not available' }}
                </div>

                <div class="wc-read-box">
                    <span class="wc-label">Completion Notes</span>
                    {{ $jobOrder->completion_notes ?? 'No completion notes recorded.' }}
                </div>
            </div>
        </div>

        <div>
            <div class="wc-card wc-action-card">
                <h3 class="wc-section-title">
                    <span class="wc-icon-pill"><i class="fas fa-check"></i></span>
                    Approve Claim
                </h3>

                @if ($canApprove)
                    <div class="wc-note wc-success-note">
                        Approving this claim confirms that the issue is covered by warranty. You may create a backjob after approval.
                    </div>

                    <form method="POST" action="{{ route('admin.warranty-claims.approve', $claim) }}" class="wc-form">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label">Admin Notes</label>
                            <textarea name="admin_notes" class="form-control" placeholder="Optional notes for this approval.">{{ old('admin_notes', $claim->admin_notes) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-circle-check me-1"></i> Approve Warranty Claim
                        </button>
                    </form>
                @else
                    <div class="wc-note">
                        Approval is only available for pending and eligible warranty claims.
                    </div>
                @endif
            </div>

            <div class="wc-card wc-action-card mt-3">
                <h3 class="wc-section-title">
                    <span class="wc-icon-pill"><i class="fas fa-xmark"></i></span>
                    Reject Claim
                </h3>

                @if ($canReject)
                    <div class="wc-note wc-danger-note">
                        Reject only when the issue is outside warranty coverage or not related to the original completed job.
                    </div>

                    <form method="POST" action="{{ route('admin.warranty-claims.reject', $claim) }}" class="wc-form">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label">Rejection Reason</label>
                            <textarea name="rejection_reason" class="form-control" placeholder="Explain why this claim is rejected." required>{{ old('rejection_reason') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Admin Notes</label>
                            <textarea name="admin_notes" class="form-control" placeholder="Optional internal notes.">{{ old('admin_notes', $claim->admin_notes) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-ban me-1"></i> Reject Warranty Claim
                        </button>
                    </form>
                @else
                    <div class="wc-note">
                        Rejection is only available while the claim is still pending.
                    </div>
                @endif
            </div>

            <div class="wc-card wc-action-card mt-3">
                <h3 class="wc-section-title">
                    <span class="wc-icon-pill"><i class="fas fa-rotate-left"></i></span>
                    Create Backjob
                </h3>

                @if ($canCreateBackJob)
                    <div class="wc-note">
                        This creates a new backjob linked to the original job order and this warranty claim.
                    </div>

                    <form method="POST" action="{{ route('admin.warranty-claims.create-backjob', $claim) }}" class="wc-form">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Assign Personnel</label>
                            <select name="worker_id" class="form-select">
                                <option value="">Use original assigned personnel</option>
                                @foreach ($workers as $worker)
                                    <option value="{{ $worker->id }}">
                                        {{ $worker->name }} — {{ ucfirst($worker->role) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Backjob Reason</label>
                            <textarea name="reason" class="form-control" required>{{ old('reason', $claim->issue_description) }}</textarea>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Schedule Date</label>
                                <input type="date" name="scheduled_date" class="form-control" value="{{ old('scheduled_date', optional($claim->preferred_date)->format('Y-m-d')) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Schedule Time</label>
                                <input type="time" name="scheduled_time" class="form-control" value="{{ old('scheduled_time', $claim->preferred_time) }}">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Admin Notes</label>
                            <textarea name="admin_notes" class="form-control" placeholder="Optional notes for this backjob.">{{ old('admin_notes') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mt-3">
                            <i class="fas fa-plus-circle me-1"></i> Create Backjob
                        </button>
                    </form>
                @elseif ($backJob)
                    <div class="wc-note wc-success-note">
                        A backjob has already been created for this warranty claim.
                    </div>

                    <a href="{{ route('admin.backjobs.show', $backJob) }}" class="btn btn-success w-100">
                        View Backjob
                    </a>
                @else
                    <div class="wc-note">
                        Backjob creation is available after the warranty claim is approved.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
