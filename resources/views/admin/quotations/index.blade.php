@extends('admin.layouts.app')

@section('title', 'View Quotations - WRPlumb')
@section('topbar_title', 'View Quotations')
@section('topbar_subtitle', 'Review quotation requests and assign available inspectors.')

@section('content')
<style>
    .quotation-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .quotation-item {
        border: 1px solid var(--wr-border);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }

    .quotation-item summary {
        list-style: none;
        cursor: pointer;
    }

    .quotation-item summary::-webkit-details-marker {
        display: none;
    }

    .quotation-summary {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr 44px;
        gap: 14px;
        align-items: center;
        padding: 18px 20px;
    }

    .quotation-summary:hover {
        background: #fbfdff;
    }

    .summary-main {
        min-width: 0;
    }

    .summary-name {
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 4px;
    }

    .summary-sub {
        color: var(--wr-muted);
        font-size: 0.9rem;
        line-height: 1.5;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .summary-chip-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
    }

    .summary-meta-label {
        font-size: 0.76rem;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--wr-muted);
        margin-bottom: 4px;
        letter-spacing: 0.03em;
    }

    .summary-meta-value {
        font-weight: 700;
        color: #1f2a37;
    }

    .summary-arrow {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: #eef6ff;
        color: var(--wr-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s ease;
        font-size: 14px;
    }

    .quotation-item[open] .summary-arrow {
        transform: rotate(180deg);
    }

    .quotation-body {
        border-top: 1px solid var(--wr-border);
        padding: 20px;
        background: #fcfdff;
    }

    .quotation-body-grid {
        display: grid;
        grid-template-columns: 1.4fr 1fr;
        gap: 18px;
    }

    .detail-card {
        background: #fff;
        border: 1px solid var(--wr-border);
        border-radius: 16px;
        padding: 16px;
    }

    .detail-title {
        font-size: 0.95rem;
        font-weight: 800;
        margin-bottom: 12px;
        color: #0f172a;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 14px;
    }

    .detail-label {
        font-size: 0.76rem;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--wr-muted);
        margin-bottom: 4px;
        letter-spacing: 0.03em;
    }

    .detail-value {
        font-weight: 700;
        color: #1f2a37;
    }

    .notes-box {
        background: #f8fbff;
        border: 1px solid #edf2f7;
        border-radius: 14px;
        padding: 12px 14px;
        color: #334155;
        font-size: 0.92rem;
        line-height: 1.5;
    }

    .assign-card {
        background: #fff;
        border: 1px solid var(--wr-border);
        border-radius: 16px;
        padding: 16px;
    }

    .service-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        border-radius: 999px;
        background: #eef6ff;
        color: var(--wr-primary-dark);
        font-weight: 700;
        font-size: 0.82rem;
    }

    .flow-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        border-radius: 999px;
        font-weight: 700;
        font-size: 0.78rem;
    }

    .flow-chip.direct {
        background: #dcfce7;
        color: #166534;
    }

    .flow-chip.inspect {
        background: #e0f2fe;
        color: #0369a1;
    }

    .flow-alert {
        border-radius: 14px;
        padding: 12px 14px;
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 14px;
        border: 1px solid transparent;
    }

    .flow-alert.direct {
        background: #f0fdf4;
        color: #166534;
        border-color: #bbf7d0;
    }

    .flow-alert.inspect {
        background: #f0f9ff;
        color: #075985;
        border-color: #bae6fd;
    }

    .compact-details {
        border: 1px solid var(--wr-border);
        border-radius: 16px;
        background: #fff;
        overflow: hidden;
    }

    .compact-details summary {
        list-style: none;
        cursor: pointer;
        padding: 14px 16px;
        font-weight: 800;
        color: #0f172a;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .compact-details summary::-webkit-details-marker {
        display: none;
    }

    .compact-details[open] summary {
        border-bottom: 1px solid var(--wr-border);
    }

    .compact-details-body {
        padding: 16px;
    }

    .compact-note {
        font-size: 0.9rem;
        line-height: 1.5;
    }

    .compact-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    @media (max-width: 767.98px) {
        .compact-grid-2 {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 1199.98px) {
        .quotation-summary {
            grid-template-columns: 1fr 1fr;
        }

        .summary-arrow {
            justify-self: end;
        }

        .quotation-body-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .quotation-summary,
        .detail-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

@if (session('success'))
    <div class="alert alert-success mb-3">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger mb-3">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="panel mb-4">
    <div class="panel-header">
        <h5><i class="fas fa-filter me-2 text-primary"></i>Filter Requests</h5>
    </div>
    <div class="panel-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Client, email, phone, service..."
                    value="{{ request('search') }}"
                >
            </div>

            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="assigned" @selected(request('status') === 'assigned')>Assigned</option>
                    <option value="in_progress" @selected(request('status') === 'in_progress')>In Progress</option>
                    <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Category</label>
                <select name="service_category" class="form-select">
                    <option value="">All</option>
                    <option value="plumbing" @selected(request('service_category') === 'plumbing')>Plumbing</option>
                    <option value="construction" @selected(request('service_category') === 'construction')>Construction</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Preferred Date</label>
                <input type="date" name="preferred_date" class="form-control" value="{{ request('preferred_date') }}">
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary me-2">
                    <i class="fas fa-sliders me-2"></i>Apply Filter
                </button>
                <a href="{{ route('admin.quotations.index') }}" class="btn btn-outline-secondary">
                    Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h5><i class="fas fa-file-signature me-2 text-primary"></i>All Quotation Requests</h5>
    </div>

    <div class="panel-body">
        @if ($quotations->count())
            <div class="quotation-list">
                @foreach ($quotations as $quotation)
                    @php
                        $availableWorkers = $availableWorkersByQuotation[$quotation->id] ?? collect();
                        $hasAvailableWorkers = $availableWorkers->isNotEmpty();
                        $flow = $quotation->service_flow ?? 'inspection_required';
                        $visitPurpose = $quotation->visit_purpose ?? ($flow === 'direct_service' ? 'service' : 'inspection');
                    @endphp

                    <details class="quotation-item">
                        <summary class="quotation-summary">
                            <div class="summary-main">
                                <div class="summary-name">
                                    {{ $quotation->full_name ?? trim($quotation->first_name . ' ' . $quotation->last_name) }}
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
                                            <span class="badge-soft gray">Job Completed</span>
                                        @elseif ($quotation->jobOrder->status === 'cancelled')
                                            <span class="badge-soft gray">Job Cancelled</span>
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
                                            <span class="badge-soft gray">Completed</span>
                                        @else
                                            <span class="badge-soft gray">{{ ucfirst(str_replace('_', ' ', $quotation->status)) }}</span>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <div>
                                <div class="summary-meta-label">
                                    {{ $flow === 'direct_service' ? 'Personnel' : 'Inspector' }}
                                </div>
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
                                <div>
                                    <div class="detail-card mb-3">
                                        <div class="detail-title">Request Timeline</div>
                                        @include('partials.request-timeline', ['quotation' => $quotation])
                                    </div>

                                    <div class="detail-card">
                                        <div class="detail-title">Request Details</div>

                                        <div class="mb-3">
                                            <span class="service-chip">
                                                <i class="fas fa-screwdriver-wrench"></i>
                                                {{ $quotation->service_type }}
                                            </span>
                                        </div>

                                        <div class="detail-grid">
                                            <div>
                                                <div class="detail-label">Category</div>
                                                <div class="detail-value">{{ ucfirst($quotation->service_category) }}</div>
                                            </div>

                                            <div>
                                                <div class="detail-label">Project Type</div>
                                                <div class="detail-value">{{ $quotation->project_type ?? '—' }}</div>
                                            </div>

                                            <div>
                                                <div class="detail-label">Address</div>
                                                <div class="detail-value">{{ $quotation->address }}</div>
                                            </div>

                                            <div>
                                                <div class="detail-label">Assigned At</div>
                                                <div class="detail-value">{{ optional($quotation->assigned_at)->format('Y-m-d h:i A') ?? '—' }}</div>
                                            </div>

                                                <div>
                                                    <div class="detail-label">{{ $quotation->jobOrder ? 'Job Order Status' : 'Appointment Status' }}</div>
                                                    <div class="detail-value">
                                                        @if ($quotation->jobOrder)
                                                            @if ($quotation->jobOrder->status === 'scheduled')
                                                                <span class="badge-soft blue">Scheduled</span>
                                                            @elseif ($quotation->jobOrder->status === 'in_progress')
                                                                <span class="badge-soft green">In Progress</span>
                                                            @elseif ($quotation->jobOrder->status === 'completed')
                                                                <span class="badge-soft gray">Completed</span>
                                                            @elseif ($quotation->jobOrder->status === 'cancelled')
                                                                <span class="badge-soft gray">Cancelled</span>
                                                            @else
                                                                <span class="badge-soft gray">{{ ucfirst(str_replace('_', ' ', $quotation->jobOrder->status)) }}</span>
                                                            @endif
                                                        @else
                                                            @if ($quotation->appointment_status === 'pending')
                                                                <span class="badge-soft orange">Pending</span>
                                                            @elseif ($quotation->appointment_status === 'approved')
                                                                <span class="badge-soft green">Approved</span>
                                                            @elseif ($quotation->appointment_status === 'rescheduled')
                                                                <span class="badge-soft blue">Rescheduled</span>
                                                            @elseif ($quotation->appointment_status === 'cancelled')
                                                                <span class="badge-soft gray">Cancelled</span>
                                                            @else
                                                                <span class="badge-soft gray">{{ ucfirst(str_replace('_', ' ', $quotation->appointment_status ?? 'pending')) }}</span>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </div>

                                            <div>
                                                <div class="detail-label">Appointment Schedule</div>
                                                <div class="detail-value">
                                                    @if ($quotation->appointment_date && $quotation->appointment_time)
                                                        {{ optional($quotation->appointment_date)->format('Y-m-d') }} • {{ date('h:i A', strtotime($quotation->appointment_time)) }}
                                                    @else
                                                        Not yet scheduled
                                                    @endif
                                                </div>
                                            </div>

                                            <div>
                                                <div class="detail-label">Service Flow</div>
                                                <div class="detail-value text-uppercase">{{ str_replace('_', ' ', $flow) }}</div>
                                            </div>

                                            <div>
                                                <div class="detail-label">Flow Source</div>
                                                <div class="detail-value text-uppercase">{{ $quotation->flow_source ?? 'system' }}</div>
                                            </div>
                                        </div>

                                        @if ($flow === 'direct_service')
                                            <div class="flow-alert direct">
                                                This request is classified as <strong>Direct Service</strong>. The preferred date should be treated as the preferred actual service date.
                                            </div>
                                        @else
                                            <div class="flow-alert inspect">
                                                This request is classified as <strong>Inspection Required</strong>. The preferred date should be treated as the preferred inspection/site visit date first.
                                            </div>
                                        @endif

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

                                <div>
                                    <div class="assign-card mb-3">
                                        <div class="detail-title">Service Flow Override</div>

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

                                            <button class="btn btn-primary w-100">
                                                <i class="fas fa-random me-2"></i>Update Flow
                                            </button>
                                        </form>
                                    </div>

                                    <div class="assign-card">
                                        <div class="detail-title">
                                                {{ $flow === 'direct_service' ? 'Assign Personnel' : 'Assign Inspector' }}
                                        </div>

                                        <div class="assign-card mb-3">
                                                <div class="detail-title">Job Order</div>

                                                @if ($quotation->jobOrder)
                                                    <div class="notes-box mb-3">
                                                        <strong>{{ $quotation->jobOrder->job_order_no }}</strong><br>
                                                        Status: {{ ucfirst(str_replace('_', ' ', $quotation->jobOrder->status)) }}<br>
                                                        @if ($quotation->jobOrder->scheduled_date)
                                                            Schedule: {{ optional($quotation->jobOrder->scheduled_date)->format('Y-m-d') }}
                                                            @if ($quotation->jobOrder->scheduled_time)
                                                                • {{ $quotation->jobOrder->scheduled_time }}
                                                            @endif
                                                        @endif
                                                    </div>

                                                    <a href="{{ route('admin.job-orders.show', $quotation->jobOrder) }}" class="btn btn-outline-primary w-100">
                                                        <i class="fas fa-eye me-2"></i>View Job Order
                                                    </a>
                                                        @else
                                                            @if ($quotation->worker_id && ($quotation->appointment_date || $quotation->preferred_date))
                                                                <div class="notes-box mb-3">
                                                                    This request is ready for service execution setup.
                                                                </div>

                                                                <a href="{{ route('admin.job-orders.create', $quotation) }}" class="btn btn-primary w-100">
                                                                    <i class="fas fa-plus-circle me-2"></i>Create Job Order
                                                                </a>
                                                            @else
                                                                <div class="notes-box mb-3">
                                                                    Assign personnel/inspector and provide a service/appointment schedule first before creating a job order.
                                                                </div>

                                                                <button class="btn btn-outline-secondary w-100" disabled>
                                                                    <i class="fas fa-ban me-2"></i>Not Ready Yet
                                                                </button>
                                                            @endif
                                                        @endif
                                            </div>

                                        <form method="POST" action="{{ route('admin.quotations.assign-worker', $quotation) }}">
                                            @csrf

                                            <div class="mb-2">
                                                <select
                                                    name="worker_id"
                                                    class="form-select"
                                                    required
                                                    @disabled(!$hasAvailableWorkers)
                                                >
                                                    <option value="">
                                                        {{ $hasAvailableWorkers ? 'Select inspector' : 'No available inspectors' }}
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
                                                        Showing inspectors available on {{ optional($quotation->preferred_date)->format('Y-m-d') }}.
                                                    @else
                                                        No inspectors marked available on {{ optional($quotation->preferred_date)->format('Y-m-d') }}.
                                                    @endif
                                                @else
                                                    Preferred date not set. Showing all active inspectors.
                                                @endif
                                            </div>

                                            @if ($flow === 'direct_service')
                                                <div class="alert alert-success py-2 px-3 small">
                                                    This request may proceed directly to service scheduling and worker assignment.
                                                </div>
                                            @else
                                                <div class="alert alert-info py-2 px-3 small">
                                                    This request should go through inspection before quotation and execution.
                                                </div>
                                            @endif

                                            <div class="mb-3">
                                                <textarea
                                                    name="admin_notes"
                                                    class="form-control"
                                                    rows="3"
                                                    placeholder="Add assignment notes..."
                                                >{{ old('admin_notes', $quotation->admin_notes) }}</textarea>
                                            </div>

                                            <button class="btn btn-primary w-100" @disabled(!$hasAvailableWorkers)>
                                                <i class="fas fa-user-check me-2"></i>Save Assignment
                                            </button>
                                        </form>
                                    </div>

                                    <div class="assign-card mt-3">
                                        <div class="detail-title">
                                            {{ $flow === 'direct_service' ? 'Schedule Service' : 'Schedule Appointment' }}
                                        </div>

                                        @if ($flow === 'direct_service')
                                        <div class="alert alert-success py-2 px-3 small">
                                            This request is marked as <strong>Direct Service</strong>. The client’s preferred date/time should be treated as the preferred actual service schedule.
                                        </div>
                                    @endif

                                        @if ($quotation->client_action_status === 'pending')
                                            <div class="assign-card mt-3">
                                                <div class="detail-title">Client Request Review</div>

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

                                                    <button class="btn btn-primary w-100">Submit Review</button>
                                                </form>
                                            </div>
                                        @endif

                                        <form method="POST" action="{{ route('admin.quotations.update-appointment', $quotation) }}">
                                            @csrf

                                            <div class="mb-2">
                                                <label class="form-label">Appointment Status</label>
                                                <select name="appointment_status" class="form-select" required>
                                                    <option value="pending" @selected($quotation->appointment_status === 'pending')>Pending</option>
                                                    <option value="approved" @selected($quotation->appointment_status === 'approved')>Approved</option>
                                                    <option value="rescheduled" @selected($quotation->appointment_status === 'rescheduled')>Rescheduled</option>
                                                    <option value="cancelled" @selected($quotation->appointment_status === 'cancelled')>Cancelled</option>
                                                </select>
                                            </div>
                                            @error('appointment_status')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror

                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <label class="form-label">
                                                        {{ $flow === 'direct_service' ? 'Service Date' : 'Appointment Date' }}
                                                    </label>
                                                    <input
                                                        type="date"
                                                        name="appointment_date"
                                                        class="form-control"
                                                        value="{{ old('appointment_date', optional($quotation->appointment_date)->format('Y-m-d')) }}"
                                                    >
                                                </div>
                                                @error('appointment_date')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror

                                                <div class="col-md-6">
                                                    <label class="form-label">
                                                        {{ $flow === 'direct_service' ? 'Service Time' : 'Appointment Time' }}
                                                    </label>
                                                    <input
                                                        type="time"
                                                        name="appointment_time"
                                                        class="form-control"
                                                        value="{{ old('appointment_time', $quotation->appointment_time) }}"
                                                    >
                                                </div>
                                                @error('appointment_time')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>

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
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror

                                            <button class="btn btn-outline-primary w-100 mt-3">
                                                <i class="fas fa-calendar-check me-2"></i>Save Appointment
                                            </button>
                                        </form>
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
            <div class="text-center py-5">
                <div class="d-flex flex-column align-items-center justify-content-center text-muted">
                    <div class="mb-3" style="width:64px;height:64px;border-radius:18px;background:#eef6ff;color:#1d9bf0;display:flex;align-items:center;justify-content:center;font-size:24px;">
                        <i class="fas fa-file-circle-xmark"></i>
                    </div>
                    <div class="fw-bold text-dark mb-1">No quotation requests found</div>
                    <div class="text-muted">Incoming service quotation requests will appear here.</div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection