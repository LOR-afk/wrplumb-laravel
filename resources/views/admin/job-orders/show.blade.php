@extends('admin.layouts.app')

@section('title', 'Job Order Details - WRPlumb')
@section('topbar_title', 'Job Order Details')
@section('topbar_subtitle', 'Review the service execution record.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/job-orders.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/job-order-details.css') }}">
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
    $canComplete = in_array($status, ['scheduled', 'in_progress'], true);

    $canReschedule = in_array($status, ['scheduled', 'in_progress'], true);
    $canReassign = in_array($status, ['scheduled', 'in_progress'], true);

    $warrantyClaims = $jobOrder->warrantyClaims()->with(['backJob', 'client'])->latest()->get();
    $backJobs = $jobOrder->backJobs()->with(['warrantyClaim', 'worker'])->latest()->get();

    $latestWarrantyClaim = $warrantyClaims->first();

    $warrantyExpiresAt = $jobOrder->completed_at
        ? $jobOrder->completed_at->copy()->addDays(30)
        : null;

    $isWithinWarranty = $warrantyExpiresAt
        ? now()->lessThanOrEqualTo($warrantyExpiresAt)
        : false;

    $warrantyStatusText = match (true) {
        $status !== 'completed' => 'Available after completion',
        $isWithinWarranty => 'Within 30-day warranty',
        default => 'Warranty period ended',
    };

    $warrantyStatusClass = match (true) {
        $status !== 'completed' => 'neutral',
        $isWithinWarranty => 'eligible',
        default => 'expired',
    };

    $scheduledLabel = $jobOrder->scheduled_date
        ? optional($jobOrder->scheduled_date)->format('Y-m-d')
            . ($jobOrder->scheduled_time
                ? ' • ' . \Carbon\Carbon::parse($jobOrder->scheduled_time)->format('h:i A')
                : '')
        : 'Not scheduled';

    $timelineLabel = match (true) {
        !empty($jobOrder->completed_at) => 'Completed ' . optional($jobOrder->completed_at)->format('Y-m-d h:i A'),
        !empty($jobOrder->cancelled_at) => 'Cancelled ' . optional($jobOrder->cancelled_at)->format('Y-m-d h:i A'),
        !empty($jobOrder->started_at) => 'Started ' . optional($jobOrder->started_at)->format('Y-m-d h:i A'),
        default => 'Not yet started',
    };
@endphp

