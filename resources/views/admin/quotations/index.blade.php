@extends('admin.layouts.app')

@section('title', 'Service Requests - WRPlumb')
@section('topbar_title', 'Service Requests')
@section('topbar_subtitle', 'Manage and process service requests using a guided workflow.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/quotations.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/quotations-archive.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/quotations-workflow.css') }}">
@endpush

@section('content')
@php
    $quotationItems = method_exists($quotations, 'getCollection') ? $quotations->getCollection() : collect($quotations);
    $totalCount = method_exists($quotations, 'total') ? $quotations->total() : $quotationItems->count();
    $displayedCount = $quotationItems->count();
    $pendingCount = $quotationItems->where('status', 'pending')->count();
    $assignedCount = $quotationItems->where('status', 'assigned')->count();
    $activeJobCount = $quotationItems->filter(function ($item) {
        return $item->jobOrder && in_array($item->jobOrder->status, ['scheduled', 'in_progress']);
    })->count();
    $activeFilterCount = collect(['search', 'status', 'service_category', 'preferred_date'])->filter(function ($key) {
        return request()->filled($key);
    })->count();
@endphp

<div class="workflow-page">
    <div class="workflow-filter-card">
        <form method="GET" action="{{ route('admin.quotations.index') }}" class="workflow-filter-form">
            <div class="workflow-filter-search">
                <label class="form-label">Search</label>
                <div class="workflow-search-control">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="text" name="search" class="form-control" placeholder="Search requests, clients, jobs..." value="{{ request('search') }}">
                </div>
            </div>

            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All statuses</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending Request</option>
                    <option value="assigned" @selected(request('status') === 'assigned')>Assigned Request</option>
                    <option value="in_progress" @selected(request('status') === 'in_progress')>In Progress Request</option>
                    <option value="completed" @selected(request('status') === 'completed')>Completed Request</option>
                    <option value="job_scheduled" @selected(request('status') === 'job_scheduled')>Job Scheduled</option>
                    <option value="job_in_progress" @selected(request('status') === 'job_in_progress')>Job In Progress</option>
                    <option value="job_completed" @selected(request('status') === 'job_completed')>Job Completed</option>
                    <option value="job_cancelled" @selected(request('status') === 'job_cancelled')>Job Cancelled</option>
                </select>
            </div>

            <div>
                <label class="form-label">Category</label>
                <select name="service_category" class="form-select">
                    <option value="">All categories</option>
                    <option value="plumbing" @selected(request('service_category') === 'plumbing')>Plumbing</option>
                    <option value="construction" @selected(request('service_category') === 'construction')>Construction</option>
                </select>
            </div>

            <div>
                <label class="form-label">Preferred Date</label>
                <input type="date" name="preferred_date" class="form-control" value="{{ request('preferred_date') }}">
            </div>

            <div class="workflow-filter-actions">
                <button class="btn btn-primary">
                    <i class="fas fa-filter me-1"></i> Apply
                </button>
                <a href="{{ route('admin.quotations.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="workflow-list-panel">
        <div class="workflow-list-head">
            <div>
                <h5><i class="fas fa-briefcase me-2 text-primary"></i>Service Requests</h5>
                <p>Showing {{ $displayedCount }} of {{ $totalCount }} request(s).</p>
            </div>

            <div class="workflow-list-actions">
                <span class="workflow-filter-count">{{ $activeFilterCount }} active filter{{ $activeFilterCount === 1 ? '' : 's' }}</span>
            </div>
        </div>

        @if ($quotations->count())
            <div class="workflow-request-grid">
                @foreach ($quotations as $quotation)
                    @php
                        $availableWorkers = $availableWorkersByQuotation[$quotation->id] ?? collect();
                        $hasAvailableWorkers = $availableWorkers->isNotEmpty();

                        $flow = $quotation->service_flow ?? 'inspection_required';
                        $visitPurpose = $quotation->visit_purpose ?? ($flow === 'direct_service' ? 'service' : 'inspection');

                        $clientName = $quotation->full_name ?? trim(($quotation->first_name ?? '') . ' ' . ($quotation->last_name ?? ''));
                        $assignmentLabel = $flow === 'direct_service' ? 'Personnel' : 'Inspector';
                        $scheduleLabel = $flow === 'direct_service' ? 'Service' : 'Inspection';
                        $scheduleActionLabel = $flow === 'direct_service' ? 'Confirm Service Schedule' : 'Confirm Inspection Schedule';
                        $scheduleConfirmedLabel = $flow === 'direct_service' ? 'Service schedule confirmed' : 'Inspection schedule confirmed';
                        $scheduleDateLabel = $flow === 'direct_service' ? 'Service Date' : 'Inspection Date';
                        $scheduleTimeLabel = $flow === 'direct_service' ? 'Service Time' : 'Inspection Time';
                        $scheduleContextText = $flow === 'direct_service'
                            ? 'The client’s preferred date and time will be used as the proposed service schedule.'
                            : 'The client’s preferred date and time will be used as the proposed inspection schedule.';

                        $hasAssigned = !empty($quotation->worker_id);
                        $hasScheduled = !empty($quotation->appointment_date)
                            && !empty($quotation->appointment_time)
                            && in_array($quotation->appointment_status, ['approved', 'rescheduled']);
                        $hasJobOrder = !empty($quotation->jobOrder);

                        $isTaskCompleted = ($quotation->jobOrder?->status === 'completed')
                            || ($quotation->status === 'completed');

                        $taskCompletedAt = $quotation->jobOrder?->completed_at
                            ?? $quotation->completed_at
                            ?? null;

                        $archiveStatus = $quotation->jobOrder?->status ?? $quotation->status;
                        $canArchive = in_array($archiveStatus, ['completed', 'cancelled'], true);

                        $recommendationLabel = $flow === 'direct_service' ? 'Direct Service' : 'Inspection Required';
                        $recommendationText = $flow === 'direct_service'
                            ? 'The preferred date may be used as the actual service date after personnel assignment.'
                            : 'This request needs a site inspection before service execution.';

                        $proposedScheduleDate = old('appointment_date', optional($quotation->appointment_date ?? $quotation->preferred_date)->format('Y-m-d'));
                        $rawScheduleTime = old('appointment_time', $quotation->appointment_time ?? $quotation->preferred_time ?? null);
                        try {
                            $scheduleTimeDisplay = $rawScheduleTime ? \Carbon\Carbon::parse($rawScheduleTime)->format('h:i A') : '';
                        } catch (\Exception $e) {
                            $scheduleTimeDisplay = $rawScheduleTime;
                        }

                        $displayStatus = $quotation->jobOrder
                            ? 'Job ' . ucfirst(str_replace('_', ' ', $quotation->jobOrder->status))
                            : ucfirst(str_replace('_', ' ', $quotation->status));

                        $statusTone = 'blue';
                        if (str_contains(strtolower($displayStatus), 'completed')) {
                            $statusTone = 'green';
                        } elseif (str_contains(strtolower($displayStatus), 'cancelled') || str_contains(strtolower($displayStatus), 'rejected')) {
                            $statusTone = 'red';
                        } elseif (str_contains(strtolower($displayStatus), 'pending')) {
                            $statusTone = 'orange';
                        }

                        if ($isTaskCompleted) {
                            $initialStep = 5;
                        } elseif (($quotation->client_action_status ?? null) === 'pending') {
                            $initialStep = 1;
                        } elseif (!$hasAssigned) {
                            $initialStep = 2;
                        } elseif (!$hasScheduled) {
                            $initialStep = 3;
                        } elseif (!$hasJobOrder) {
                            $initialStep = 4;
                        } else {
                            $initialStep = 5;
                        }
                    @endphp

                    <article class="workflow-request-card workflow-request-clickable"
                             role="button"
                             tabindex="0"
                             data-bs-toggle="modal"
                             data-bs-target="#requestWorkflowModal{{ $quotation->id }}">
                        <div class="workflow-request-main">
                            <div class="workflow-request-icon {{ $flow === 'direct_service' ? 'direct' : 'inspect' }}">
                                @if ($flow === 'direct_service')
                                    <i class="fas fa-screwdriver-wrench"></i>
                                @else
                                    <i class="fas fa-shower"></i>
                                @endif
                            </div>

                            <div class="workflow-request-copy">
                                <h5>{{ $clientName ?: 'Unnamed Client' }}</h5>
                                <span class="workflow-service-link">
                                    {{ $quotation->service_type }}
                                </span>
                                <p>{{ $quotation->email }} @if($quotation->phone) • {{ $quotation->phone }} @endif</p>

                                <div class="workflow-chip-row">
                                    <span class="workflow-chip service"><i class="fas fa-screwdriver-wrench"></i>{{ $quotation->service_type }}</span>
                                    @if ($flow === 'direct_service')
                                        <span class="workflow-chip direct"><i class="fas fa-bolt"></i>Direct Service</span>
                                    @else
                                        <span class="workflow-chip inspect"><i class="fas fa-search-location"></i>Inspection Required</span>
                                    @endif
                                    <span class="workflow-chip date"><i class="fas fa-calendar-day"></i>{{ optional($quotation->preferred_date)->format('Y-m-d') ?? 'No date' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="workflow-request-meta">
                            <div>
                                <span>Preferred Date</span>
                                <strong>{{ optional($quotation->preferred_date)->format('Y-m-d') ?? '—' }}</strong>
                            </div>
                            <div>
                                <span>Status</span>
                                <strong><span class="workflow-status {{ $statusTone }}">{{ strtoupper($displayStatus) }}</span></strong>
                            </div>
                            <div>
                                <span>{{ $assignmentLabel }}</span>
                                <strong>{{ $quotation->worker?->name ?? 'Not assigned' }}</strong>
                            </div>
                        </div>

                    </article>

                    <div class="modal fade workflow-modal"
                         id="requestWorkflowModal{{ $quotation->id }}"
                         tabindex="-1"
                         aria-labelledby="requestWorkflowModalLabel{{ $quotation->id }}"
                         aria-hidden="true"
                         data-initial-step="{{ $initialStep }}"
                         data-task-completed="{{ $isTaskCompleted ? '1' : '0' }}"
                         data-job-order-create-url="{{ route('admin.job-orders.create', $quotation) }}"
                         data-job-order-show-url="{{ $hasJobOrder ? route('admin.job-orders.show', $quotation->jobOrder) : '' }}">
                        <div class="modal-dialog modal-dialog-centered modal-xl">
                            <div class="modal-content">
                                <div class="workflow-modal-header">
                                    <div>
                                        <h5 class="modal-title" id="requestWorkflowModalLabel{{ $quotation->id }}">Service Request Workflow</h5>
                                        <p>{{ $clientName ?: 'Unnamed Client' }} <span>•</span> {{ $quotation->service_type }}</p>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div class="workflow-stepper" data-workflow-stepper>
                                    <button type="button" class="workflow-step" data-step-target="1">
                                        <span>1</span><strong>Review</strong>
                                    </button>
                                    <button type="button" class="workflow-step" data-step-target="2">
                                        <span>2</span><strong>Assign</strong>
                                    </button>
                                    <button type="button" class="workflow-step" data-step-target="3">
                                        <span>3</span><strong>Confirm</strong>
                                    </button>
                                    <button type="button" class="workflow-step" data-step-target="4">
                                        <span>4</span><strong>Job Order</strong>
                                    </button>
                                    <button type="button" class="workflow-step" data-step-target="5">
                                        <span>{{ $isTaskCompleted ? '✓' : '5' }}</span><strong>{{ $isTaskCompleted ? 'Completed' : 'Options' }}</strong>
                                    </button>
                                </div>

                                <div class="workflow-modal-body">
                                    <section class="workflow-step-panel" data-step-panel="1">
                                        <div class="workflow-section-title">
                                            <h4>Review Request</h4>
                                            <p>Confirm the service request details before proceeding to assignment.</p>
                                        </div>

                                        <div class="workflow-review-grid">
                                            <div class="workflow-info-card primary">
                                                <div class="workflow-info-icon"><i class="fas fa-user"></i></div>
                                                <div><span>Client</span><strong>{{ $clientName ?: 'Unnamed Client' }}</strong></div>
                                            </div>
                                            <div class="workflow-info-card">
                                                <div class="workflow-info-icon"><i class="fas fa-screwdriver-wrench"></i></div>
                                                <div><span>Service Type</span><strong>{{ $quotation->service_type }}</strong></div>
                                            </div>
                                            <div class="workflow-info-card">
                                                <div class="workflow-info-icon"><i class="fas fa-calendar-day"></i></div>
                                                <div><span>Preferred Date</span><strong>{{ optional($quotation->preferred_date)->format('Y-m-d') ?? 'Not set' }}</strong></div>
                                            </div>
                                            <div class="workflow-info-card">
                                                <div class="workflow-info-icon"><i class="fas fa-location-dot"></i></div>
                                                <div><span>Address</span><strong>{{ $quotation->address ?? '—' }}</strong></div>
                                            </div>
                                        </div>

                                        <div class="workflow-decision-card {{ $flow === 'direct_service' ? 'direct' : 'inspect' }}">
                                            <div class="workflow-decision-icon">
                                                @if ($flow === 'direct_service')
                                                    <i class="fas fa-bolt"></i>
                                                @else
                                                    <i class="fas fa-search-location"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <span>System Decision</span>
                                                <strong>{{ $recommendationLabel }}</strong>
                                                <p>{{ $recommendationText }}</p>
                                            </div>
                                        </div>

                                        <div class="workflow-notes-card">
                                            <span>Client Request Details</span>
                                            <p>{{ $quotation->details ?: 'No additional request details provided.' }}</p>
                                        </div>

                                        @if (($quotation->client_action_status ?? null) === 'pending')
                                            <div class="workflow-alert-card">
                                                <div class="workflow-alert-title"><i class="fas fa-user-clock"></i> Client has a pending request</div>
                                                <p>Review the client request before proceeding.</p>
                                                <div class="workflow-notes-card mb-3">
                                                    <span>Request</span>
                                                    <p>
                                                        <strong>{{ ucfirst($quotation->client_action_request) }}</strong><br>
                                                        @if ($quotation->client_requested_date)
                                                            Requested date: {{ optional($quotation->client_requested_date)->format('Y-m-d') }}<br>
                                                        @endif
                                                        @if ($quotation->client_requested_time)
                                                            Requested time: {{ date('h:i A', strtotime($quotation->client_requested_time)) }}<br>
                                                        @endif
                                                        Reason: {{ $quotation->client_request_reason }}
                                                    </p>
                                                </div>
                                                <form method="POST"
                                                      action="{{ route('admin.quotations.review-client-request', $quotation) }}"
                                                      class="js-workflow-ajax-form"
                                                      data-workflow-action="client-review"
                                                      data-success-message="Client request reviewed successfully.">
                                                    @csrf
                                                    <div class="mb-3">
                                                        <label class="form-label">Review Notes</label>
                                                        <textarea name="client_request_review_notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                                                    </div>
                                                    <div class="row g-2">
                                                        <div class="col-sm-6">
                                                            <button name="decision" value="declined" class="btn btn-outline-danger w-100">Decline</button>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <button name="decision" value="approved" class="btn btn-primary w-100">Approve</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        @endif
                                    </section>

                                    <section class="workflow-step-panel" data-step-panel="2">
                                        <div class="workflow-section-title">
                                            <h4>Assign {{ $assignmentLabel }}</h4>
                                            <p>Select the inspector or personnel responsible for this service request.</p>
                                        </div>

                                        @if (!$hasAssigned)
                                            <form method="POST"
                                                  action="{{ route('admin.quotations.assign-worker', $quotation) }}"
                                                  class="workflow-form-card js-workflow-ajax-form"
                                                  data-workflow-action="assign"
                                                  data-assignment-label="{{ $assignmentLabel }}"
                                                  data-success-message="{{ $assignmentLabel }} assigned successfully.">
                                                @csrf
                                                <div class="mb-3">
                                                    <label class="form-label">{{ $assignmentLabel }} <span class="text-danger">*</span></label>
                                                    <select name="worker_id" class="form-select" required @disabled(!$hasAvailableWorkers)>
                                                        <option value="">{{ $hasAvailableWorkers ? 'Select ' . strtolower($assignmentLabel) : 'No available ' . strtolower($assignmentLabel) }}</option>
                                                        @foreach ($availableWorkers as $worker)
                                                            <option value="{{ $worker->id }}" @selected($quotation->worker_id == $worker->id)>{{ $worker->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="workflow-helper-note">
                                                    @if ($quotation->preferred_date)
                                                        @if ($hasAvailableWorkers)
                                                            Available personnel are shown for {{ optional($quotation->preferred_date)->format('Y-m-d') }}.
                                                        @else
                                                            No personnel is marked available on {{ optional($quotation->preferred_date)->format('Y-m-d') }}.
                                                        @endif
                                                    @else
                                                        Preferred date is not set. Active personnel are shown.
                                                    @endif
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Notes <span class="text-muted">(Optional)</span></label>
                                                    <textarea name="admin_notes" class="form-control" rows="3" placeholder="Add any notes or instructions for the assigned personnel...">{{ old('admin_notes', $quotation->admin_notes) }}</textarea>
                                                </div>

                                                <button class="btn btn-primary w-100" @disabled(!$hasAvailableWorkers)>
                                                    <i class="fas fa-user-check me-1"></i> Save Assignment
                                                </button>
                                            </form>
                                        @else
                                            <div class="workflow-complete-card">
                                                <div class="workflow-complete-icon"><i class="fas fa-check"></i></div>
                                                <div>
                                                    <strong>{{ $assignmentLabel }} already assigned</strong>
                                                    <p>{{ $quotation->worker?->name ?? 'Assigned' }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="workflow-summary-strip">
                                            <div>
                                                <div class="workflow-strip-icon blue"><i class="fas fa-screwdriver-wrench"></i></div>
                                                <span>Service Type</span>
                                                <strong>{{ $quotation->service_type }}</strong>
                                            </div>
                                            <div>
                                                <div class="workflow-strip-icon green"><i class="fas fa-bolt"></i></div>
                                                <span>System Decision</span>
                                                <strong>{{ $recommendationLabel }}</strong>
                                            </div>
                                        </div>
                                    </section>

                                    <section class="workflow-step-panel" data-step-panel="3">
                                        <div class="workflow-section-title">
                                            <h4>{{ $scheduleActionLabel }}</h4>
                                            <p>{{ $scheduleContextText }}</p>
                                        </div>

                                        @if (!$hasScheduled)
                                            <form method="POST"
                                                  action="{{ route('admin.quotations.update-appointment', $quotation) }}"
                                                  class="workflow-form-card workflow-confirm-schedule-card js-workflow-ajax-form"
                                                  data-workflow-action="schedule"
                                                  data-schedule-confirmed-label="{{ $scheduleConfirmedLabel }}"
                                                  data-schedule-label="{{ $scheduleLabel }}"
                                                  data-success-message="{{ $scheduleActionLabel }} saved successfully.">
                                                @csrf
                                                <input type="hidden" name="appointment_status" value="approved">

                                                <div class="workflow-proposed-schedule">
                                                    <div class="workflow-proposed-icon">
                                                        <i class="fas fa-calendar-check"></i>
                                                    </div>
                                                    <div>
                                                        <span>Proposed {{ $scheduleLabel }} Schedule</span>
                                                        <strong>
                                                            {{ $proposedScheduleDate ?: 'No preferred date' }}
                                                            @if ($scheduleTimeDisplay)
                                                                • {{ $scheduleTimeDisplay }}
                                                            @endif
                                                        </strong>
                                                        <p>
                                                            @if ($flow === 'direct_service')
                                                                Direct service uses the client’s preferred date/time as the service schedule after personnel assignment.
                                                            @else
                                                                Inspection-required requests use the client’s preferred date/time as the inspection schedule.
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">{{ $scheduleDateLabel }}</label>
                                                        <input type="date" name="appointment_date" class="form-control" value="{{ $proposedScheduleDate }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">{{ $scheduleTimeLabel }}</label>
<input
    type="time"
    name="appointment_time"
    class="form-control"
    value="{{ $rawScheduleTime ? \Carbon\Carbon::parse($rawScheduleTime)->format('H:i') : '' }}"
    required
>                                                    </div>
                                                </div>

                                                @error('appointment_date')
                                                    <small class="text-danger d-block mt-2">{{ $message }}</small>
                                                @enderror
                                                @error('appointment_time')
                                                    <small class="text-danger d-block mt-2">{{ $message }}</small>
                                                @enderror

                                                <button class="btn btn-primary w-100 mt-3">
                                                    <i class="fas fa-calendar-check me-1"></i> {{ $scheduleActionLabel }}
                                                </button>
                                            </form>
                                        @else
                                            <div class="workflow-complete-card workflow-schedule-confirmed-card">
                                                <div class="workflow-complete-icon"><i class="fas fa-check"></i></div>
                                                <div>
                                                    <strong>{{ $scheduleConfirmedLabel }}</strong>
                                                    <p>{{ optional($quotation->appointment_date)->format('M d, Y') }} • {{ date('h:i A', strtotime($quotation->appointment_time)) }}</p>
                                                </div>
                                            </div>
                                        @endif
                                    </section>

                                    <section class="workflow-step-panel" data-step-panel="4">
                                        <div class="workflow-section-title">
                                            <h4>Job Order</h4>
                                            <p>Create or review the job order connected to this request.</p>
                                        </div>

                                        @if (!$hasAssigned || !$hasScheduled)
                                            <div class="workflow-empty-warning">
                                                <i class="fas fa-triangle-exclamation"></i>
                                                <div>
                                                    <strong>Assignment and confirmed schedule required</strong>
                                                    <p>Complete the assignment and confirm the service/inspection schedule before creating a job order.</p>
                                                </div>
                                            </div>
                                        @elseif (!$hasJobOrder)
                                            <div class="workflow-job-card ready">
                                                <i class="fas fa-circle-check"></i>
                                                <div>
                                                    <strong>Ready for job order</strong>
                                                    <span>Assignment and schedule are already set.</span>
                                                </div>
                                            </div>
                                            <a href="{{ route('admin.job-orders.create', $quotation) }}" class="btn btn-success w-100">
                                                <i class="fas fa-plus-circle me-1"></i> Create Job Order
                                            </a>
                                        @else
                                            <div class="workflow-job-card done">
                                                <i class="fas fa-clipboard-check"></i>
                                                <div>
                                                    <strong>{{ $quotation->jobOrder->job_order_no }}</strong>
                                                    <span>Status: {{ ucfirst(str_replace('_', ' ', $quotation->jobOrder->status)) }}</span>
                                                </div>
                                            </div>
                                            <a href="{{ route('admin.job-orders.show', $quotation->jobOrder) }}" class="btn btn-outline-primary w-100">
                                                <i class="fas fa-eye me-1"></i> View Job Order
                                            </a>
                                        @endif
                                    </section>

                                    <section class="workflow-step-panel {{ $isTaskCompleted ? 'workflow-completed-panel' : '' }}" data-step-panel="5">
                                        @if ($isTaskCompleted)
                                            <div class="workflow-completion-hero">
                                                <div class="workflow-completion-hero-icon">
                                                    <i class="fas fa-check"></i>
                                                </div>

                                                <div class="workflow-completion-hero-copy">
                                                    <div class="workflow-completion-title-row">
                                                        <h4>Task Successfully Completed</h4>
                                                        <span class="workflow-completion-pill">
                                                            <i class="fas fa-circle-check"></i>
                                                            Completed
                                                        </span>
                                                    </div>

                                                    <div class="workflow-completion-meta">
                                                        @if ($quotation->jobOrder?->job_order_no)
                                                            <span>
                                                                <i class="fas fa-clipboard-list"></i>
                                                                Job Order
                                                                <strong>{{ $quotation->jobOrder->job_order_no }}</strong>
                                                            </span>
                                                        @endif

                                                        @if ($taskCompletedAt)
                                                            <span>
                                                                <i class="fas fa-calendar-check"></i>
                                                                Completed on
                                                                <strong>{{ optional($taskCompletedAt)->format('M d, Y h:i A') }}</strong>
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="workflow-completion-summary-grid">
                                                <div class="workflow-completion-stat">
                                                    <div class="workflow-completion-stat-icon blue">
                                                        <i class="fas fa-screwdriver-wrench"></i>
                                                    </div>
                                                    <div>
                                                        <span>Service Type</span>
                                                        <strong>{{ $flow === 'direct_service' ? 'Direct Service' : 'Inspection Required' }}</strong>
                                                    </div>
                                                </div>

                                                <div class="workflow-completion-stat">
                                                    <div class="workflow-completion-stat-icon purple">
                                                        <i class="fas fa-user-group"></i>
                                                    </div>
                                                    <div>
                                                        <span>Assigned To</span>
                                                        <strong>{{ $quotation->worker?->name ?? 'Assigned Personnel' }}</strong>
                                                    </div>
                                                </div>

                                                <div class="workflow-completion-stat">
                                                    <div class="workflow-completion-stat-icon green">
                                                        <i class="fas fa-circle-check"></i>
                                                    </div>
                                                    <div>
                                                        <span>Status</span>
                                                        <strong>Completed</strong>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="workflow-completion-details">
                                                <h5>Completion Details</h5>

                                                <div class="workflow-completion-timeline">
                                                    <div class="workflow-completion-timeline-item">
                                                        <span class="workflow-completion-timeline-dot">
                                                            <i class="fas fa-check"></i>
                                                        </span>
                                                        <div class="workflow-completion-timeline-copy">
                                                            <strong>Request submitted</strong>
                                                            <span>{{ optional($quotation->created_at)->format('M d, Y h:i A') ?? 'Completed' }}</span>
                                                        </div>
                                                    </div>

                                                    @if ($quotation->assigned_at)
                                                        <div class="workflow-completion-timeline-item">
                                                            <span class="workflow-completion-timeline-dot">
                                                                <i class="fas fa-check"></i>
                                                            </span>
                                                            <div class="workflow-completion-timeline-copy">
                                                                <strong>Request assigned to {{ $quotation->worker?->name ?? $assignmentLabel }}</strong>
                                                                <span>{{ optional($quotation->assigned_at)->format('M d, Y h:i A') }}</span>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    @if ($quotation->approved_at || $quotation->rescheduled_at)
                                                        <div class="workflow-completion-timeline-item">
                                                            <span class="workflow-completion-timeline-dot">
                                                                <i class="fas fa-check"></i>
                                                            </span>
                                                            <div class="workflow-completion-timeline-copy">
                                                                <strong>Schedule confirmed</strong>
                                                                <span>{{ optional($quotation->rescheduled_at ?? $quotation->approved_at)->format('M d, Y h:i A') }}</span>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    @if ($quotation->jobOrder?->created_at)
                                                        <div class="workflow-completion-timeline-item">
                                                            <span class="workflow-completion-timeline-dot">
                                                                <i class="fas fa-check"></i>
                                                            </span>
                                                            <div class="workflow-completion-timeline-copy">
                                                                <strong>Job order issued</strong>
                                                                <span>{{ optional($quotation->jobOrder->created_at)->format('M d, Y h:i A') }}</span>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    <div class="workflow-completion-timeline-item final">
                                                        <span class="workflow-completion-timeline-dot">
                                                            <i class="fas fa-check"></i>
                                                        </span>
                                                        <div class="workflow-completion-timeline-copy">
                                                            <strong>Work finished and task completed</strong>
                                                            <span>{{ $taskCompletedAt ? optional($taskCompletedAt)->format('M d, Y h:i A') : 'Completed' }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            @php
                                                $alreadySentToHr = !empty($quotation->ready_for_quotation_at);
                                                $hasExistingQuotation = !empty($quotation->quotation);

                                                $inspectionCompleted = $flow !== 'inspection_required'
                                                    || (
                                                        $quotation->jobOrder
                                                        && $quotation->jobOrder->status === 'completed'
                                                    );

                                                $canSendToHr = !$alreadySentToHr
                                                    && !$hasExistingQuotation
                                                    && $hasAssigned
                                                    && $hasScheduled
                                                    && $inspectionCompleted;
                                            @endphp

                                            <div
                                                class="quotation-handoff-card {{ $alreadySentToHr ? 'is-sent' : '' }}"
                                                data-quotation-handoff-card
                                            >
                                                <div class="quotation-handoff-icon">
                                                    @if ($alreadySentToHr)
                                                        <i class="fas fa-circle-check"></i>
                                                    @else
                                                        <i class="fas fa-file-circle-check"></i>
                                                    @endif
                                                </div>

                                                <div class="quotation-handoff-content">
                                                    @if ($alreadySentToHr)
                                                        <span class="quotation-handoff-eyebrow">
                                                            Ready for Quotation
                                                        </span>

                                                        <h6>Sent to HR</h6>

                                                        <p>
                                                            This service request was forwarded to HR on
                                                            <strong>
                                                                {{ optional($quotation->ready_for_quotation_at)
                                                                    ->format('M d, Y h:i A') }}
                                                            </strong>.
                                                        </p>

                                                        <div class="quotation-handoff-success">
                                                            <i class="fas fa-check-circle"></i>
                                                            HR has been notified
                                                        </div>
                                                    @elseif ($hasExistingQuotation)
                                                        <span class="quotation-handoff-eyebrow">
                                                            Quotation Prepared
                                                        </span>

                                                        <h6>Quotation already created</h6>

                                                        <p>
                                                            HR has already created a quotation for this service request.
                                                        </p>
                                                    @else
                                                        <span class="quotation-handoff-eyebrow">
                                                            Quotation Handoff
                                                        </span>

                                                        <h6>Send request to HR</h6>

                                                        <p>
                                                            The job order is complete. Forward this request to HR
                                                            so the official quotation can be prepared.
                                                        </p>

                                                        <form
                                                            method="POST"
                                                            action="{{ route('admin.quotations.send-to-hr', $quotation) }}"
                                                            class="js-workflow-ajax-form"
                                                            data-workflow-action="send-to-hr"
                                                            data-success-message="Request sent to HR successfully."
                                                        >
                                                            @csrf

                                                            <div class="mb-3">
                                                                <label class="form-label">
                                                                    Handoff Notes
                                                                    <span class="text-muted">(Optional)</span>
                                                                </label>

                                                                <textarea
                                                                    name="quotation_handoff_notes"
                                                                    class="form-control"
                                                                    rows="3"
                                                                    placeholder="Add quotation instructions, findings, or important project details..."
                                                                >{{ old(
                                                                    'quotation_handoff_notes',
                                                                    $quotation->quotation_handoff_notes
                                                                ) }}</textarea>
                                                            </div>

                                                            <button
                                                                type="submit"
                                                                class="btn btn-primary w-100"
                                                                @disabled(!$canSendToHr)
                                                            >
                                                                <i class="fas fa-paper-plane me-1"></i>
                                                                Send to HR for Quotation
                                                            </button>

                                                            <small class="quotation-handoff-helper">
                                                                This will mark the request as Ready for Quotation
                                                                and notify all active HR accounts.
                                                            </small>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <div class="workflow-section-title">
                                                <h4>Service Flow Options</h4>
                                                <p>Adjust the service flow when needed. Archive actions are handled separately in the archived requests area.</p>
                                            </div>

                                        

                                        <div class="workflow-decision-card {{ $flow === 'direct_service' ? 'direct' : 'inspect' }}">
                                            <div class="workflow-decision-icon">
                                                @if ($flow === 'direct_service')
                                                    <i class="fas fa-bolt"></i>
                                                @else
                                                    <i class="fas fa-search-location"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <span>Current System Decision</span>
                                                <strong>{{ $recommendationLabel }}</strong>
                                                <p>{{ $recommendationText }}</p>
                                            </div>
                                        </div>

                                        @php
    $alreadySentToHr = !empty($quotation->ready_for_quotation_at);
    $hasExistingQuotation = !empty($quotation->quotation);

    $inspectionCompleted = $flow !== 'inspection_required'
        || (
            $quotation->jobOrder
            && $quotation->jobOrder->status === 'completed'
        );

    $canSendToHr = !$alreadySentToHr
        && !$hasExistingQuotation
        && $hasAssigned
        && $hasScheduled
        && $inspectionCompleted;
@endphp

<div
    class="quotation-handoff-card
        {{ $alreadySentToHr ? 'is-sent' : '' }}"
    data-quotation-handoff-card
>
    <div class="quotation-handoff-icon">
        @if ($alreadySentToHr)
            <i class="fas fa-circle-check"></i>
        @else
            <i class="fas fa-file-circle-check"></i>
        @endif
    </div>

    <div class="quotation-handoff-content">
        @if ($alreadySentToHr)
            <span class="quotation-handoff-eyebrow">
                Ready for Quotation
            </span>

            <h6>Sent to HR</h6>

            <p>
                This service request was forwarded to HR on
                <strong>
                    {{ optional($quotation->ready_for_quotation_at)
                        ->format('M d, Y h:i A') }}
                </strong>.
            </p>

            <div class="quotation-handoff-success">
                <i class="fas fa-check-circle"></i>
                HR has been notified
            </div>
        @elseif ($hasExistingQuotation)
            <span class="quotation-handoff-eyebrow">
                Quotation Prepared
            </span>

            <h6>Quotation already created</h6>

            <p>
                HR has already created a quotation for this service request.
            </p>
        @else
            <span class="quotation-handoff-eyebrow">
                Quotation Handoff
            </span>

            <h6>Send request to HR</h6>

            <p>
                Mark this request as ready for quotation and notify the HR
                team to prepare the official quotation.
            </p>

            @if (!$hasAssigned)
                <div class="quotation-handoff-warning">
                    <i class="fas fa-triangle-exclamation"></i>
                    Assign an inspector or personnel first.
                </div>
            @elseif (!$hasScheduled)
                <div class="quotation-handoff-warning">
                    <i class="fas fa-triangle-exclamation"></i>
                    Confirm the inspection or service schedule first.
                </div>
            @elseif (
                $flow === 'inspection_required'
                && !$hasJobOrder
            )
                <div class="quotation-handoff-warning">
                    <i class="fas fa-triangle-exclamation"></i>
                    Create the inspection job order first.
                </div>
            @elseif (
                $flow === 'inspection_required'
                && $quotation->jobOrder?->status !== 'completed'
            )
                <div class="quotation-handoff-warning">
                    <i class="fas fa-triangle-exclamation"></i>
                    Complete the inspection job order first.
                </div>
            @endif

            <form
                method="POST"
                action="{{ route(
                    'admin.quotations.send-to-hr',
                    $quotation
                ) }}"
                class="js-workflow-ajax-form"
                data-workflow-action="send-to-hr"
                data-success-message="Request sent to HR successfully."
            >
                @csrf

                <div class="mb-3">
                    <label class="form-label">
                        Handoff Notes
                        <span class="text-muted">(Optional)</span>
                    </label>

                    <textarea
                        name="quotation_handoff_notes"
                        class="form-control"
                        rows="3"
                        placeholder="Add quotation instructions, findings, or important project details..."
                    >{{ old(
                        'quotation_handoff_notes',
                        $quotation->quotation_handoff_notes
                    ) }}</textarea>
                </div>

                <button
                    type="submit"
                    class="btn btn-primary w-100"
                    @disabled(!$canSendToHr)
                >
                    <i class="fas fa-paper-plane me-1"></i>
                    Send to HR for Quotation
                </button>

                <small class="quotation-handoff-helper">
                    This will mark the request as Ready for Quotation
                    and notify all active HR accounts.
                </small>
            </form>
        @endif
    </div>
</div>

                                        <div class="workflow-form-card">
                                            <h6 class="workflow-mini-heading">Change System Decision</h6>
                                            <form method="POST"
                                                  action="{{ route('admin.quotations.update-flow', $quotation) }}"
                                                  class="js-workflow-ajax-form"
                                                  data-workflow-action="update-flow"
                                                  data-success-message="System decision updated successfully.">
                                                @csrf
                                                @method('PATCH')

                                                <div class="mb-3">
                                                    <label class="form-label">Service Flow</label>
                                                    <select name="service_flow" class="form-select" required>
                                                        <option value="direct_service" @selected($flow === 'direct_service')>Direct Service</option>
                                                        <option value="inspection_required" @selected($flow === 'inspection_required')>Inspection Required</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Reason</label>
                                                    <textarea name="flow_override_reason" class="form-control" rows="3" placeholder="Optional admin note">{{ old('flow_override_reason', $quotation->flow_override_reason) }}</textarea>
                                                </div>

                                                <button class="btn btn-outline-primary w-100">
                                                    <i class="fas fa-save me-1"></i> Save Changes
                                                </button>
                                            </form>
                                        </div>
                                        @endif
                                    </section>
                                </div>

                                <div class="workflow-modal-footer">
                                    @if ($isTaskCompleted)
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                        <div class="workflow-nav-actions">
                                            @if ($quotation->jobOrder)
                                                <a href="{{ route('admin.job-orders.show', $quotation->jobOrder) }}" class="btn btn-primary">
                                                    View Full Job Order
                                                    <i class="fas fa-chevron-right ms-1"></i>
                                                </a>
                                            @endif
                                        </div>
                                    @else
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <div class="workflow-nav-actions">
                                            <button type="button" class="btn btn-outline-secondary" data-workflow-back>
                                                Back
                                            </button>
                                            <button type="button" class="btn btn-primary" data-workflow-next>
                                            Next <i class="fas fa-chevron-right ms-1"></i>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="workflow-pagination mt-3">
                {{ $quotations->links() }}
            </div>
        @else
            <div class="workflow-empty-state">
                <div><i class="fas fa-file-circle-xmark"></i></div>
                <strong>No quotation requests found</strong>
                <p>Incoming service requests will appear here.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function showWorkflowToast(message, type = 'success') {
        let toastStack = document.getElementById('wrWorkflowToastStack');

        if (!toastStack) {
            toastStack = document.createElement('div');
            toastStack.id = 'wrWorkflowToastStack';
            toastStack.className = 'wr-workflow-toast-stack';
            document.body.appendChild(toastStack);
        }

        const toast = document.createElement('div');
        toast.className = type === 'success'
            ? 'wr-workflow-toast wr-workflow-toast-success'
            : 'wr-workflow-toast wr-workflow-toast-error';

        toast.innerHTML = `
            <div class="wr-workflow-toast-icon">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            </div>
            <div class="wr-workflow-toast-body">
                <strong>${type === 'success' ? 'Success' : 'Action Needed'}</strong>
                <span>${message}</span>
            </div>
            <button type="button" class="wr-workflow-toast-close" aria-label="Close notification">
                <i class="fas fa-times"></i>
            </button>
        `;

        toast.querySelector('.wr-workflow-toast-close').addEventListener('click', function () {
            toast.remove();
        });

        toastStack.appendChild(toast);

        setTimeout(function () {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-8px) translateX(12px)';
            toast.style.transition = 'all 0.25s ease';

            setTimeout(function () {
                toast.remove();
            }, 260);
        }, 3500);
    }

    document.querySelectorAll('.workflow-modal').forEach(function (modal) {
        let currentStep = Number(modal.dataset.initialStep || 1);
        const maxStep = 5;
        const taskCompleted = modal.dataset.taskCompleted === '1';

        modal.classList.toggle('is-task-completed', taskCompleted);

        const stepButtons = modal.querySelectorAll('[data-step-target]');
        const panels = modal.querySelectorAll('[data-step-panel]');
        const backButton = modal.querySelector('[data-workflow-back]');
        const nextButton = modal.querySelector('[data-workflow-next]');

        function showStep(step) {
            currentStep = Math.max(1, Math.min(maxStep, Number(step)));

            panels.forEach(function (panel) {
                panel.classList.toggle('active', Number(panel.dataset.stepPanel) === currentStep);
            });

            stepButtons.forEach(function (button) {
                const target = Number(button.dataset.stepTarget);
                button.classList.toggle('active', target === currentStep);
                button.classList.toggle('completed', taskCompleted || target < currentStep);
            });

            if (backButton) {
                backButton.disabled = currentStep === 1;
            }

            if (nextButton) {
                if (currentStep === maxStep) {
                    nextButton.innerHTML = taskCompleted
                        ? '<i class="fas fa-check-circle me-1"></i> Done'
                        : 'Done';
                } else {
                    nextButton.innerHTML = 'Next <i class="fas fa-chevron-right ms-1"></i>';
                }
            }
        }

        stepButtons.forEach(function (button) {
            button.setAttribute('aria-disabled', 'true');
            button.setAttribute('tabindex', '-1');
            button.style.pointerEvents = 'none';
            button.style.cursor = 'default';
        });

        if (backButton) {
            backButton.addEventListener('click', function () {
                showStep(currentStep - 1);
            });
        }

        if (nextButton) {
            nextButton.addEventListener('click', function () {
                if (currentStep === maxStep) {
                    const instance = bootstrap.Modal.getInstance(modal);
                    if (instance) instance.hide();
                    return;
                }

                showStep(currentStep + 1);
            });
        }

        modal.addEventListener('shown.bs.modal', function () {
            showStep(modal.dataset.initialStep || 1);
        });

        modal.addEventListener('workflow:show-step', function (event) {
            showStep(event.detail?.step || currentStep);
        });

        showStep(currentStep);
    });

    function escapeWorkflowHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function formatWorkflowSchedule(dateValue, timeValue) {
        const dateText = dateValue || 'Confirmed date';
        const timeText = timeValue || '';
        return timeText ? `${dateText} • ${timeText}` : dateText;
    }

    function updateWorkflowDomAfterSave(form, data) {
        const modal = form.closest('.workflow-modal');
        if (!modal) return;

        const action = form.dataset.workflowAction || '';

        if (action === 'assign') {
            const select = form.querySelector('select[name="worker_id"]');
            const workerName = data.worker_name || select?.selectedOptions?.[0]?.textContent?.trim() || 'Assigned';
            const label = form.dataset.assignmentLabel || 'Personnel';

            form.outerHTML = `
                <div class="workflow-complete-card workflow-ajax-updated">
                    <div class="workflow-complete-icon"><i class="fas fa-check"></i></div>
                    <div>
                        <strong>${escapeWorkflowHtml(label)} already assigned</strong>
                        <p>${escapeWorkflowHtml(workerName)}</p>
                    </div>
                </div>
            `;
        }

        if (action === 'schedule') {
            const label = form.dataset.scheduleConfirmedLabel || 'Schedule confirmed';
            const appointmentDate = data.appointment_date || form.querySelector('input[name="appointment_date"]')?.value || '';
            const appointmentTime = data.appointment_time_display || data.appointment_time || form.querySelector('input[name="appointment_time"]')?.value || '';
            const scheduleText = formatWorkflowSchedule(appointmentDate, appointmentTime);

            form.outerHTML = `
                <div class="workflow-complete-card workflow-schedule-confirmed-card workflow-ajax-updated">
                    <div class="workflow-complete-icon"><i class="fas fa-check"></i></div>
                    <div>
                        <strong>${escapeWorkflowHtml(label)}</strong>
                        <p>${escapeWorkflowHtml(scheduleText)}</p>
                    </div>
                </div>
            `;

            const jobPanel = modal.querySelector('[data-step-panel="4"]');
            const createUrl = modal.dataset.jobOrderCreateUrl || '#';

            if (jobPanel) {
                jobPanel.innerHTML = `
                    <div class="workflow-section-title">
                        <h4>Job Order</h4>
                        <p>Create or review the job order connected to this request.</p>
                    </div>
                    <div class="workflow-job-card ready workflow-ajax-updated">
                        <i class="fas fa-circle-check"></i>
                        <div>
                            <strong>Ready for job order</strong>
                            <span>Assignment and schedule are already confirmed.</span>
                        </div>
                    </div>
                    <a href="${escapeWorkflowHtml(createUrl)}" class="btn btn-success w-100">
                        <i class="fas fa-plus-circle me-1"></i> Create Job Order
                    </a>
                `;
            }
        }

        if (action === 'send-to-hr') {
            const card = form.closest('[data-quotation-handoff-card]');

            if (card) {
                card.classList.add('is-sent');

                card.innerHTML = `
                    <div class="quotation-handoff-icon">
                        <i class="fas fa-circle-check"></i>
                    </div>

                    <div class="quotation-handoff-content">
                        <span class="quotation-handoff-eyebrow">
                            Ready for Quotation
                        </span>

                        <h6>Sent to HR</h6>

                        <p>
                            This service request was forwarded to HR on
                            <strong>
                                ${escapeWorkflowHtml(
                                    data.ready_for_quotation_at || 'Just now'
                                )}
                            </strong>.
                        </p>

                        <div class="quotation-handoff-success">
                            <i class="fas fa-check-circle"></i>
                            HR has been notified
                        </div>
                    </div>
                `;
            }
        }

        if (data.next_step) {
            modal.dispatchEvent(new CustomEvent('workflow:show-step', {
                detail: { step: Number(data.next_step) }
            }));
        }
    }

    document.querySelectorAll('.js-workflow-ajax-form').forEach(function (form) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            const submitButton = form.querySelector('button[type="submit"]');
            const originalButtonHtml = submitButton ? submitButton.innerHTML : '';

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json().catch(function () {
                    return {};
                });

                if (!response.ok) {
                    const message = data.message || 'Please check the form and try again.';
                    showWorkflowToast(message, 'error');

                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalButtonHtml;
                    }

                    return;
                }

                showWorkflowToast(data.message || form.dataset.successMessage || 'Changes saved successfully.', 'success');
                updateWorkflowDomAfterSave(form, data);

                if (submitButton && document.body.contains(submitButton)) {
                    submitButton.innerHTML = '<i class="fas fa-check me-1"></i> Saved';

                    setTimeout(function () {
                        if (document.body.contains(submitButton)) {
                            submitButton.disabled = false;
                            submitButton.innerHTML = originalButtonHtml;
                        }
                    }, 1400);
                }
            } catch (error) {
                showWorkflowToast('Something went wrong. Please try again.', 'error');

                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonHtml;
                }
            }
        });
    });


    document.querySelectorAll('.workflow-request-clickable').forEach(function (card) {
        card.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                card.click();
            }
        });
    });
});
</script>
@endpush