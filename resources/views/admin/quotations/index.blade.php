@extends('admin.layouts.app')

@section('title', 'View Quotations - WRPlumb')
@section('topbar_title', 'View Quotations')
@section('topbar_subtitle', 'Review quotation requests and manage assignment, schedule, and job order flow.')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/quotations.css') }}">
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
                <div class="q-stat-label">Pending on Page</div>
                <div class="q-stat-value">{{ $pendingCount }}</div>
            </div>
        </div>

        <div class="q-stat-card">
            <div class="q-stat-icon green"><i class="fas fa-user-check"></i></div>
            <div>
                <div class="q-stat-label">Assigned on Page</div>
                <div class="q-stat-value">{{ $assignedCount }}</div>
            </div>
        </div>

        <div class="q-stat-card">
            <div class="q-stat-icon gray"><i class="fas fa-clipboard-check"></i></div>
            <div>
                <div class="q-stat-label">Active Jobs on Page</div>
                <div class="q-stat-value">{{ $activeJobCount }}</div>
            </div>
        </div>
    </div>

    <div class="q-toolbar-card">
        <div class="q-toolbar-head">
            <div>
                <h5 class="q-toolbar-title"><i class="fas fa-filter me-2 text-primary"></i>Filter Requests</h5>
                <div class="text-muted small mt-1">Search and narrow quotation requests before reviewing details.</div>
            </div>

            <div class="q-filter-count">
                <i class="fas fa-sliders"></i>
                {{ $activeFilterCount }} active {{ \Illuminate\Support\Str::plural('filter', $activeFilterCount) }}
            </div>
        </div>

        <div class="q-toolbar-body">
            <form method="GET" class="row g-3 align-items-end">
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
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        <option value="assigned" @selected(request('status') === 'assigned')>Assigned</option>
                        <option value="in_progress" @selected(request('status') === 'in_progress')>In Progress</option>
                        <option value="completed" @selected(request('status') === 'completed')>Completed</option>
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
                        <i class="fas fa-magnifying-glass me-1"></i> Apply
                    </button>
                    <a href="{{ route('admin.quotations.index') }}" class="btn btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="q-list-panel">
        <div class="q-list-head">
            <div>
                <h5 class="q-list-title"><i class="fas fa-file-signature me-2 text-primary"></i>All Quotation Requests</h5>
                <div class="q-list-subtitle">Showing {{ $displayedCount }} request(s) on this page. Expand a row to review and take action.</div>
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
                            $scheduleLabel = $flow === 'direct_service' ? 'Service' : 'Appointment';
                        @endphp

                        <details class="quotation-item">
                            <summary class="quotation-summary">
                                <div class="summary-main">
                                    <div class="summary-name-row">
                                        <div class="summary-name">{{ $clientName ?: 'Unnamed Client' }}</div>
                                    </div>

                                    <div class="summary-sub">
                                        {{ $quotation->service_type }} • {{ $quotation->email }} • {{ $quotation->phone }}
                                    </div>

                                    <div class="summary-chip-row">
                                        @if ($flow === 'direct_service')
                                            <span class="flow-chip direct">
                                                <i class="fas fa-bolt"></i> Direct Service
                                            </span>
                                        @else
                                            <span class="flow-chip inspect">
                                                <i class="fas fa-search-location"></i> Inspection First
                                            </span>
                                        @endif

                                        <span class="service-chip">
                                            <i class="fas fa-calendar-day"></i>
                                            {{ ucfirst($visitPurpose) }} Visit
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <div class="summary-meta-label">Preferred Date</div>
                                    <div class="summary-meta-value">
                                        {{ optional($quotation->preferred_date)->format('Y-m-d') ?? '—' }}
                                    </div>
                                </div>

                                <div>
                                    <div class="summary-meta-label">Status</div>
                                    <div class="summary-meta-value mt-1">
                                        @if ($quotation->jobOrder)
                                            @if ($quotation->jobOrder->status === 'scheduled')
                                                <span class="badge-soft blue">Job Scheduled</span>
                                            @elseif ($quotation->jobOrder->status === 'in_progress')
                                                <span class="badge-soft green">Job In Progress</span>
                                            @elseif ($quotation->jobOrder->status === 'completed')
                                                <span class="badge-soft green">Job Completed</span>
                                            @elseif ($quotation->jobOrder->status === 'cancelled')
                                                <span class="badge-soft red">Job Cancelled</span>
                                            @else
                                                <span class="badge-soft gray">{{ ucfirst(str_replace('_', ' ', $quotation->jobOrder->status)) }}</span>
                                            @endif
                                        @else
                                            @if ($quotation->status === 'pending')
                                                <span class="badge-soft orange">Pending</span>
                                            @elseif ($quotation->status === 'assigned')
                                                <span class="badge-soft blue">Assigned</span>
                                            @elseif ($quotation->status === 'in_progress')
                                                <span class="badge-soft green">In Progress</span>
                                            @elseif ($quotation->status === 'completed')
                                                <span class="badge-soft green">Completed</span>
                                            @else
                                                <span class="badge-soft gray">{{ ucfirst(str_replace('_', ' ', $quotation->status)) }}</span>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <div>
                                    <div class="summary-meta-label">{{ $assignmentLabel }}</div>
                                    <div class="summary-meta-value">
                                        {{ $quotation->worker?->name ?? 'Not assigned' }}
                                    </div>
                                </div>

                                <div class="summary-arrow">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </summary>

                            <div class="quotation-body">
                                <div class="quotation-body-grid">
                                    <div class="info-column">
                                        <div class="q-card">
                                            <div class="q-card-title">
                                                <i class="fas fa-timeline"></i> Request Timeline
                                            </div>
                                            @include('partials.request-timeline', ['quotation' => $quotation])
                                        </div>

                                        <div class="q-card">
                                            <div class="q-card-title">
                                                <i class="fas fa-circle-info"></i> Request Overview
                                            </div>

                                            <div class="mb-3 d-flex flex-wrap gap-2">
                                                <span class="service-chip">
                                                    <i class="fas fa-screwdriver-wrench"></i>
                                                    {{ $quotation->service_type }}
                                                </span>

                                                @if ($flow === 'direct_service')
                                                    <span class="flow-chip direct">
                                                        <i class="fas fa-bolt"></i> Direct Service
                                                    </span>
                                                @else
                                                    <span class="flow-chip inspect">
                                                        <i class="fas fa-search-location"></i> Inspection Required
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="detail-grid">
                                                <div class="detail-box">
                                                    <div class="detail-label">Category</div>
                                                    <div class="detail-value">{{ ucfirst($quotation->service_category) }}</div>
                                                </div>

                                                <div class="detail-box">
                                                    <div class="detail-label">Project Type</div>
                                                    <div class="detail-value">{{ $quotation->project_type ?? '—' }}</div>
                                                </div>

                                                <div class="detail-box">
                                                    <div class="detail-label">Address</div>
                                                    <div class="detail-value">{{ $quotation->address }}</div>
                                                </div>

                                                <div class="detail-box">
                                                    <div class="detail-label">Assigned At</div>
                                                    <div class="detail-value">{{ optional($quotation->assigned_at)->format('Y-m-d h:i A') ?? '—' }}</div>
                                                </div>

                                                <div class="detail-box">
                                                    <div class="detail-label">{{ $quotation->jobOrder ? 'Job Order Status' : 'Appointment Status' }}</div>
                                                    <div class="detail-value">
                                                        @if ($quotation->jobOrder)
                                                            {{ ucfirst(str_replace('_', ' ', $quotation->jobOrder->status)) }}
                                                        @else
                                                            {{ ucfirst(str_replace('_', ' ', $quotation->appointment_status ?? 'pending')) }}
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="detail-box">
                                                    <div class="detail-label">{{ $scheduleLabel }} Schedule</div>
                                                    <div class="detail-value">
                                                        @if ($quotation->appointment_date && $quotation->appointment_time)
                                                            {{ optional($quotation->appointment_date)->format('Y-m-d') }} • {{ date('h:i A', strtotime($quotation->appointment_time)) }}
                                                        @else
                                                            Not yet scheduled
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="detail-box">
                                                    <div class="detail-label">Service Flow</div>
                                                    <div class="detail-value text-uppercase">{{ str_replace('_', ' ', $flow) }}</div>
                                                </div>

                                                <div class="detail-box">
                                                    <div class="detail-label">Flow Source</div>
                                                    <div class="detail-value text-uppercase">{{ $quotation->flow_source ?? 'system' }}</div>
                                                </div>
                                            </div>

                                            @if ($flow === 'direct_service')
                                                <div class="flow-alert direct mb-0">
                                                    This request is classified as <strong>Direct Service</strong>. The preferred date may be treated as the preferred actual service date.
                                                </div>
                                            @else
                                                <div class="flow-alert inspect mb-0">
                                                    This request is classified as <strong>Inspection Required</strong>. The preferred date should be treated as the preferred site visit date first.
                                                </div>
                                            @endif
                                        </div>

                                        <div class="q-card">
                                            <div class="q-card-title">
                                                <i class="fas fa-note-sticky"></i> Notes and Request Details
                                            </div>

                                            <div class="detail-label mb-2">Client Request Details</div>
                                            <div class="notes-box mb-3">
                                                {{ $quotation->details }}
                                            </div>

                                            <div class="detail-label mb-2">Inspector Notes</div>
                                            <div class="notes-box">
                                                {{ $quotation->inspector_notes ?? 'No inspector notes yet.' }}
                                            </div>

                                            @if ($quotation->appointment_status === 'cancelled' && $quotation->cancel_reason)
                                                <div class="detail-label mb-2 mt-3">Cancellation Reason</div>
                                                <div class="notes-box">
                                                    {{ $quotation->cancel_reason }}
                                                </div>
                                            @endif

                                            @if (!empty($quotation->flow_override_reason))
                                                <div class="detail-label mb-2 mt-3">Flow Override Reason</div>
                                                <div class="notes-box">
                                                    {{ $quotation->flow_override_reason }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="action-column">
                                        <div class="action-card">
                                            <div class="action-title">
                                                <i class="fas fa-bolt"></i> Admin Action Center
                                            </div>

                                            <div class="action-stack">
                                                <div class="job-order-box {{ $quotation->jobOrder ? '' : (($quotation->worker_id && ($quotation->appointment_date || $quotation->preferred_date)) ? 'job-order-ready' : 'job-order-blocked') }}">
                                                    <div class="action-label">Job Order</div>

                                                    @if ($quotation->jobOrder)
                                                        <div class="fw-bold mb-1">{{ $quotation->jobOrder->job_order_no }}</div>
                                                        <div class="text-muted small mb-3">
                                                            Status: {{ ucfirst(str_replace('_', ' ', $quotation->jobOrder->status)) }}
                                                            @if ($quotation->jobOrder->scheduled_date)
                                                                <br>Schedule: {{ optional($quotation->jobOrder->scheduled_date)->format('Y-m-d') }}
                                                                @if ($quotation->jobOrder->scheduled_time)
                                                                    • {{ $quotation->jobOrder->scheduled_time }}
                                                                @endif
                                                            @endif
                                                        </div>

                                                        <a href="{{ route('admin.job-orders.show', $quotation->jobOrder) }}" class="btn btn-action-outline w-100">
                                                            <i class="fas fa-eye me-2"></i>View Job Order
                                                        </a>
                                                    @else
                                                        @if ($quotation->worker_id && ($quotation->appointment_date || $quotation->preferred_date))
                                                            <div class="small text-success fw-bold mb-3">
                                                                This request is ready for service execution setup.
                                                            </div>

                                                            <a href="{{ route('admin.job-orders.create', $quotation) }}" class="btn btn-action-success w-100">
                                                                <i class="fas fa-plus-circle me-2"></i>Create Job Order
                                                            </a>
                                                        @else
                                                            <div class="small text-muted mb-3">
                                                                Assign {{ strtolower($assignmentLabel) }} and provide a {{ strtolower($scheduleLabel) }} schedule first before creating a job order.
                                                            </div>

                                                            <button class="btn btn-outline-secondary w-100" disabled>
                                                                <i class="fas fa-ban me-2"></i>Not Ready Yet
                                                            </button>
                                                        @endif
                                                    @endif
                                                </div>

                                                @if ($quotation->client_action_status === 'pending')
                                                    <details class="action-section" open>
                                                        <summary>
                                                            <span><i class="fas fa-user-clock me-2 text-warning"></i>Client Request Review</span>
                                                            <i class="fas fa-chevron-down section-chevron"></i>
                                                        </summary>
                                                        <div class="action-section-body">
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
                                                                    <label class="form-label">Decision</label>
                                                                    <select name="decision" class="form-select" required>
                                                                        <option value="">Select decision</option>
                                                                        <option value="approved">Approve</option>
                                                                        <option value="declined">Decline</option>
                                                                    </select>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label">Review Notes</label>
                                                                    <textarea name="client_request_review_notes" class="form-control" rows="3"></textarea>
                                                                </div>

                                                                <button class="btn btn-action-primary w-100">Submit Review</button>
                                                            </form>
                                                        </div>
                                                    </details>
                                                @endif

                                                <details class="action-section" open>
                                                    <summary>
                                                        <span><i class="fas fa-user-check me-2 text-primary"></i>Assign {{ $assignmentLabel }}</span>
                                                        <i class="fas fa-chevron-down section-chevron"></i>
                                                    </summary>
                                                    <div class="action-section-body">
                                                        <form method="POST" action="{{ route('admin.quotations.assign-worker', $quotation) }}">
                                                            @csrf

                                                            <div class="mb-2">
                                                                <label class="form-label">{{ $assignmentLabel }}</label>
                                                                <select
                                                                    name="worker_id"
                                                                    class="form-select"
                                                                    required
                                                                    @disabled(!$hasAvailableWorkers)
                                                                >
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

                                                            <div class="small text-muted mb-3">
                                                                @if ($quotation->preferred_date)
                                                                    @if ($hasAvailableWorkers)
                                                                        Showing available workers on {{ optional($quotation->preferred_date)->format('Y-m-d') }}.
                                                                    @else
                                                                        No workers marked available on {{ optional($quotation->preferred_date)->format('Y-m-d') }}.
                                                                    @endif
                                                                @else
                                                                    Preferred date not set. Showing all active workers.
                                                                @endif
                                                            </div>

                                                            @if ($flow === 'direct_service')
                                                                <div class="flow-alert direct">
                                                                    This request may proceed directly to service scheduling and worker assignment.
                                                                </div>
                                                            @else
                                                                <div class="flow-alert inspect">
                                                                    This request should go through inspection before quotation and execution.
                                                                </div>
                                                            @endif

                                                            <div class="mb-3">
                                                                <label class="form-label">Assignment Notes</label>
                                                                <textarea
                                                                    name="admin_notes"
                                                                    class="form-control"
                                                                    rows="3"
                                                                    placeholder="Add assignment notes..."
                                                                >{{ old('admin_notes', $quotation->admin_notes) }}</textarea>
                                                            </div>

                                                            <button class="btn btn-action-primary w-100" @disabled(!$hasAvailableWorkers)>
                                                                <i class="fas fa-user-check me-2"></i>Save Assignment
                                                            </button>
                                                        </form>
                                                    </div>
                                                </details>

                                                <details class="action-section" open>
                                                    <summary>
                                                        <span><i class="fas fa-calendar-check me-2 text-primary"></i>Schedule {{ $scheduleLabel }}</span>
                                                        <i class="fas fa-chevron-down section-chevron"></i>
                                                    </summary>
                                                    <div class="action-section-body">
                                                        @if ($flow === 'direct_service')
                                                            <div class="flow-alert direct">
                                                                This request is marked as <strong>Direct Service</strong>. The client’s preferred date/time may be used as the actual service schedule.
                                                            </div>
                                                        @endif

                                                        <form method="POST" action="{{ route('admin.quotations.update-appointment', $quotation) }}">
                                                            @csrf

                                                            <div class="mb-3">
                                                                <label class="form-label">{{ $scheduleLabel }} Status</label>
                                                                <select name="appointment_status" class="form-select" required>
                                                                    <option value="pending" @selected($quotation->appointment_status === 'pending')>Pending</option>
                                                                    <option value="approved" @selected($quotation->appointment_status === 'approved')>Approved</option>
                                                                    <option value="rescheduled" @selected($quotation->appointment_status === 'rescheduled')>Rescheduled</option>
                                                                    <option value="cancelled" @selected($quotation->appointment_status === 'cancelled')>Cancelled</option>
                                                                </select>
                                                            </div>
                                                            @error('appointment_status')
                                                                <small class="text-danger d-block mb-2">{{ $message }}</small>
                                                            @enderror

                                                            <div class="row g-2">
                                                                <div class="col-md-6">
                                                                    <label class="form-label">{{ $scheduleLabel }} Date</label>
                                                                    <input
                                                                        type="date"
                                                                        name="appointment_date"
                                                                        class="form-control"
                                                                        value="{{ old('appointment_date', optional($quotation->appointment_date)->format('Y-m-d')) }}"
                                                                    >
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <label class="form-label">{{ $scheduleLabel }} Time</label>
                                                                    <input
                                                                        type="time"
                                                                        name="appointment_time"
                                                                        class="form-control"
                                                                        value="{{ old('appointment_time', $quotation->appointment_time) }}"
                                                                    >
                                                                </div>
                                                            </div>

                                                            @error('appointment_date')
                                                                <small class="text-danger d-block mt-2">{{ $message }}</small>
                                                            @enderror
                                                            @error('appointment_time')
                                                                <small class="text-danger d-block mt-2">{{ $message }}</small>
                                                            @enderror

                                                            <div class="mt-3">
                                                                <label class="form-label">Cancel Reason</label>
                                                                <textarea
                                                                    name="cancel_reason"
                                                                    class="form-control"
                                                                    rows="2"
                                                                    placeholder="Optional reason if cancelled..."
                                                                >{{ old('cancel_reason', $quotation->cancel_reason) }}</textarea>
                                                            </div>
                                                            @error('cancel_reason')
                                                                <small class="text-danger d-block mt-2">{{ $message }}</small>
                                                            @enderror

                                                            <button class="btn btn-action-outline w-100 mt-3">
                                                                <i class="fas fa-calendar-check me-2"></i>Save {{ $scheduleLabel }}
                                                            </button>
                                                        </form>
                                                    </div>
                                                </details>

                                                <details class="action-section">
                                                    <summary>
                                                        <span><i class="fas fa-shuffle me-2 text-primary"></i>Service Flow Override</span>
                                                        <i class="fas fa-chevron-down section-chevron"></i>
                                                    </summary>
                                                    <div class="action-section-body">
                                                        <form method="POST" action="{{ route('admin.quotations.update-flow', $quotation) }}">
                                                            @csrf
                                                            @method('PATCH')

                                                            <div class="mb-3">
                                                                <label class="form-label">Current Flow</label>
                                                                <div class="notes-box">
                                                                    <strong>{{ $flow === 'direct_service' ? 'Direct Service' : 'Inspection Required' }}</strong><br>
                                                                    Visit Purpose: {{ ucfirst($visitPurpose) }}<br>
                                                                    Source: {{ strtoupper($quotation->flow_source ?? 'system') }}
                                                                </div>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label">Override Flow</label>
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

                                                            <button class="btn btn-action-primary w-100">
                                                                <i class="fas fa-shuffle me-2"></i>Update Flow
                                                            </button>
                                                        </form>
                                                    </div>
                                                </details>
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
                    <div class="text-muted">Incoming service quotation requests will appear here.</div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
