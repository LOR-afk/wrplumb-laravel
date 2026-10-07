@extends('admin.layouts.app')

@section('title', 'Job Order Details - WRPlumb')
@section('topbar_title', 'Job Order Details')
@section('topbar_subtitle', 'Review and manage the job order.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/job-orders.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/job-order-details.css') }}">
@endpush

@section('content')
@php
    $status = $jobOrder->status ?? 'pending';
    $statusText = ucwords(str_replace('_', ' ', $status));

    $jobType = strtolower((string) ($jobOrder->job_type ?? 'service'));
    $isInspection = $jobType === 'inspection';
    $isService = !$isInspection;

    $jobTypeLabel = $isInspection ? 'Inspection Job Order' : 'Actual Service Job Order';
    $jobTypeShort = $isInspection ? 'Inspection' : 'Actual Service';

    $statusClass = match ($status) {
        'pending' => 'pending',
        'scheduled' => 'scheduled',
        'in_progress' => 'progressing',
        'completed' => 'completed',
        'cancelled' => 'cancelled',
        default => 'neutral',
    };

    $request = $jobOrder->quotationRequest;
    $clientName = $request?->full_name
        ?? trim(($request?->first_name ?? '') . ' ' . ($request?->last_name ?? ''));

    $workerName = $jobOrder->worker?->name
        ?? trim(($jobOrder->worker?->first_name ?? '') . ' ' . ($jobOrder->worker?->last_name ?? ''));

    $hasWorker = !empty($jobOrder->worker_id);
    $hasSchedule = !empty($jobOrder->scheduled_date);
    $isFinal = in_array($status, ['completed', 'cancelled'], true);

    $canStart = $isService
        && $status === 'scheduled'
        && $hasWorker
        && $hasSchedule;

    $canComplete = $isService && $status === 'in_progress';
    $canReschedule = !$isFinal && in_array($status, ['pending', 'scheduled', 'in_progress'], true);
    $canReassign = !$isFinal && in_array($status, ['pending', 'scheduled', 'in_progress'], true);

    $scheduledLabel = $jobOrder->scheduled_date
        ? optional($jobOrder->scheduled_date)->format('M d, Y')
            . ($jobOrder->scheduled_time
                ? ' • ' . \Carbon\Carbon::parse($jobOrder->scheduled_time)->format('h:i A')
                : '')
        : 'Not scheduled';

    $acceptedAt = $request?->quotation?->accepted_at;
    $inspectionReport = $request?->inspectionReport;

    $warrantyClaims = $isService
        ? $jobOrder->warrantyClaims()->with(['backJob', 'client'])->latest()->get()
        : collect();

    $backJobs = $isService
        ? $jobOrder->backJobs()->with(['warrantyClaim', 'worker'])->latest()->get()
        : collect();

    $latestWarrantyClaim = $warrantyClaims->first();

    $warrantyExpiresAt = $isService && $jobOrder->completed_at
        ? $jobOrder->completed_at->copy()->addDays(30)
        : null;

    $isWithinWarranty = $warrantyExpiresAt
        ? now()->lessThanOrEqualTo($warrantyExpiresAt)
        : false;

    $nextStepTitle = match (true) {
        $status === 'cancelled' => 'Job order cancelled',
        $status === 'completed' && $isInspection => 'Inspection complete',
        $status === 'completed' => 'Service complete',
        $isInspection => 'Inspector manages this job',
        !$hasWorker && !$hasSchedule => 'Assign worker and set schedule',
        !$hasWorker => 'Assign a worker',
        !$hasSchedule => 'Set the service schedule',
        $status === 'scheduled' => 'Ready to start service',
        $status === 'in_progress' => 'Complete the actual service',
        default => 'Review job order',
    };

    $nextStepCopy = match (true) {
        $status === 'cancelled' => 'No further execution action is available for this job order.',
        $status === 'completed' && $isInspection => 'The inspection is finished. Actual repair or installation is handled by the separate service job order.',
        $status === 'completed' => 'Actual service work is complete. Warranty and backjob tracking are now available.',
        $isInspection => 'Inspection progress and completion are controlled by the assigned inspector from the inspector portal.',
        !$hasWorker && !$hasSchedule => 'Choose the worker responsible for the actual work and set the service date and time.',
        !$hasWorker => 'Choose the worker who will perform the actual service.',
        !$hasSchedule => 'Set the actual service date and time.',
        $status === 'scheduled' => 'Worker and schedule are ready. Start the service when work begins.',
        $status === 'in_progress' => 'Record the completion notes once the repair or installation is finished.',
        default => 'Review the current job details before proceeding.',
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
                <i class="fas {{ $isInspection ? 'fa-magnifying-glass' : 'fa-screwdriver-wrench' }}"></i>
            </div>

            <div class="jo-details-hero-copy">
                <div class="jo-details-hero-title-row">
                    <h1>{{ $jobOrder->job_order_no }}</h1>

                    <span class="jo-status-badge {{ $statusClass }}">
                        {{ $statusText }}
                    </span>

                    <span class="jo-status-badge type">
                        {{ $jobTypeShort }}
                    </span>
                </div>

                <p>{{ $jobOrder->service_type ?? 'Service' }} • {{ $clientName ?: 'Unnamed client' }}</p>
            </div>
        </div>

        <div class="jo-details-hero-actions">
            @if ($canStart)
                <form method="POST" action="{{ route('admin.job-orders.start', $jobOrder) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-primary jo-details-btn">
                        <i class="fas fa-play me-1"></i>
                        Start Service
                    </button>
                </form>
            @endif

            <a href="{{ route('admin.job-orders.index') }}" class="btn btn-outline-primary jo-details-btn">
                <i class="fas fa-arrow-left me-1"></i>
                Job Orders
            </a>

            <a href="{{ route('admin.quotations.index') }}" class="btn btn-outline-secondary jo-details-btn">
                Requests
            </a>
        </div>
    </section>

    <section class="jo-details-card jo-overview-card">
        <div class="jo-details-card-head">
            <span class="jo-card-icon blue"><i class="fas fa-circle-info"></i></span>
            <div>
                <h2>Job Overview</h2>
                <p>Key details for this {{ strtolower($jobTypeLabel) }}.</p>
            </div>
        </div>

        <div class="jo-overview-grid">
            <div class="jo-overview-item">
                <span>Client</span>
                <strong>{{ $clientName ?: '—' }}</strong>
            </div>

            <div class="jo-overview-item">
                <span>{{ $isInspection ? 'Inspector' : 'Worker' }}</span>
                <strong>{{ $workerName ?: 'Not assigned' }}</strong>
            </div>

            <div class="jo-overview-item">
                <span>Schedule</span>
                <strong>{{ $scheduledLabel }}</strong>
            </div>

            <div class="jo-overview-item">
                <span>Project Type</span>
                <strong>{{ $jobOrder->project_type ?? '—' }}</strong>
            </div>

            <div class="jo-overview-item">
                <span>Job Type</span>
                <strong>{{ $jobTypeLabel }}</strong>
            </div>

            <div class="jo-overview-item">
                <span>Current Status</span>
                <strong>{{ $statusText }}</strong>
            </div>
        </div>
    </section>

    <section class="jo-details-card jo-flow-card">
        <div class="jo-details-card-head jo-flow-head">
            <span class="jo-card-icon green"><i class="fas fa-route"></i></span>
            <div>
                <h2>{{ $isInspection ? 'Inspection Progress' : 'Service Progress' }}</h2>
                <p>{{ $isInspection ? 'Track the inspection milestones.' : 'Track the actual service execution from acceptance to completion.' }}</p>
            </div>
        </div>

        <div class="jo-flow-track">
            @if ($isInspection)
                <div class="jo-flow-step is-done">
                    <span class="jo-flow-dot"><i class="fas fa-file-circle-plus"></i></span>
                    <strong>Created</strong>
                    <small>{{ optional($jobOrder->created_at)->format('M d, Y') }}</small>
                </div>

                <div class="jo-flow-step {{ $hasWorker ? 'is-done' : '' }}">
                    <span class="jo-flow-dot"><i class="fas fa-user-check"></i></span>
                    <strong>Inspector</strong>
                    <small>{{ $hasWorker ? ($workerName ?: 'Assigned') : 'Not assigned' }}</small>
                </div>

                <div class="jo-flow-step {{ $hasSchedule ? 'is-done' : '' }}">
                    <span class="jo-flow-dot"><i class="fas fa-calendar-check"></i></span>
                    <strong>Scheduled</strong>
                    <small>{{ $hasSchedule ? $scheduledLabel : 'Not scheduled' }}</small>
                </div>

                <div class="jo-flow-step {{ ($jobOrder->started_at || in_array($status, ['in_progress', 'completed'], true)) ? 'is-done' : '' }}">
                    <span class="jo-flow-dot"><i class="fas fa-magnifying-glass"></i></span>
                    <strong>Inspection</strong>
                    <small>{{ $jobOrder->started_at ? optional($jobOrder->started_at)->format('M d, Y h:i A') : 'Not started' }}</small>
                </div>

                <div class="jo-flow-step {{ $status === 'completed' ? 'is-done is-final' : '' }}">
                    <span class="jo-flow-dot"><i class="fas fa-check"></i></span>
                    <strong>Completed</strong>
                    <small>{{ $jobOrder->completed_at ? optional($jobOrder->completed_at)->format('M d, Y h:i A') : 'Pending' }}</small>
                </div>
            @else
                <div class="jo-flow-step is-done">
                    <span class="jo-flow-dot"><i class="fas fa-file-signature"></i></span>
                    <strong>Quotation Accepted</strong>
                    <small>{{ $acceptedAt ? optional($acceptedAt)->format('M d, Y h:i A') : 'Accepted' }}</small>
                </div>

                <div class="jo-flow-step {{ $hasWorker ? 'is-done' : '' }}">
                    <span class="jo-flow-dot"><i class="fas fa-user-gear"></i></span>
                    <strong>Worker Assigned</strong>
                    <small>{{ $hasWorker ? ($workerName ?: 'Assigned') : 'Not assigned' }}</small>
                </div>

                <div class="jo-flow-step {{ $hasSchedule ? 'is-done' : '' }}">
                    <span class="jo-flow-dot"><i class="fas fa-calendar-check"></i></span>
                    <strong>Scheduled</strong>
                    <small>{{ $hasSchedule ? $scheduledLabel : 'Not scheduled' }}</small>
                </div>

                <div class="jo-flow-step {{ ($jobOrder->started_at || in_array($status, ['in_progress', 'completed'], true)) ? 'is-done' : '' }}">
                    <span class="jo-flow-dot"><i class="fas fa-screwdriver-wrench"></i></span>
                    <strong>In Progress</strong>
                    <small>{{ $jobOrder->started_at ? optional($jobOrder->started_at)->format('M d, Y h:i A') : 'Not started' }}</small>
                </div>

                <div class="jo-flow-step {{ $status === 'completed' ? 'is-done is-final' : '' }}">
                    <span class="jo-flow-dot"><i class="fas fa-check"></i></span>
                    <strong>Completed</strong>
                    <small>{{ $jobOrder->completed_at ? optional($jobOrder->completed_at)->format('M d, Y h:i A') : 'Pending' }}</small>
                </div>
            @endif
        </div>
    </section>

    <div class="jo-workspace-grid">
        <section class="jo-details-card jo-work-details-card">
            <div class="jo-details-card-head">
                <span class="jo-card-icon blue"><i class="fas fa-list-check"></i></span>
                <div>
                    <h2>Work Details</h2>
                    <p>Scope, current remarks, and internal notes.</p>
                </div>
            </div>

            <div class="jo-work-detail-list">
                <div class="jo-work-detail-row">
                    <span>Scope of Work</span>
                    <p>{{ $jobOrder->scope_of_work ?: 'No scope of work recorded.' }}</p>
                </div>

                <div class="jo-work-detail-row">
                    <span>Work Remarks</span>
                    <p>{{ $jobOrder->work_remarks ?: 'No work remarks yet.' }}</p>
                </div>

                <div class="jo-work-detail-row">
                    <span>Admin Notes</span>
                    <p>{{ $jobOrder->admin_notes ?: 'No admin notes yet.' }}</p>
                </div>

                @if ($jobOrder->completion_notes)
                    <div class="jo-work-detail-row">
                        <span>Completion Notes</span>
                        <p>{{ $jobOrder->completion_notes }}</p>
                    </div>
                @endif
            </div>

            @if (!$isFinal)
                <details class="jo-edit-details">
                    <summary>
                        <span><i class="fas fa-pen-to-square me-2"></i>Edit remarks</span>
                        <i class="fas fa-chevron-down"></i>
                    </summary>

                    <form method="POST" action="{{ route('admin.job-orders.update-remarks', $jobOrder) }}" class="jo-edit-form">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label class="form-label">Work Remarks</label>
                            <textarea name="work_remarks" class="form-control" rows="4" placeholder="Enter service progress or work updates.">{{ old('work_remarks', $jobOrder->work_remarks) }}</textarea>
                        </div>

                        <div>
                            <label class="form-label">Admin Notes</label>
                            <textarea name="admin_notes" class="form-control" rows="4" placeholder="Enter internal notes or instructions.">{{ old('admin_notes', $jobOrder->admin_notes) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-floppy-disk me-1"></i>
                            Save Remarks
                        </button>
                    </form>
                </details>
            @endif
        </section>

        <aside class="jo-action-column">
            <section class="jo-details-card jo-next-action-card">
                <span class="jo-next-action-label">Next Step</span>
                <h2>{{ $nextStepTitle }}</h2>
                <p>{{ $nextStepCopy }}</p>

                @if (!$isFinal && $isService)
                    <div class="jo-primary-actions">
                        @if ($canReassign)
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reassignJobOrderModal">
                                <i class="fas fa-user-gear me-1"></i>
                                {{ $hasWorker ? 'Change Worker' : 'Assign Worker' }}
                            </button>
                        @endif

                        @if ($canReschedule)
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#rescheduleJobOrderModal">
                                <i class="fas fa-calendar-days me-1"></i>
                                {{ $hasSchedule ? 'Change Schedule' : 'Set Schedule' }}
                            </button>
                        @endif
                    </div>

                    @if ($canStart)
                        <form method="POST" action="{{ route('admin.job-orders.start', $jobOrder) }}" class="mt-2">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-play me-1"></i>
                                Start Actual Service
                            </button>
                        </form>
                    @endif
                @elseif (!$isFinal && $isInspection)
                    <div class="jo-inspector-note">
                        <i class="fas fa-circle-info"></i>
                        <span>
                            Final report:
                            <strong>{{ $inspectionReport ? ucwords(str_replace('_', ' ', $inspectionReport->status)) : 'Not created' }}</strong>
                        </span>
                    </div>
                @endif
            </section>

            @if ($canComplete)
                <section class="jo-details-card jo-complete-card">
                    <div class="jo-details-card-head">
                        <span class="jo-card-icon green"><i class="fas fa-circle-check"></i></span>
                        <div>
                            <h2>Complete Service</h2>
                            <p>Use only after the actual work is finished.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.job-orders.complete', $jobOrder) }}">
                        @csrf
                        @method('PATCH')

                        <label class="form-label">Completion Notes</label>
                        <textarea name="completion_notes" class="form-control" rows="4" placeholder="Describe the completed repair, installation, testing, or final service notes.">{{ old('completion_notes', $jobOrder->completion_notes) }}</textarea>

                        <button type="submit" class="btn btn-success w-100 mt-3">
                            <i class="fas fa-check me-1"></i>
                            Mark Service Completed
                        </button>
                    </form>
                </section>
            @endif
        </aside>
    </div>

    @if ($isService && $status === 'completed')
        <div class="jo-aftercare-grid">
            <section class="jo-details-card jo-aftercare-card">
                <div class="jo-details-card-head">
                    <span class="jo-card-icon blue"><i class="fas fa-shield-halved"></i></span>
                    <div>
                        <h2>Warranty</h2>
                        <p>30-day service coverage.</p>
                    </div>
                </div>

                <div class="jo-aftercare-stats">
                    <div>
                        <span>Warranty Until</span>
                        <strong>{{ $warrantyExpiresAt ? $warrantyExpiresAt->format('M d, Y') : 'Not available' }}</strong>
                    </div>
                    <div>
                        <span>Status</span>
                        <strong>{{ $isWithinWarranty ? 'Active' : 'Expired' }}</strong>
                    </div>
                    <div>
                        <span>Claims</span>
                        <strong>{{ $warrantyClaims->count() }}</strong>
                    </div>
                </div>

                @if ($latestWarrantyClaim)
                    <a href="{{ route('admin.warranty-claims.show', $latestWarrantyClaim) }}" class="btn btn-sm btn-outline-primary mt-3">
                        View Latest Claim
                    </a>
                @endif
            </section>

            <section class="jo-details-card jo-aftercare-card">
                <div class="jo-details-card-head">
                    <span class="jo-card-icon blue"><i class="fas fa-rotate-left"></i></span>
                    <div>
                        <h2>Backjobs</h2>
                        <p>Follow-up service linked to this completed job.</p>
                    </div>
                </div>

                @if ($backJobs->count())
                    <div class="jo-mini-list">
                        @foreach ($backJobs as $backJob)
                            <div class="jo-mini-record">
                                <div>
                                    <span>{{ $backJob->backjob_no }}</span>
                                    <strong>{{ ucwords(str_replace('_', ' ', $backJob->status)) }}</strong>
                                </div>
                                <a href="{{ route('admin.backjobs.show', $backJob) }}" class="btn btn-sm btn-outline-primary">Open</a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="jo-empty-inline">No backjobs recorded.</div>
                @endif
            </section>
        </div>
    @endif

    <div class="modal fade" id="rescheduleJobOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content jo-control-modal">
                <form method="POST" action="{{ route('admin.job-orders.reschedule', $jobOrder) }}">
                    @csrf
                    @method('PATCH')

                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">{{ $hasSchedule ? 'Change Schedule' : 'Set Service Schedule' }}</h5>
                            <p class="text-muted mb-0">Set the actual service date and time.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ $isInspection ? 'Inspection Date' : 'Service Date' }}</label>
                            <input type="date" name="scheduled_date" class="form-control" value="{{ old('scheduled_date', optional($jobOrder->scheduled_date)->format('Y-m-d')) }}" required>
                        </div>

                        <div>
                            <label class="form-label">{{ $isInspection ? 'Inspection Time' : 'Service Time' }}</label>
                            <input type="time" name="scheduled_time" class="form-control" value="{{ old('scheduled_time', $jobOrder->scheduled_time ? \Carbon\Carbon::parse($jobOrder->scheduled_time)->format('H:i') : '') }}">
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
                            <h5 class="modal-title">{{ $hasWorker ? 'Change Worker' : 'Assign Worker' }}</h5>
                            <p class="text-muted mb-0">Select the personnel responsible for this job order.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <label class="form-label">{{ $isInspection ? 'Assigned Inspector' : 'Assigned Worker' }}</label>

                        <select name="worker_id" class="form-select" required>
                            <option value="">{{ $isInspection ? 'Select inspector' : 'Select worker' }}</option>

                            @foreach (($availableWorkers ?? collect()) as $worker)
                                @php
                                    $workerLabel = $worker->name
                                        ?? trim(($worker->first_name ?? '') . ' ' . ($worker->last_name ?? ''))
                                        ?: $worker->email;
                                @endphp

                                <option value="{{ $worker->id }}" @selected((string) old('worker_id', $jobOrder->worker_id) === (string) $worker->id)>
                                    {{ $workerLabel }} — {{ ucfirst($worker->role) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-check me-1"></i>
                            Save Assignment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
