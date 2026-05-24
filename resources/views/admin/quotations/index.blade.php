@extends('admin.layouts.app')

@section('title', 'View Quotations - WRPlumb')
@section('topbar_title', 'View Quotations')
@section('topbar_subtitle', 'Review service requests and take the next required action.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/quotations.css') }}?v=20260522modalfix">
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

<div class="quotations-page">
    <div class="q-stats-grid">
        <div class="q-stat-card">
            <div class="q-stat-icon blue"><i class="fas fa-file-signature"></i></div>
            <div>
                <div class="q-stat-label">Total Requests</div>
                <div class="q-stat-value">{{ $totalCount }}</div>
            </div>
        </div>

        <div class="q-stat-card">
            <div class="q-stat-icon orange"><i class="fas fa-clock"></i></div>
            <div>
                <div class="q-stat-label">Pending</div>
                <div class="q-stat-value">{{ $pendingCount }}</div>
            </div>
        </div>

        <div class="q-stat-card">
            <div class="q-stat-icon green"><i class="fas fa-user-check"></i></div>
            <div>
                <div class="q-stat-label">Assigned</div>
                <div class="q-stat-value">{{ $assignedCount }}</div>
            </div>
        </div>

        <div class="q-stat-card">
            <div class="q-stat-icon gray"><i class="fas fa-clipboard-check"></i></div>
            <div>
                <div class="q-stat-label">Active Jobs</div>
                <div class="q-stat-value">{{ $activeJobCount }}</div>
            </div>
        </div>
    </div>

    <details class="q-filter-panel" {{ $activeFilterCount ? 'open' : '' }}>
        <summary>
            <span><i class="fas fa-filter me-2"></i>Filter Requests</span>
            <span class="q-filter-count">{{ $activeFilterCount }} active</span>
        </summary>

        <div class="q-filter-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label">Search</label>
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Client, email, phone, service, address..."
                        value="{{ request('search') }}"
                    >
                </div>

                <div class="col-lg-2 col-md-6">
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

                <div class="col-lg-2 col-md-6">
                    <label class="form-label">Category</label>
                    <select name="service_category" class="form-select">
                        <option value="">All categories</option>
                        <option value="plumbing" @selected(request('service_category') === 'plumbing')>Plumbing</option>
                        <option value="construction" @selected(request('service_category') === 'construction')>Construction</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-6">
                    <label class="form-label">Preferred Date</label>
                    <input type="date" name="preferred_date" class="form-control" value="{{ request('preferred_date') }}">
                </div>

                <div class="col-lg-2 col-md-12 d-flex gap-2">
                    <button class="btn btn-primary flex-fill">
                        <i class="fas fa-magnifying-glass me-1"></i>Apply
                    </button>
                    <a href="{{ route('admin.quotations.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </details>

    <div class="q-list-panel">
        <div class="q-list-head">
            <div>
                <h5 class="q-list-title"><i class="fas fa-file-signature me-2 text-primary"></i>Service Requests</h5>
                <div class="q-list-subtitle">Showing {{ $displayedCount }} request(s). Status filters distinguish request status from job order status.</div>
            </div>
        </div>

        <div class="q-list-body">
            @if ($quotations->count())
                <div class="quotation-list">
                    @foreach ($quotations as $quotation)
                        @php
                            $availableWorkers = $availableWorkersByQuotation[$quotation->id] ?? collect();
                            $hasAvailableWorkers = $availableWorkers->isNotEmpty();

                            $flow = $quotation->service_flow ?? 'inspection_required';
                            $visitPurpose = $quotation->visit_purpose ?? ($flow === 'direct_service' ? 'service' : 'inspection');

                            $clientName = $quotation->full_name ?? trim(($quotation->first_name ?? '') . ' ' . ($quotation->last_name ?? ''));
                            $assignmentLabel = $flow === 'direct_service' ? 'Personnel' : 'Inspector';
                            $scheduleLabel = $flow === 'direct_service' ? 'Service' : 'Inspection';

                            $hasAssigned = !empty($quotation->worker_id);
                            $hasScheduled = !empty($quotation->appointment_date)
                                && !empty($quotation->appointment_time)
                                && in_array($quotation->appointment_status, ['approved', 'rescheduled']);
                            $hasJobOrder = !empty($quotation->jobOrder);

                            $recommendationLabel = $flow === 'direct_service' ? 'Direct Service' : 'Inspection Required';
                            $recommendationText = $flow === 'direct_service'
                                ? 'The preferred date may be used as the actual service date after personnel assignment.'
                                : 'This request needs a site inspection before service execution.';

                            $rawScheduleTime = old('appointment_time', $quotation->appointment_time ?? $quotation->preferred_time ?? null);
                            try {
                                $scheduleTimeDisplay = $rawScheduleTime ? \Carbon\Carbon::parse($rawScheduleTime)->format('h:i A') : '';
                            } catch (\Exception $e) {
                                $scheduleTimeDisplay = $rawScheduleTime;
                            }
                        @endphp

                        <details class="quotation-item" @if($loop->first) open @endif>
                            <summary class="quotation-summary">
                                <div class="summary-main">
                                    <div class="summary-name">{{ $clientName ?: 'Unnamed Client' }}</div>
                                    <div class="summary-sub">
                                        {{ $quotation->service_type }} • {{ $quotation->email }} • {{ $quotation->phone }}
                                    </div>

                                    <div class="summary-chip-row">
                                        <span class="service-chip">
                                            <i class="fas fa-screwdriver-wrench"></i>{{ $quotation->service_type }}
                                        </span>

                                        @if ($flow === 'direct_service')
                                            <span class="flow-chip direct"><i class="fas fa-bolt"></i>Direct Service</span>
                                        @else
                                            <span class="flow-chip inspect"><i class="fas fa-search-location"></i>Inspection Required</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="summary-meta">
                                    <div class="summary-meta-label">Preferred Date</div>
                                    <div class="summary-meta-value">
                                        {{ optional($quotation->preferred_date)->format('Y-m-d') ?? '—' }}
                                    </div>
                                </div>

                                <div class="summary-meta">
                                    <div class="summary-meta-label">Status</div>
                                    <div class="summary-meta-value">
                                        @if ($quotation->jobOrder)
                                            <span class="badge-soft blue">Job {{ ucfirst(str_replace('_', ' ', $quotation->jobOrder->status)) }}</span>
                                        @elseif ($quotation->status === 'pending')
                                            <span class="badge-soft orange">Pending</span>
                                        @elseif ($quotation->status === 'assigned')
                                            <span class="badge-soft blue">Assigned</span>
                                        @elseif ($quotation->status === 'completed')
                                            <span class="badge-soft green">Completed</span>
                                        @else
                                            <span class="badge-soft gray">{{ ucfirst(str_replace('_', ' ', $quotation->status)) }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="summary-meta">
                                    <div class="summary-meta-label">{{ $assignmentLabel }}</div>
                                    <div class="summary-meta-value">{{ $quotation->worker?->name ?? 'Not assigned' }}</div>
                                </div>

                                <div class="summary-arrow"><i class="fas fa-chevron-down"></i></div>
                            </summary>

                            <div class="quotation-body">
                                <div class="quotation-body-grid">
                                    <div class="info-column">
                                        <div class="q-card simplified-card">
                                            <div class="q-card-title">
                                                <i class="fas fa-list-check"></i>Request Status
                                            </div>

                                            <div class="simple-progress">
                                                <div class="simple-step completed">
                                                    <div class="simple-step-icon"><i class="fas fa-paper-plane"></i></div>
                                                    <div>
                                                        <strong>Submitted</strong>
                                                        <span>{{ optional($quotation->created_at)->format('M d, Y h:i A') }}</span>
                                                    </div>
                                                </div>

                                                <div class="simple-step {{ $hasAssigned ? 'completed' : 'waiting' }}">
                                                    <div class="simple-step-icon"><i class="fas fa-user-check"></i></div>
                                                    <div>
                                                        <strong>Assigned</strong>
                                                        <span>{{ $hasAssigned ? ($quotation->worker?->name ?? 'Assigned') : 'Waiting' }}</span>
                                                    </div>
                                                </div>

                                                <div class="simple-step {{ $hasScheduled ? 'completed' : 'waiting' }}">
                                                    <div class="simple-step-icon"><i class="fas fa-calendar-check"></i></div>
                                                    <div>
                                                        <strong>Scheduled</strong>
                                                        <span>
                                                            @if ($hasScheduled)
                                                                {{ optional($quotation->appointment_date)->format('M d, Y') }} • {{ date('h:i A', strtotime($quotation->appointment_time)) }}
                                                            @else
                                                                Waiting
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="simple-step {{ $hasJobOrder ? 'completed' : 'waiting' }}">
                                                    <div class="simple-step-icon"><i class="fas fa-clipboard-check"></i></div>
                                                    <div>
                                                        <strong>Job Order</strong>
                                                        <span>{{ $hasJobOrder ? 'Created' : 'Not yet created' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="q-card simplified-card">
                                            <div class="q-card-title">
                                                <i class="fas fa-circle-info"></i>Request Summary
                                            </div>

                                            <div class="summary-clean-grid">
                                                <div class="summary-clean-box main">
                                                    <span>Service Needed</span>
                                                    <strong>{{ $quotation->service_type }}</strong>
                                                </div>

                                                <div class="summary-clean-box">
                                                    <span>Client</span>
                                                    <strong>{{ $clientName ?: 'Unnamed Client' }}</strong>
                                                </div>

                                                <div class="summary-clean-box">
                                                    <span>Preferred Date</span>
                                                    <strong>{{ optional($quotation->preferred_date)->format('Y-m-d') ?? 'Not set' }}</strong>
                                                </div>

                                                <div class="summary-clean-box">
                                                    <span>Address</span>
                                                    <strong>{{ $quotation->address }}</strong>
                                                </div>
                                            </div>

                                            <div class="system-decision-card {{ $flow === 'direct_service' ? 'direct' : 'inspect' }}">
                                                <div class="decision-icon">
                                                    @if ($flow === 'direct_service')
                                                        <i class="fas fa-bolt"></i>
                                                    @else
                                                        <i class="fas fa-search-location"></i>
                                                    @endif
                                                </div>
                                                <div>
                                                    <h6>System Decision: {{ $recommendationLabel }}</h6>
                                                    <p>{{ $recommendationText }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="q-card simplified-card details-button-card">
                                            <button
                                                type="button"
                                                class="q-modal-trigger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#requestDetailsModal{{ $quotation->id }}"
                                            >
                                                <span><i class="fas fa-folder-open me-2"></i>View Full Request Details</span>
                                                <i class="fas fa-up-right-from-square"></i>
                                            </button>
                                        </div>

                                        <div class="modal fade wr-admin-modal" id="requestDetailsModal{{ $quotation->id }}" tabindex="-1" aria-labelledby="requestDetailsModalLabel{{ $quotation->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <div>
                                                            <h5 class="modal-title" id="requestDetailsModalLabel{{ $quotation->id }}">Request Details</h5>
                                                            <p class="modal-subtitle mb-0">{{ $clientName ?: 'Unnamed Client' }} • {{ $quotation->service_type }}</p>
                                                        </div>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>

                                                    <div class="modal-body">
                                                        <div class="modal-section">
                                                            <div class="modal-section-title">Request Information</div>
                                                            <div class="summary-clean-grid modal-grid">
                                                                <div class="summary-clean-box">
                                                                    <span>Category</span>
                                                                    <strong>{{ ucfirst($quotation->service_category) }}</strong>
                                                                </div>

                                                                <div class="summary-clean-box">
                                                                    <span>Project Type</span>
                                                                    <strong>{{ $quotation->project_type ?? '—' }}</strong>
                                                                </div>

                                                                <div class="summary-clean-box">
                                                                    <span>Appointment Status</span>
                                                                    <strong>{{ ucfirst(str_replace('_', ' ', $quotation->appointment_status ?? 'pending')) }}</strong>
                                                                </div>

                                                                <div class="summary-clean-box">
                                                                    <span>Assigned At</span>
                                                                    <strong>{{ optional($quotation->assigned_at)->format('Y-m-d h:i A') ?? '—' }}</strong>
                                                                </div>

                                                                <div class="summary-clean-box">
                                                                    <span>Assigned To</span>
                                                                    <strong>{{ $quotation->worker?->name ?? 'Not assigned' }}</strong>
                                                                </div>

                                                                <div class="summary-clean-box">
                                                                    <span>Schedule</span>
                                                                    <strong>
                                                                        @if ($quotation->appointment_date && $quotation->appointment_time)
                                                                            {{ optional($quotation->appointment_date)->format('Y-m-d') }} • {{ date('h:i A', strtotime($quotation->appointment_time)) }}
                                                                        @else
                                                                            Not set
                                                                        @endif
                                                                    </strong>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="modal-section">
                                                            <div class="modal-section-title">Client Request Details</div>
                                                            <div class="notes-box">{{ $quotation->details }}</div>
                                                        </div>

                                                        <div class="modal-section">
                                                            <div class="modal-section-title">Inspector Notes</div>
                                                            <div class="notes-box">{{ $quotation->inspector_notes ?? 'No inspector notes yet.' }}</div>
                                                        </div>

                                                        @if ($quotation->appointment_status === 'cancelled' && $quotation->cancel_reason)
                                                            <div class="modal-section">
                                                                <div class="modal-section-title">Cancellation Reason</div>
                                                                <div class="notes-box">{{ $quotation->cancel_reason }}</div>
                                                            </div>
                                                        @endif

                                                        @if (!empty($quotation->flow_override_reason))
                                                            <div class="modal-section">
                                                                <div class="modal-section-title">Admin Change Reason</div>
                                                                <div class="notes-box">{{ $quotation->flow_override_reason }}</div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="action-column">
                                        <div class="action-card guided-action-card">
                                            <div class="action-title">
                                                <i class="fas fa-bolt"></i>Admin Action Center
                                            </div>

                                            <div class="guided-recommendation {{ $flow === 'direct_service' ? 'direct' : 'inspect' }}">
                                                <div class="guided-recommendation-icon">
                                                    @if ($flow === 'direct_service')
                                                        <i class="fas fa-bolt"></i>
                                                    @else
                                                        <i class="fas fa-search-location"></i>
                                                    @endif
                                                </div>

                                                <div>
                                                    <div class="action-label">System Decision</div>
                                                    <h5>{{ $recommendationLabel }}</h5>
                                                    <p>{{ $recommendationText }}</p>
                                                </div>
                                            </div>

                                            @if ($quotation->client_action_status === 'pending')
                                                <div class="guided-alert-card">
                                                    <div class="guided-alert-title">
                                                        <i class="fas fa-user-clock"></i>
                                                        Client has a pending request
                                                    </div>

                                                    <p class="mb-2">Review the client’s request before continuing.</p>

                                                    <details class="guided-details">
                                                        <summary>Review client request</summary>

                                                        <div class="guided-details-body">
                                                            <div class="notes-box mb-3">
                                                                <strong>Request:</strong> {{ ucfirst($quotation->client_action_request) }}<br>

                                                                @if ($quotation->client_requested_date)
                                                                    <strong>Requested Date:</strong> {{ optional($quotation->client_requested_date)->format('Y-m-d') }}<br>
                                                                @endif

                                                                @if ($quotation->client_requested_time)
                                                                    <strong>Requested Time:</strong> {{ date('h:i A', strtotime($quotation->client_requested_time)) }}<br>
                                                                @endif

                                                                <strong>Reason:</strong> {{ $quotation->client_request_reason }}
                                                            </div>

                                                            <form method="POST" action="{{ route('admin.quotations.review-client-request', $quotation) }}">
                                                                @csrf

                                                                <div class="mb-3">
                                                                    <label class="form-label">Review Notes</label>
                                                                    <textarea name="client_request_review_notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                                                                </div>

                                                                <div class="row g-2">
                                                                    <div class="col-6">
                                                                        <button name="decision" value="declined" class="btn btn-outline-danger w-100">Decline</button>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <button name="decision" value="approved" class="btn btn-action-primary w-100">Approve</button>
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </details>
                                                </div>
                                            @endif

                                            <div class="next-action-panel">
                                                <div class="next-action-header">
                                                    <div class="next-action-icon">
                                                        @if (!$hasAssigned)
                                                            <span>1</span>
                                                        @elseif (!$hasScheduled)
                                                            <span>2</span>
                                                        @elseif (!$hasJobOrder)
                                                            <span>3</span>
                                                        @else
                                                            <i class="fas fa-check"></i>
                                                        @endif
                                                    </div>

                                                    <div>
                                                        <div class="action-label">Next Action</div>

                                                        @if (!$hasAssigned)
                                                            <h4>Assign {{ $assignmentLabel }}</h4>
                                                            <p>Select the available person who will handle this request.</p>
                                                        @elseif (!$hasScheduled)
                                                            <h4>Set {{ $scheduleLabel }} Schedule</h4>
                                                            <p>Confirm the date and time for the service or inspection.</p>
                                                        @elseif (!$hasJobOrder)
                                                            <h4>Create Job Order</h4>
                                                            <p>The request is ready for service execution setup.</p>
                                                        @else
                                                            <h4>Job Order Created</h4>
                                                            <p>This request already has a job order record.</p>
                                                        @endif
                                                    </div>
                                                </div>

                                                @if (!$hasAssigned)
                                                    <form method="POST" action="{{ route('admin.quotations.assign-worker', $quotation) }}">
                                                        @csrf

                                                        <div class="mb-3">
                                                            <label class="form-label">Available {{ $assignmentLabel }}</label>
                                                            <select name="worker_id" class="form-select" required @disabled(!$hasAvailableWorkers)>
                                                                <option value="">
                                                                    {{ $hasAvailableWorkers ? 'Select ' . strtolower($assignmentLabel) : 'No available ' . strtolower($assignmentLabel) }}
                                                                </option>

                                                                @foreach ($availableWorkers as $worker)
                                                                    <option value="{{ $worker->id }}" @selected($quotation->worker_id == $worker->id)>
                                                                        {{ $worker->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="next-action-note">
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
                                                            <label class="form-label">Assignment Notes</label>
                                                            <textarea
                                                                name="admin_notes"
                                                                class="form-control"
                                                                rows="2"
                                                                placeholder="Add assignment notes..."
                                                            >{{ old('admin_notes', $quotation->admin_notes) }}</textarea>
                                                        </div>

                                                        <button class="btn btn-action-primary w-100" @disabled(!$hasAvailableWorkers)>
                                                            <i class="fas fa-user-check me-2"></i>Save Assignment
                                                        </button>
                                                    </form>
                                                @elseif (!$hasScheduled)
                                                    <form method="POST" action="{{ route('admin.quotations.update-appointment', $quotation) }}">
                                                        @csrf

                                                        <input type="hidden" name="appointment_status" value="approved">

                                                        <div class="row g-2">
                                                            <div class="col-md-6">
                                                                <label class="form-label">{{ $scheduleLabel }} Date</label>
                                                                <input
                                                                    type="date"
                                                                    name="appointment_date"
                                                                    class="form-control"
                                                                    value="{{ old('appointment_date', optional($quotation->appointment_date ?? $quotation->preferred_date)->format('Y-m-d')) }}"
                                                                    required
                                                                >
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label class="form-label">{{ $scheduleLabel }} Time</label>
                                                                <input
                                                                    type="text"
                                                                    name="appointment_time"
                                                                    class="form-control"
                                                                    value="{{ $scheduleTimeDisplay }}"
                                                                    placeholder="01:02 PM"
                                                                    inputmode="numeric"
                                                                    required
                                                                >
                                                            </div>
                                                        </div>

                                                        @error('appointment_date')
                                                            <small class="text-danger d-block mt-2">{{ $message }}</small>
                                                        @enderror

                                                        @error('appointment_time')
                                                            <small class="text-danger d-block mt-2">{{ $message }}</small>
                                                        @enderror

                                                        <button class="btn btn-action-primary w-100 mt-3">
                                                            <i class="fas fa-calendar-check me-2"></i>Save Schedule
                                                        </button>
                                                    </form>
                                                @elseif (!$hasJobOrder)
                                                    <div class="ready-job-card">
                                                        <i class="fas fa-circle-check"></i>
                                                        <div>
                                                            <strong>Ready for job order</strong>
                                                            <span>Assignment and schedule are already set.</span>
                                                        </div>
                                                    </div>

                                                    <a href="{{ route('admin.job-orders.create', $quotation) }}" class="btn btn-action-success w-100">
                                                        <i class="fas fa-plus-circle me-2"></i>Create Job Order
                                                    </a>
                                                @else
                                                    <div class="ready-job-card">
                                                        <i class="fas fa-circle-check"></i>
                                                        <div>
                                                            <strong>{{ $quotation->jobOrder->job_order_no }}</strong>
                                                            <span>Status: {{ ucfirst(str_replace('_', ' ', $quotation->jobOrder->status)) }}</span>
                                                        </div>
                                                    </div>

                                                    <a href="{{ route('admin.job-orders.show', $quotation->jobOrder) }}" class="btn btn-action-outline w-100">
                                                        <i class="fas fa-eye me-2"></i>View Job Order
                                                    </a>
                                                @endif
                                            </div>

                                            <button
                                                type="button"
                                                class="q-modal-trigger q-modal-trigger-muted"
                                                data-bs-toggle="modal"
                                                data-bs-target="#moreOptionsModal{{ $quotation->id }}"
                                            >
                                                <span><i class="fas fa-sliders me-2"></i>More Options</span>
                                                <i class="fas fa-up-right-from-square"></i>
                                            </button>

                                            <div class="modal fade wr-admin-modal" id="moreOptionsModal{{ $quotation->id }}" tabindex="-1" aria-labelledby="moreOptionsModalLabel{{ $quotation->id }}" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <div>
                                                                <h5 class="modal-title" id="moreOptionsModalLabel{{ $quotation->id }}">More Options</h5>
                                                                <p class="modal-subtitle mb-0">Use only when the system decision needs manual review.</p>
                                                            </div>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>

                                                        <div class="modal-body">
                                                            <div class="mini-status-grid mb-3">
                                                                <div>
                                                                    <span>Assigned To</span>
                                                                    <strong>{{ $quotation->worker?->name ?? 'Not assigned' }}</strong>
                                                                </div>
                                                                <div>
                                                                    <span>Schedule</span>
                                                                    <strong>
                                                                        @if ($quotation->appointment_date && $quotation->appointment_time)
                                                                            {{ optional($quotation->appointment_date)->format('Y-m-d') }} • {{ date('h:i A', strtotime($quotation->appointment_time)) }}
                                                                        @else
                                                                            Not set
                                                                        @endif
                                                                    </strong>
                                                                </div>
                                                                <div>
                                                                    <span>System Decision</span>
                                                                    <strong>{{ $recommendationLabel }}</strong>
                                                                </div>
                                                            </div>

                                                            <form method="POST" action="{{ route('admin.quotations.update-flow', $quotation) }}">
                                                                @csrf
                                                                @method('PATCH')

                                                                <div class="mb-3">
                                                                    <label class="form-label">Change System Decision</label>
                                                                    <select name="service_flow" class="form-select" required>
                                                                        <option value="direct_service" @selected($flow === 'direct_service')>Direct Service</option>
                                                                        <option value="inspection_required" @selected($flow === 'inspection_required')>Inspection Required</option>
                                                                    </select>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label">Reason</label>
                                                                    <textarea
                                                                        name="flow_override_reason"
                                                                        class="form-control"
                                                                        rows="3"
                                                                        placeholder="Optional admin note"
                                                                    >{{ old('flow_override_reason', $quotation->flow_override_reason) }}</textarea>
                                                                </div>

                                                                <button class="btn btn-action-outline w-100">
                                                                    <i class="fas fa-save me-2"></i>Save Changes
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </details>
                    @endforeach
                </div>

                <div class="mt-3">
                    {{ $quotations->links() }}
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-file-circle-xmark"></i>
                    </div>
                    <div class="fw-bold text-dark mb-1">No quotation requests found</div>
                    <div class="text-muted">Incoming service requests will appear here.</div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
