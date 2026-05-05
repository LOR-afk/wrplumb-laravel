@extends('admin.layouts.app')

@section('title', 'Job Order Details - WRPlumb')
@section('topbar_title', 'Job Order Details')
@section('topbar_subtitle', 'Review the service execution record.')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/job-orders.css') }}">
@endpush
@section('content')
@php
    $status = $jobOrder->status ?? 'scheduled';
    $statusText = ucwords(str_replace('_', ' ', $status));
    $statusClass = match ($status) {
        'scheduled' => 'scheduled',
        'in_progress' => 'progressing',
        'completed' => 'completed',
        'cancelled' => 'cancelled',
        default => 'neutral',
    };

    $clientName = $jobOrder->quotationRequest->full_name
        ?? trim(($jobOrder->quotationRequest->first_name ?? '') . ' ' . ($jobOrder->quotationRequest->last_name ?? ''));

    $workerName = $jobOrder->worker->name
        ?? trim(($jobOrder->worker->first_name ?? '') . ' ' . ($jobOrder->worker->last_name ?? ''));

    $canStart = $status === 'scheduled';
    $canComplete = in_array($status, ['scheduled', 'in_progress']);
    $canCancel = !in_array($status, ['completed', 'cancelled']);
@endphp

<style>
    .job-order-page {
        max-width: 1240px;
        margin: 0 auto;
    }

    .jo-card {
        background: #ffffff;
        border: 1px solid var(--wr-border);
        border-radius: 22px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    }

    .jo-hero {
        padding: 24px;
        margin-bottom: 20px;
        background:
            radial-gradient(circle at top right, rgba(29, 155, 240, 0.12), transparent 35%),
            linear-gradient(135deg, #ffffff, #f8fbff);
    }

    .jo-hero-title {
        font-size: 1.45rem;
        font-weight: 900;
        color: #0f172a;
        margin-bottom: 6px;
    }

    .jo-hero-subtitle {
        color: #64748b;
        margin-bottom: 0;
        font-weight: 600;
    }

    .jo-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 999px;
        font-weight: 900;
        font-size: 0.86rem;
        white-space: nowrap;
    }

    .jo-status-badge.scheduled {
        background: #eaf4ff;
        color: #1d4ed8;
    }

    .jo-status-badge.progressing {
        background: #fff7ed;
        color: #c2410c;
    }

    .jo-status-badge.completed {
        background: #ecfdf3;
        color: #15803d;
    }

    .jo-status-badge.cancelled {
        background: #fee2e2;
        color: #b91c1c;
    }

    .jo-status-badge.neutral {
        background: #eef2f7;
        color: #475569;
    }

    .jo-summary-card {
        padding: 22px;
        margin-bottom: 20px;
    }

    .jo-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .jo-summary-item {
        min-height: 82px;
        border: 1px solid #edf2f7;
        background: #f8fbff;
        border-radius: 18px;
        padding: 14px 16px;
    }

    .jo-summary-label {
        display: block;
        color: #64748b;
        font-size: 0.76rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 6px;
    }

    .jo-summary-value {
        color: #0f172a;
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .jo-section-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(340px, 0.85fr);
        gap: 20px;
        align-items: start;
    }

    .jo-action-card {
        padding: 22px;
        margin-bottom: 20px;
    }

    .jo-section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.04rem;
        font-weight: 900;
        color: #0f172a;
        margin-bottom: 16px;
    }

    .jo-section-title .icon-pill {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #eaf4ff;
        color: #1d4ed8;
    }

    .jo-section-title.success .icon-pill {
        background: #ecfdf3;
        color: #15803d;
    }

    .jo-section-title.danger {
        color: #b91c1c;
    }

    .jo-section-title.danger .icon-pill {
        background: #fee2e2;
        color: #b91c1c;
    }

    .jo-readonly-block {
        border: 1px solid #edf2f7;
        background: #f8fbff;
        border-radius: 16px;
        padding: 14px 16px;
        color: #334155;
        font-weight: 600;
        line-height: 1.6;
        min-height: 54px;
    }

    .jo-readonly-block + .jo-readonly-block {
        margin-top: 12px;
    }

    .jo-readonly-label {
        color: #64748b;
        display: block;
        font-size: 0.76rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 6px;
    }

    .jo-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 18px;
    }

    .jo-action-button {
        border-radius: 14px !important;
        font-weight: 800 !important;
        padding: 10px 16px !important;
    }

    .jo-danger-zone {
        border-color: #fecaca;
        background: linear-gradient(135deg, #fff7f7, #ffffff);
    }

    .jo-danger-note {
        background: #fff1f2;
        border: 1px solid #fecaca;
        color: #991b1b;
        border-radius: 14px;
        padding: 12px 14px;
        font-size: 0.9rem;
        font-weight: 700;
        margin-bottom: 14px;
    }

    .jo-empty-action {
        border: 1px dashed #cbd5e1;
        background: #f8fafc;
        color: #64748b;
        border-radius: 16px;
        padding: 16px;
        font-weight: 700;
        text-align: center;
    }

    .jo-card textarea.form-control {
        min-height: 126px;
        resize: vertical;
    }

    @media (max-width: 1199.98px) {
        .jo-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .jo-section-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .jo-summary-grid {
            grid-template-columns: 1fr;
        }

        .jo-hero {
            padding: 20px;
        }
    }
</style>

<div class="job-order-page">
    @if (session('info'))
        <div class="alert alert-info mb-4">{{ session('info') }}</div>
    @endif

    <div class="jo-card jo-hero">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <div class="jo-hero-title">
                    <i class="fas fa-clipboard-check me-2 text-primary"></i>
                    {{ $jobOrder->job_order_no }}
                </div>
                <p class="jo-hero-subtitle">
                    {{ $jobOrder->service_type ?? 'Service job order' }} for
                    {{ $clientName ?: 'Unnamed client' }}
                </p>
            </div>

            <span class="jo-status-badge {{ $statusClass }}">
                <i class="fas fa-circle"></i>
                {{ $statusText }}
            </span>
        </div>

        <div class="jo-actions">
            <a href="{{ route('admin.job-orders.index') }}" class="btn btn-outline-secondary jo-action-button">
                <i class="fas fa-arrow-left me-1"></i> Back to Job Orders
            </a>

            <a href="{{ route('admin.quotations.index') }}" class="btn btn-outline-primary jo-action-button">
                <i class="fas fa-file-signature me-1"></i> Back to Requests
            </a>

            @if ($canStart)
                <form method="POST" action="{{ route('admin.job-orders.start', $jobOrder) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-primary jo-action-button">
                        <i class="fas fa-play me-1"></i> Mark In Progress
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="jo-card jo-summary-card">
        <div class="jo-summary-grid">
            <div class="jo-summary-item">
                <span class="jo-summary-label">Job Order No.</span>
                <div class="jo-summary-value">{{ $jobOrder->job_order_no }}</div>
            </div>

            <div class="jo-summary-item">
                <span class="jo-summary-label">Service Flow</span>
                <div class="jo-summary-value text-uppercase">{{ str_replace('_', ' ', $jobOrder->service_flow ?? '—') }}</div>
            </div>

            <div class="jo-summary-item">
                <span class="jo-summary-label">Client</span>
                <div class="jo-summary-value">{{ $clientName ?: '—' }}</div>
            </div>

            <div class="jo-summary-item">
                <span class="jo-summary-label">Assigned Worker</span>
                <div class="jo-summary-value">{{ $workerName ?: 'Not assigned' }}</div>
            </div>

            <div class="jo-summary-item">
                <span class="jo-summary-label">Service Type</span>
                <div class="jo-summary-value">{{ $jobOrder->service_type ?? '—' }}</div>
            </div>

            <div class="jo-summary-item">
                <span class="jo-summary-label">Project Type</span>
                <div class="jo-summary-value">{{ $jobOrder->project_type ?? '—' }}</div>
            </div>

            <div class="jo-summary-item">
                <span class="jo-summary-label">Schedule</span>
                <div class="jo-summary-value">
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

            <div class="jo-summary-item">
                <span class="jo-summary-label">Timeline</span>
                <div class="jo-summary-value">
                    @if ($jobOrder->completed_at)
                        Completed {{ optional($jobOrder->completed_at)->format('Y-m-d h:i A') }}
                    @elseif ($jobOrder->cancelled_at)
                        Cancelled {{ optional($jobOrder->cancelled_at)->format('Y-m-d h:i A') }}
                    @elseif ($jobOrder->started_at)
                        Started {{ optional($jobOrder->started_at)->format('Y-m-d h:i A') }}
                    @else
                        Not yet started
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="jo-section-grid">
        <div>
            <div class="jo-card jo-action-card">
                <h3 class="jo-section-title">
                    <span class="icon-pill"><i class="fas fa-list-check"></i></span>
                    Scope and Current Notes
                </h3>

                <div class="jo-readonly-block">
                    <span class="jo-readonly-label">Scope of Work</span>
                    {{ $jobOrder->scope_of_work ?? 'No scope of work recorded.' }}
                </div>

                <div class="jo-readonly-block">
                    <span class="jo-readonly-label">Work Remarks</span>
                    {{ $jobOrder->work_remarks ?? 'No work remarks yet.' }}
                </div>

                <div class="jo-readonly-block">
                    <span class="jo-readonly-label">Admin Notes</span>
                    {{ $jobOrder->admin_notes ?? 'No admin notes yet.' }}
                </div>

                @if ($jobOrder->completion_notes)
                    <div class="jo-readonly-block">
                        <span class="jo-readonly-label">Completion / Cancellation Notes</span>
                        {{ $jobOrder->completion_notes }}
                    </div>
                @endif
            </div>

            <div class="jo-card jo-action-card">
                <h3 class="jo-section-title">
                    <span class="icon-pill"><i class="fas fa-pen-to-square"></i></span>
                    Update Remarks
                </h3>

                <form method="POST" action="{{ route('admin.job-orders.update-remarks', $jobOrder) }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label class="form-label">Work Remarks</label>
                        <textarea name="work_remarks" class="form-control" rows="4" placeholder="Enter service progress, work updates, or worker remarks.">{{ old('work_remarks', $jobOrder->work_remarks) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Admin Notes</label>
                        <textarea name="admin_notes" class="form-control" rows="4" placeholder="Enter internal notes or instructions for this job order.">{{ old('admin_notes', $jobOrder->admin_notes) }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-outline-primary jo-action-button">
                        <i class="fas fa-save me-1"></i> Save Remarks
                    </button>
                </form>
            </div>
        </div>

        <div>
            <div class="jo-card jo-action-card">
                <h3 class="jo-section-title success">
                    <span class="icon-pill"><i class="fas fa-circle-check"></i></span>
                    Complete Job Order
                </h3>

                @if ($canComplete)
                    <form method="POST" action="{{ route('admin.job-orders.complete', $jobOrder) }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label">Completion Notes</label>
                            <textarea name="completion_notes" class="form-control" rows="4" placeholder="Describe the completed work, findings, or final service notes.">{{ old('completion_notes', $jobOrder->completion_notes) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-success jo-action-button">
                            <i class="fas fa-check me-1"></i> Mark Completed
                        </button>
                    </form>
                @else
                    <div class="jo-empty-action">
                        This job order cannot be marked completed while its current status is {{ $statusText }}.
                    </div>
                @endif
            </div>

            <div class="jo-card jo-action-card jo-danger-zone">
                <h3 class="jo-section-title danger">
                    <span class="icon-pill"><i class="fas fa-triangle-exclamation"></i></span>
                    Danger Zone
                </h3>

                @if ($canCancel)
                    <div class="jo-danger-note">
                        Cancelling this job order will update the service record and should only be done when the work will no longer proceed.
                    </div>

                    <form method="POST" action="{{ route('admin.job-orders.cancel', $jobOrder) }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label">Cancellation Notes</label>
                            <textarea name="completion_notes" class="form-control" rows="4" placeholder="State the reason for cancellation.">{{ old('completion_notes') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-danger jo-action-button">
                            <i class="fas fa-ban me-1"></i> Cancel Job Order
                        </button>
                    </form>
                @else
                    <div class="jo-empty-action">
                        Cancellation is no longer available because this job order is already {{ $statusText }}.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
