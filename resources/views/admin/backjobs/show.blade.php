@extends('admin.layouts.app')

@section('title', 'Backjob Details - WRPlumb')
@section('topbar_title', 'Backjob Details')
@section('topbar_subtitle', 'Manage schedule, progress, and resolution of the backjob.')

@push('styles')
<style>
    .bj-show-page {
        max-width: 1240px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .bj-card {
        background: #ffffff;
        border: 1px solid #dbe7f3;
        border-radius: 22px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.055);
        overflow: hidden;
    }

    .bj-hero {
        padding: 22px;
        background:
            radial-gradient(circle at top right, rgba(29, 155, 240, 0.12), transparent 36%),
            linear-gradient(135deg, #ffffff, #f8fbff);
    }

    .bj-hero-title {
        color: #0f172a;
        font-size: 1.35rem;
        font-weight: 950;
        margin-bottom: 5px;
    }

    .bj-hero-subtitle {
        color: #64748b;
        font-weight: 650;
        margin-bottom: 0;
    }

    .bj-status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 950;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .bj-status-badge.scheduled {
        background: #e8f5ff;
        color: #0f4c81;
    }

    .bj-status-badge.in_progress {
        background: #fff7ed;
        color: #c2410c;
    }

    .bj-status-badge.resolved {
        background: #ecfdf3;
        color: #15803d;
    }

    .bj-status-badge.cancelled {
        background: #fee2e2;
        color: #b91c1c;
    }

    .bj-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        padding: 18px;
    }

    .bj-summary-item {
        min-height: 82px;
        border: 1px solid #edf2f7;
        background: #f8fbff;
        border-radius: 18px;
        padding: 13px 15px;
    }

    .bj-label {
        display: block;
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 6px;
    }

    .bj-value {
        color: #0f172a;
        font-weight: 850;
        overflow-wrap: anywhere;
    }

    .bj-main-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(340px, 0.9fr);
        gap: 16px;
        align-items: start;
    }

    .bj-section {
        padding: 18px;
    }

    .bj-section + .bj-section {
        border-top: 1px solid #dbe7f3;
    }

    .bj-section-title {
        display: flex;
        align-items: center;
        gap: 9px;
        color: #0f172a;
        font-weight: 950;
        font-size: 1rem;
        margin-bottom: 13px;
    }

    .bj-icon-pill {
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        border-radius: 12px;
        background: #e8f5ff;
        color: #0f4c81;
        flex: 0 0 auto;
    }

    .bj-read-box {
        border: 1px solid #edf2f7;
        background: #f8fbff;
        border-radius: 16px;
        padding: 14px 15px;
        color: #334155;
        font-weight: 650;
        line-height: 1.55;
        min-height: 58px;
    }

    .bj-read-box + .bj-read-box {
        margin-top: 10px;
    }

    .bj-actions {
        display: flex;
        gap: 9px;
        flex-wrap: wrap;
        margin-top: 16px;
    }

    .bj-action-card {
        padding: 18px;
    }

    .bj-note {
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

    .bj-danger-note {
        background: #fff1f2;
        border-color: #fecaca;
        color: #991b1b;
    }

    .bj-success-note {
        background: #ecfdf3;
        border-color: #bbf7d0;
        color: #166534;
    }

    .bj-form textarea {
        min-height: 105px;
        resize: vertical;
    }

    @media (max-width: 1200px) {
        .bj-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 992px) {
        .bj-main-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 576px) {
        .bj-summary-grid {
            grid-template-columns: 1fr;
        }

        .bj-hero {
            padding: 18px;
        }
    }
</style>
@endpush

@section('content')
@php
    $claim = $backJob->warrantyClaim;
    $client = $claim?->client;
    $originalJob = $backJob->originalJobOrder;
    $workerName = $backJob->worker?->name ?? 'Not assigned';
    $clientName = $client?->name ?? 'Unknown client';

    $canSchedule = !in_array($backJob->status, ['resolved', 'cancelled']);
    $canStart = $backJob->status === 'scheduled';
    $canResolve = in_array($backJob->status, ['scheduled', 'in_progress']);
    $canCancel = $backJob->status !== 'resolved' && $backJob->status !== 'cancelled';
@endphp

<div class="bj-show-page">
    <div class="bj-card bj-hero">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <div class="bj-hero-title">
                    <i class="fas fa-rotate-left me-2 text-primary"></i>
                    {{ $backJob->backjob_no }}
                </div>
                <p class="bj-hero-subtitle">
                    Backjob for {{ $originalJob->job_order_no ?? 'original job order' }} under warranty claim {{ $claim->claim_no ?? 'N/A' }}.
                </p>
            </div>

            <span class="bj-status-badge {{ $backJob->status }}">
                {{ ucwords(str_replace('_', ' ', $backJob->status)) }}
            </span>
        </div>

        <div class="bj-actions">
            <a href="{{ route('admin.backjobs.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Backjobs
            </a>

            @if ($claim)
                <a href="{{ route('admin.warranty-claims.show', $claim) }}" class="btn btn-outline-primary">
                    <i class="fas fa-shield-halved me-1"></i> View Warranty Claim
                </a>
            @endif

            @if ($originalJob)
                <a href="{{ route('admin.job-orders.show', $originalJob) }}" class="btn btn-outline-primary">
                    <i class="fas fa-clipboard-list me-1"></i> View Original Job
                </a>
            @endif

            @if ($canStart)
                <form method="POST" action="{{ route('admin.backjobs.start', $backJob) }}">
                    @csrf
                    @method('PATCH')
                    <button class="btn btn-primary">
                        <i class="fas fa-play me-1"></i> Start Backjob
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="bj-card">
        <div class="bj-summary-grid">
            <div class="bj-summary-item">
                <span class="bj-label">Backjob No.</span>
                <div class="bj-value">{{ $backJob->backjob_no }}</div>
            </div>

            <div class="bj-summary-item">
                <span class="bj-label">Warranty Claim</span>
                <div class="bj-value">{{ $claim->claim_no ?? 'Not available' }}</div>
            </div>

            <div class="bj-summary-item">
                <span class="bj-label">Client</span>
                <div class="bj-value">{{ $clientName }}</div>
            </div>

            <div class="bj-summary-item">
                <span class="bj-label">Assigned Personnel</span>
                <div class="bj-value">{{ $workerName }}</div>
            </div>

            <div class="bj-summary-item">
                <span class="bj-label">Original Job Order</span>
                <div class="bj-value">{{ $originalJob->job_order_no ?? 'Not available' }}</div>
            </div>

            <div class="bj-summary-item">
                <span class="bj-label">Schedule</span>
                <div class="bj-value">
                    @if ($backJob->scheduled_date)
                        {{ optional($backJob->scheduled_date)->format('Y-m-d') }}
                        @if ($backJob->scheduled_time)
                            <div class="text-muted small">{{ \Carbon\Carbon::parse($backJob->scheduled_time)->format('h:i A') }}</div>
                        @endif
                    @else
                        Not scheduled
                    @endif
                </div>
            </div>

            <div class="bj-summary-item">
                <span class="bj-label">Created By</span>
                <div class="bj-value">{{ $backJob->creator->name ?? 'System' }}</div>
            </div>

            <div class="bj-summary-item">
                <span class="bj-label">Timeline</span>
                <div class="bj-value">
                    @if ($backJob->resolved_at)
                        Resolved {{ optional($backJob->resolved_at)->format('Y-m-d h:i A') }}
                    @elseif ($backJob->cancelled_at)
                        Cancelled {{ optional($backJob->cancelled_at)->format('Y-m-d h:i A') }}
                    @elseif ($backJob->started_at)
                        Started {{ optional($backJob->started_at)->format('Y-m-d h:i A') }}
                    @else
                        Not yet started
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="bj-main-grid">
        <div class="bj-card">
            <div class="bj-section">
                <h3 class="bj-section-title">
                    <span class="bj-icon-pill"><i class="fas fa-circle-info"></i></span>
                    Backjob Information
                </h3>

                <div class="bj-read-box">
                    <span class="bj-label">Reason</span>
                    {{ $backJob->reason ?? 'No reason recorded.' }}
                </div>

                <div class="bj-read-box">
                    <span class="bj-label">Admin Notes</span>
                    {{ $backJob->admin_notes ?? 'No admin notes recorded.' }}
                </div>

                <div class="bj-read-box">
                    <span class="bj-label">Resolution Notes</span>
                    {{ $backJob->resolution_notes ?? 'No resolution notes yet.' }}
                </div>
            </div>

            <div class="bj-section">
                <h3 class="bj-section-title">
                    <span class="bj-icon-pill"><i class="fas fa-triangle-exclamation"></i></span>
                    Warranty Claim Issue
                </h3>

                <div class="bj-read-box">
                    <span class="bj-label">Client Reported Issue</span>
                    {{ $claim->issue_description ?? 'No issue description available.' }}
                </div>
            </div>
        </div>

        <div>
            <div class="bj-card bj-action-card">
                <h3 class="bj-section-title">
                    <span class="bj-icon-pill"><i class="fas fa-calendar-check"></i></span>
                    Schedule / Reassign
                </h3>

                @if ($canSchedule)
                    <div class="bj-note">
                        Update the assigned personnel and schedule for the backjob.
                    </div>

                    <form method="POST" action="{{ route('admin.backjobs.schedule', $backJob) }}" class="bj-form">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label">Assign Personnel</label>
                            <select name="worker_id" class="form-select" required>
                                <option value="">Select personnel</option>
                                @foreach ($workers as $worker)
                                    <option value="{{ $worker->id }}" @selected($backJob->worker_id === $worker->id)>
                                        {{ $worker->name }} — {{ ucfirst($worker->role) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Schedule Date</label>
                                <input type="date" name="scheduled_date" class="form-control" value="{{ old('scheduled_date', optional($backJob->scheduled_date)->format('Y-m-d')) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Schedule Time</label>
                                <input type="time" name="scheduled_time" class="form-control" value="{{ old('scheduled_time', $backJob->scheduled_time) }}" required>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Admin Notes</label>
                            <textarea name="admin_notes" class="form-control" placeholder="Optional notes for the assigned personnel.">{{ old('admin_notes', $backJob->admin_notes) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mt-3">
                            <i class="fas fa-save me-1"></i> Save Schedule
                        </button>
                    </form>
                @else
                    <div class="bj-note">
                        Schedule updates are no longer available because this backjob is {{ ucwords(str_replace('_', ' ', $backJob->status)) }}.
                    </div>
                @endif
            </div>

            <div class="bj-card bj-action-card mt-3">
                <h3 class="bj-section-title">
                    <span class="bj-icon-pill"><i class="fas fa-circle-check"></i></span>
                    Resolve Backjob
                </h3>

                @if ($canResolve)
                    <div class="bj-note bj-success-note">
                        Mark this backjob as resolved once the repeat issue has been fixed.
                    </div>

                    <form method="POST" action="{{ route('admin.backjobs.resolve', $backJob) }}" class="bj-form">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label">Resolution Notes</label>
                            <textarea name="resolution_notes" class="form-control" placeholder="Describe what was repaired or resolved." required>{{ old('resolution_notes', $backJob->resolution_notes) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-check me-1"></i> Mark Resolved
                        </button>
                    </form>
                @else
                    <div class="bj-note">
                        Resolve action is not available for the current status.
                    </div>
                @endif
            </div>

            <div class="bj-card bj-action-card mt-3">
                <h3 class="bj-section-title">
                    <span class="bj-icon-pill"><i class="fas fa-ban"></i></span>
                    Cancel Backjob
                </h3>

                @if ($canCancel)
                    <div class="bj-note bj-danger-note">
                        Cancel only if this backjob will no longer proceed.
                    </div>

                    <form method="POST" action="{{ route('admin.backjobs.cancel', $backJob) }}" class="bj-form">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label">Cancellation Notes</label>
                            <textarea name="resolution_notes" class="form-control" placeholder="Optional reason for cancellation.">{{ old('resolution_notes') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-ban me-1"></i> Cancel Backjob
                        </button>
                    </form>
                @else
                    <div class="bj-note">
                        Cancellation is no longer available.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