<div class="jo-details-page">
    @if (session('info'))
        <div class="alert alert-info mb-3">{{ session('info') }}</div>
    @endif

    @if (session('success'))
        <div class="alert alert-success mb-3">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mb-3">{{ $errors->first() }}</div>
    @endif

    <section class="jo-details-hero">
        <div class="jo-details-hero-main">
            <div class="jo-details-hero-icon">
                <i class="fas fa-clipboard-check"></i>
            </div>

            <div class="jo-details-hero-copy">
                <div class="jo-details-hero-title-row">
                    <h1>{{ $jobOrder->job_order_no }}</h1>
                    <span class="jo-status-badge {{ $statusClass }}">
                        <i class="fas fa-circle-check"></i>
                        {{ $statusText }}
                    </span>
                </div>

                <p>{{ $jobOrder->service_type ?? 'Service job order' }} for {{ $clientName ?: 'Unnamed client' }}</p>
            </div>
        </div>

        <div class="jo-details-hero-actions">
            <a href="{{ route('admin.job-orders.index') }}" class="btn btn-outline-primary jo-details-btn">
                <i class="fas fa-arrow-left me-1"></i>
                Back to Job Orders
            </a>

            <a href="{{ route('admin.quotations.index') }}" class="btn btn-outline-primary jo-details-btn">
                <i class="fas fa-file-signature me-1"></i>
                Back to Requests
            </a>

            @if ($canStart)
                <form method="POST" action="{{ route('admin.job-orders.start', $jobOrder) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-primary jo-details-btn">
                        <i class="fas fa-play me-1"></i>
                        Mark In Progress
                    </button>
                </form>
            @endif
        </div>
    </section>

    <div class="jo-details-top-grid">
        <section class="jo-details-card jo-information-card">
            <div class="jo-details-card-head">
                <span class="jo-card-icon blue"><i class="fas fa-circle-info"></i></span>
                <h2>Job Information</h2>
            </div>

            <div class="jo-information-grid">
                <div class="jo-information-item">
                    <span class="jo-information-icon"><i class="fas fa-hashtag"></i></span>
                    <div>
                        <span>Job Order No.</span>
                        <strong>{{ $jobOrder->job_order_no }}</strong>
                    </div>
                </div>

                <div class="jo-information-item">
                    <span class="jo-information-icon"><i class="fas fa-code-branch"></i></span>
                    <div>
                        <span>Service Flow</span>
                        <strong>{{ ucwords(str_replace('_', ' ', $jobOrder->service_flow ?? '—')) }}</strong>
                    </div>
                </div>

                <div class="jo-information-item">
                    <span class="jo-information-icon"><i class="fas fa-user"></i></span>
                    <div>
                        <span>Client</span>
                        <strong>{{ $clientName ?: '—' }}</strong>
                    </div>
                </div>

                <div class="jo-information-item">
                    <span class="jo-information-icon"><i class="fas fa-user-gear"></i></span>
                    <div>
                        <span>Assigned Worker</span>
                        <strong>{{ $workerName ?: 'Not assigned' }}</strong>
                    </div>
                </div>

                <div class="jo-information-item">
                    <span class="jo-information-icon"><i class="fas fa-screwdriver-wrench"></i></span>
                    <div>
                        <span>Service Type</span>
                        <strong>{{ $jobOrder->service_type ?? '—' }}</strong>
                    </div>
                </div>

                <div class="jo-information-item">
                    <span class="jo-information-icon"><i class="fas fa-house"></i></span>
                    <div>
                        <span>Project Type</span>
                        <strong>{{ $jobOrder->project_type ?? '—' }}</strong>
                    </div>
                </div>

                <div class="jo-information-item">
                    <span class="jo-information-icon"><i class="fas fa-calendar-days"></i></span>
                    <div>
                        <span>Schedule</span>
                        <strong>{{ $scheduledLabel }}</strong>
                    </div>
                </div>

                <div class="jo-information-item">
                    <span class="jo-information-icon"><i class="fas fa-clock"></i></span>
                    <div>
                        <span>Timeline</span>
                        <strong>{{ $timelineLabel }}</strong>
                    </div>
                </div>
            </div>
        </section>

        <aside class="jo-details-card jo-timeline-card">
            <div class="jo-details-card-head">
                <span class="jo-card-icon green"><i class="fas fa-circle-check"></i></span>
                <h2>Service Timeline</h2>
            </div>

            <div class="jo-service-timeline">
                <div class="jo-timeline-step done">
                    <span class="jo-timeline-dot"><i class="fas fa-calendar-check"></i></span>
                    <div>
                        <strong>Site Inspection</strong>
                        <small>{{ $scheduledLabel }}</small>
                    </div>
                </div>

                <div class="jo-timeline-step {{ in_array($status, ['in_progress', 'completed'], true) ? 'done' : '' }}">
                    <span class="jo-timeline-dot"><i class="fas fa-screwdriver-wrench"></i></span>
                    <div>
                        <strong>Service Execution</strong>
                        <small>
                            {{ $jobOrder->started_at
                                ? optional($jobOrder->started_at)->format('Y-m-d • h:i A')
                                : ($status === 'scheduled' ? 'Pending service execution' : 'Service execution underway') }}
                        </small>
                    </div>
                </div>

                <div class="jo-timeline-step {{ $status === 'completed' ? 'done final' : '' }}">
                    <span class="jo-timeline-dot"><i class="fas fa-check"></i></span>
                    <div>
                        <strong>Completed</strong>
                        <small>{{ $jobOrder->completed_at ? optional($jobOrder->completed_at)->format('Y-m-d • h:i A') : 'Pending completion' }}</small>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <div class="jo-details-main-grid">
        <section class="jo-details-card jo-notes-card">
            <div class="jo-details-card-head">
                <span class="jo-card-icon blue"><i class="fas fa-list-check"></i></span>
                <h2>Scope and Current Notes</h2>
            </div>

            <div class="jo-notes-layout">
                <div class="jo-note-stack">
                    <div class="jo-note-block">
                        <span>Scope of Work</span>
                        <p>{{ $jobOrder->scope_of_work ?? 'No scope of work recorded.' }}</p>
                    </div>

                    <div class="jo-note-block">
                        <span>Work Remarks</span>
                        <p>{{ $jobOrder->work_remarks ?? 'No work remarks yet.' }}</p>
                    </div>

                    <div class="jo-note-block">
                        <span>Admin Notes</span>
                        <p>{{ $jobOrder->admin_notes ?? 'No admin notes yet.' }}</p>
                    </div>

                    @if ($jobOrder->completion_notes)
                        <div class="jo-note-block">
                            <span>Completion / Cancellation Notes</span>
                            <p>{{ $jobOrder->completion_notes }}</p>
                        </div>
                    @endif
                </div>

                <div class="jo-remarks-editor">
                    <div class="jo-subsection-title">
                        <i class="fas fa-pen-to-square"></i>
                        <span>Update Remarks</span>
                    </div>

                    <form method="POST" action="{{ route('admin.job-orders.update-remarks', $jobOrder) }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label">Work Remarks</label>
                            <textarea
                                name="work_remarks"
                                class="form-control"
                                rows="5"
                                placeholder="Enter service progress, work updates, or worker remarks."
                            >{{ old('work_remarks', $jobOrder->work_remarks) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Admin Notes</label>
                            <textarea
                                name="admin_notes"
                                class="form-control"
                                rows="4"
                                placeholder="Enter internal notes or instructions for this job order."
                            >{{ old('admin_notes', $jobOrder->admin_notes) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary jo-save-remarks">
                            <i class="fas fa-floppy-disk me-1"></i>
                            Save Remarks
                        </button>
                    </form>
                </div>
            </div>
        </section>

        <aside class="jo-details-side-stack">
            <section class="jo-details-card jo-utility-card">
                <div class="jo-utility-head">
                    <div class="jo-details-card-head mb-0">
                        <span class="jo-card-icon blue"><i class="fas fa-shield-halved"></i></span>
                        <h2>Warranty Monitoring</h2>
                    </div>

                    <span class="jo-warranty-badge {{ $warrantyStatusClass }}">
                        {{ $warrantyStatusText }}
                    </span>
                </div>

                <p class="jo-utility-copy">
                    @if ($status === 'completed')
                        Warranty coverage is active based on the 30-day service period.
                    @else
                        Warranty tracking becomes available after this job order is completed.
                    @endif
                </p>

                <div class="jo-utility-meta-grid">
                    <div>
                        <span>Warranty Until</span>
                        <strong>{{ $warrantyExpiresAt ? $warrantyExpiresAt->format('Y-m-d h:i A') : 'Not available' }}</strong>
                    </div>
                    <div>
                        <span>Total Claims</span>
                        <strong>{{ $warrantyClaims->count() }}</strong>
                    </div>
                </div>

                @if ($latestWarrantyClaim)
                    <div class="jo-mini-record">
                        <div>
                            <span>Latest Claim</span>
                            <strong>{{ $latestWarrantyClaim->claim_no }}</strong>
                        </div>

                        <a href="{{ route('admin.warranty-claims.show', $latestWarrantyClaim) }}" class="btn btn-sm btn-outline-primary">
                            Review
                        </a>
                    </div>
                @endif
            </section>

            <section class="jo-details-card jo-utility-card">
                <div class="jo-utility-head">
                    <div class="jo-details-card-head mb-0">
                        <span class="jo-card-icon blue"><i class="fas fa-rotate-left"></i></span>
                        <h2>Backjob Tracking</h2>
                    </div>

                    <span class="jo-count-badge">{{ $backJobs->count() }}</span>
                </div>

                <p class="jo-utility-copy">Track backjob or follow-up actions related to this job order.</p>

                @if ($backJobs->count())
                    <div class="jo-mini-list">
                        @foreach ($backJobs as $backJob)
                            <div class="jo-mini-record">
                                <div>
                                    <span>{{ $backJob->backjob_no }}</span>
                                    <strong>{{ ucwords(str_replace('_', ' ', $backJob->status)) }}</strong>
                                </div>

                                <a href="{{ route('admin.backjobs.show', $backJob) }}" class="btn btn-sm btn-outline-primary">
                                    Open
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="jo-utility-empty">No backjob has been created for this job order.</div>
                @endif
            </section>

            @if ($canComplete || $canReschedule || $canReassign)
                @if ($canComplete)
                    <section class="jo-details-card jo-action-state jo-action-state-success">
                        <div class="jo-details-card-head">
                            <span class="jo-card-icon green"><i class="fas fa-circle-check"></i></span>
                            <h2>Complete Job Order</h2>
                        </div>

                        <form method="POST" action="{{ route('admin.job-orders.complete', $jobOrder) }}">
                            @csrf
                            @method('PATCH')

                            <div class="mb-3">
                                <label class="form-label">Completion Notes</label>
                                <textarea
                                    name="completion_notes"
                                    class="form-control"
                                    rows="4"
                                    placeholder="Describe the completed work, findings, or final service notes."
                                >{{ old('completion_notes', $jobOrder->completion_notes) }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-success jo-details-btn">
                                <i class="fas fa-check me-1"></i>
                                Mark Completed
                            </button>
                        </form>
                    </section>
                @endif

                <section class="jo-details-card jo-service-controls">
                    <div class="jo-details-card-head">
                        <span class="jo-card-icon blue"><i class="fas fa-sliders"></i></span>
                        <div>
                            <h2>Service Controls</h2>
                            <p class="jo-service-controls-subtitle">
                                Adjust the active job order without cancelling the issued record.
                            </p>
                        </div>
                    </div>

                    <div class="jo-service-control-grid">
                        @if ($canReschedule)
                            <button
                                type="button"
                                class="jo-service-control-item"
                                data-bs-toggle="modal"
                                data-bs-target="#rescheduleJobOrderModal"
                            >
                                <span class="jo-service-control-icon blue">
                                    <i class="fas fa-calendar-days"></i>
                                </span>
                                <span>
                                    <strong>Reschedule Service</strong>
                                    <small>Change the scheduled service date or time.</small>
                                </span>
                                <i class="fas fa-chevron-right jo-service-control-arrow"></i>
                            </button>
                        @endif

                        @if ($canReassign)
                            <button
                                type="button"
                                class="jo-service-control-item"
                                data-bs-toggle="modal"
                                data-bs-target="#reassignJobOrderModal"
                            >
                                <span class="jo-service-control-icon purple">
                                    <i class="fas fa-user-gear"></i>
                                </span>
                                <span>
                                    <strong>Reassign Worker</strong>
                                    <small>Transfer this job order to another worker.</small>
                                </span>
                                <i class="fas fa-chevron-right jo-service-control-arrow"></i>
                            </button>
                        @endif
                    </div>
                </section>
            @else
                <section class="jo-details-card jo-final-status-card {{ $statusClass }}">
                    <div class="jo-final-status-icon">
                        @if ($status === 'completed')
                            <i class="fas fa-circle-check"></i>
                        @elseif ($status === 'cancelled')
                            <i class="fas fa-ban"></i>
                        @else
                            <i class="fas fa-circle-info"></i>
                        @endif
                    </div>

                    <div class="jo-final-status-copy">
                        <span>Job Order Status</span>
                        <strong>{{ $statusText }}</strong>

                        <p>
                            @if ($status === 'completed')
                                This job order is complete. No further completion or service adjustment is required.
                            @elseif ($status === 'cancelled')
                                This job order has been closed and no further service action is available.
                            @else
                                No additional status action is currently available.
                            @endif
                        </p>
                    </div>
                </section>
            @endif

        </aside>
    </div>

    <div class="modal fade" id="rescheduleJobOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content jo-control-modal">
                <form method="POST" action="{{ route('admin.job-orders.reschedule', $jobOrder) }}">
                    @csrf
                    @method('PATCH')

                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">Reschedule Service</h5>
                            <p class="text-muted mb-0">Update the service date or time while keeping the same job order.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Service Date</label>
                            <input
                                type="date"
                                name="scheduled_date"
                                class="form-control"
                                value="{{ old('scheduled_date', optional($jobOrder->scheduled_date)->format('Y-m-d')) }}"
                                required
                            >
                        </div>

                        <div>
                            <label class="form-label">Service Time</label>
                            <input
                                type="time"
                                name="scheduled_time"
                                class="form-control"
                                value="{{ old('scheduled_time', $jobOrder->scheduled_time ? \Carbon\Carbon::parse($jobOrder->scheduled_time)->format('H:i') : '') }}"
                            >
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-calendar-check me-1"></i>
                            Save Schedule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="reassignJobOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content jo-control-modal">
                <form method="POST" action="{{ route('admin.job-orders.reassign', $jobOrder) }}">
                    @csrf
                    @method('PATCH')

                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">Reassign Worker</h5>
                            <p class="text-muted mb-0">Transfer this active job order to another worker or inspector.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <label class="form-label">Assigned Worker</label>
                        <select name="worker_id" class="form-select" required>
                            <option value="">Select worker</option>

                            @foreach (($availableWorkers ?? collect()) as $worker)
                                @php
                                    $workerLabel = $worker->name
                                        ?? trim(($worker->first_name ?? '') . ' ' . ($worker->last_name ?? ''))
                                        ?: $worker->email;
                                @endphp

                                <option
                                    value="{{ $worker->id }}"
                                    @selected((string) old('worker_id', $jobOrder->worker_id) === (string) $worker->id)
                                >
                                    {{ $workerLabel }} — {{ ucfirst($worker->role) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-check me-1"></i>
                            Reassign Worker
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection