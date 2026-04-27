@extends('client.layouts.app')

@section('title', 'Request Details - WRPlumb')
@section('topbar_title', 'Request Details')
@section('topbar_subtitle', 'View the current status and service details of your request.')

@section('content')
<style>
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 18px;
    }

    .detail-label {
        font-size: 0.76rem;
        font-weight: 800;
        text-transform: uppercase;
        color: #6b7280;
        margin-bottom: 4px;
    }

    .detail-value {
        font-weight: 700;
    }

    .notes-box {
        background: #f8fbff;
        border: 1px solid #edf2f7;
        border-radius: 14px;
        padding: 12px 14px;
        color: #334155;
        font-size: 0.94rem;
        line-height: 1.5;
    }

    .reschedule-calendar-wrap {
        border: 1px solid var(--wr-border);
        border-radius: 18px;
        background: #fff;
        padding: 16px;
    }

    .calendar-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }

    .calendar-toolbar button {
        border: 1px solid var(--wr-border);
        background: #fff;
        border-radius: 12px;
        padding: 8px 12px;
        font-weight: 700;
    }

    .calendar-month-label {
        font-weight: 800;
        color: #0f172a;
    }

    .calendar-weekdays,
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 8px;
    }

    .calendar-weekday {
        text-align: center;
        font-size: 0.78rem;
        font-weight: 800;
        color: #6b7280;
        padding: 6px 0;
    }

    .calendar-day {
        min-height: 52px;
        border-radius: 14px;
        border: 1px solid #e5edf5;
        background: #fff;
        font-weight: 700;
        cursor: pointer;
        position: relative;
    }

    .calendar-day.empty {
        visibility: hidden;
    }

    .calendar-day.past {
        background: #f8fafc;
        color: #94a3b8;
        cursor: not-allowed;
    }

    .calendar-day.available {
        background: #ecfdf3;
        border-color: #bbf7d0;
        color: #166534;
    }

    .calendar-day.unavailable {
        background: #fef2f2;
        border-color: #fecaca;
        color: #b91c1c;
        cursor: not-allowed;
    }

    .calendar-day.selected {
        outline: 2px solid #1d9bf0;
        outline-offset: 2px;
    }

    .calendar-count {
        display: block;
        font-size: 0.68rem;
        font-weight: 600;
        margin-top: 2px;
    }

    .calendar-legend {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
        margin-top: 14px;
        font-size: 0.82rem;
        color: #475569;
    }

    .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        display: inline-block;
        margin-right: 6px;
    }

    .legend-green { background: #22c55e; }
    .legend-red { background: #ef4444; }
    .legend-gray { background: #94a3b8; }

    .selected-date-box {
        margin-top: 14px;
        background: #f8fbff;
        border: 1px solid #e6eef8;
        border-radius: 14px;
        padding: 12px 14px;
        font-size: 0.92rem;
        line-height: 1.5;
    }

    .request-action-card {
        background: #fff;
        border: 1px solid var(--wr-border);
        border-radius: 18px;
        padding: 18px;
        margin-top: 16px;
    }

    .request-action-title {
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 4px;
    }

    .request-action-subtitle {
        font-size: 0.9rem;
        color: #64748b;
        margin-bottom: 14px;
    }

    .current-appointment-box {
        background: #f8fbff;
        border: 1px solid #dbeafe;
        border-radius: 14px;
        padding: 12px 14px;
        margin-bottom: 14px;
        color: #1e293b;
        font-size: 0.92rem;
        line-height: 1.5;
    }

    .reschedule-submit-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .cancel-warning-box {
        background: #fff7ed;
        border: 1px solid #fed7aa;
        border-radius: 14px;
        padding: 12px 14px;
        color: #9a3412;
        font-size: 0.9rem;
        margin-bottom: 14px;
    }

    @media (max-width: 767.98px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }
    }

    .calendar-day.current-appointment {
        border: 2px solid #1d4ed8;
        background: #eef6ff;
        color: #1e3a8a;
        position: relative;
    }

    .calendar-day.current-appointment::after {
        content: 'Current';
        position: absolute;
        bottom: 4px;
        right: 6px;
        font-size: 0.6rem;
        font-weight: 700;
        color: #1d4ed8;
    }

    .calendar-day.current-appointment.selected {
        outline: 2px solid #1d9bf0;
        outline-offset: 2px;
    }

    .pending-request-banner {
        background: #fff7ed;
        border: 1px solid #fdba74;
        color: #9a3412;
        border-radius: 14px;
        padding: 12px 14px;
        font-size: 0.9rem;
        margin-bottom: 14px;
    }

    .calendar-toolbar button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .calendar-day.selected {
        outline: 3px solid #22c55e;
        outline-offset: 2px;
    }

    .calendar-day.current-appointment {
        border: 2px solid #2563eb;
        background: #eff6ff;
        color: #1d4ed8;
        position: relative;
    }

    .calendar-day.current-appointment.has-badge::after {
        color: #1d4ed8;
    }

    .calendar-day.pending-requested {
        border: 2px solid #f59e0b !important;
        background: #fff7ed !important;
        color: #b45309 !important;
        position: relative;
    }

    .calendar-day.pending-requested .calendar-count {
        color: #b45309 !important;
    }

    .calendar-day.pending-requested.has-badge::after {
        color: #b45309 !important;
    }
</style>

@php
    $hasJobOrder = (bool) $quotation->jobOrder;
    $jobOrderStatus = $quotation->jobOrder->status ?? null;

    $primaryStatus = $hasJobOrder ? $quotation->jobOrder->status : $quotation->status;
    $primaryScheduleDate = $hasJobOrder ? $quotation->jobOrder->scheduled_date : $quotation->appointment_date;
    $primaryScheduleTime = $hasJobOrder ? $quotation->jobOrder->scheduled_time : $quotation->appointment_time;

    $canRequestScheduleChange = $hasJobOrder
        ? $jobOrderStatus === 'scheduled'
        : in_array($quotation->appointment_status, ['approved', 'rescheduled']) && $quotation->status !== 'completed';

    $canRequestCancellation = $hasJobOrder
        ? $jobOrderStatus === 'scheduled' && $quotation->client_action_status !== 'pending'
        : $quotation->status !== 'completed' && $quotation->client_action_status !== 'pending';

    $showExecutionLockedNotice = $hasJobOrder && in_array($jobOrderStatus, ['in_progress', 'completed', 'cancelled']);
@endphp

<div class="page-header">
    <h1>Request Details</h1>
    <p>Check your request status, assigned personnel/inspector, and service notes.</p>
</div>

<div class="panel mb-4">
    <div class="panel-header">
        <h5><i class="fas fa-timeline me-2 text-primary"></i>Request Timeline</h5>
    </div>
    <div class="panel-body">
        @include('partials.request-timeline', ['quotation' => $quotation])
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h5><i class="fas fa-circle-info me-2 text-primary"></i>Service Request Information</h5>
    </div>

    <div class="panel-body">
        <div class="detail-grid">
            <div>
                <div class="detail-label">Service Type</div>
                <div class="detail-value">{{ $quotation->service_type }}</div>
            </div>

            <div>
                <div class="detail-label">Category</div>
                <div class="detail-value">{{ ucfirst($quotation->service_category) }}</div>
            </div>

            <div>
                <div class="detail-label">Preferred Date</div>
                <div class="detail-value">{{ optional($quotation->preferred_date)->format('Y-m-d') ?? '—' }}</div>
            </div>

            <div>
                <div class="detail-label">{{ $hasJobOrder ? 'Job Order Status' : 'Status' }}</div>
                <div class="detail-value">
                    @if ($hasJobOrder)
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
                <div class="detail-label">
                    {{ ($quotation->service_flow ?? null) === 'direct_service' ? 'Assigned Personnel' : 'Assigned Inspector' }}
                </div>
                <div class="detail-value">{{ $quotation->worker?->name ?? 'Not assigned yet' }}</div>
            </div>

            <div>
                <div class="detail-label">Assigned At</div>
                <div class="detail-value">{{ optional($quotation->assigned_at)->format('Y-m-d h:i A') ?? '—' }}</div>
            </div>

            <div>
                <div class="detail-label">Address</div>
                <div class="detail-value">{{ $quotation->address }}</div>
            </div>

            <div>
                <div class="detail-label">Project Type</div>
                <div class="detail-value">{{ $quotation->project_type ?? '—' }}</div>
            </div>

            @if ($hasJobOrder)
                <div>
                    <div class="detail-label">Job Order No.</div>
                    <div class="detail-value">{{ $quotation->jobOrder->job_order_no }}</div>
                </div>

                <div>
                    <div class="detail-label">Service Flow</div>
                    <div class="detail-value text-uppercase">{{ str_replace('_', ' ', $quotation->jobOrder->service_flow ?? $quotation->service_flow ?? '—') }}</div>
                </div>
            @endif
        </div>

        <div class="mb-3">
            <div class="detail-label mb-2">Problem Details</div>
            <div class="notes-box">{{ $quotation->details }}</div>
        </div>

        <div class="mb-4">
            <div class="detail-label mb-2">{{ $hasJobOrder ? 'Job Order Information' : 'Appointment Information' }}</div>

            <div class="detail-grid">
                <div>
                    <div class="detail-label">{{ $hasJobOrder ? '' : 'Appointment Status' }}</div>
                    @if (!$hasJobOrder)
    <div>
        <div class="detail-label">Appointment Status</div>
        <div class="detail-value">
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
        </div>
    </div>
@endif
                <div>
                    <div class="detail-label">{{ $hasJobOrder ? 'Job Order Date' : 'Appointment Date' }}</div>
                    <div class="detail-value">
                        {{ optional($primaryScheduleDate)->format('Y-m-d') ?? 'Not yet scheduled' }}
                    </div>
                </div>

                <div>
                    <div class="detail-label">{{ $hasJobOrder ? 'Job Order Time' : 'Appointment Time' }}</div>
                    <div class="detail-value">
                        {{ $primaryScheduleTime ? date('h:i A', strtotime($primaryScheduleTime)) : 'Not yet scheduled' }}
                    </div>
                </div>

                <div>
                    <div class="detail-label">Preferred Time</div>
                    <div class="detail-value">
                        {{ $quotation->preferred_time ? date('h:i A', strtotime($quotation->preferred_time)) : 'Not specified' }}
                    </div>
                </div>
            </div>

            @if (!$hasJobOrder && $quotation->appointment_status === 'cancelled' && $quotation->cancel_reason)
                <div class="mt-3">
                    <div class="detail-label mb-2">Cancellation Reason</div>
                    <div class="notes-box">{{ $quotation->cancel_reason }}</div>
                </div>
            @endif
        </div>

        <div class="mb-3">
            <div class="detail-label mb-2">Admin Notes</div>
            <div class="notes-box">{{ $quotation->admin_notes ?? 'No admin notes yet.' }}</div>
        </div>

        <div class="mb-3">
            <div class="detail-label mb-2">Inspector Notes</div>
            <div class="notes-box">{{ $quotation->inspector_notes ?? 'No inspector notes yet.' }}</div>
        </div>
                @if ($showExecutionLockedNotice)
            <div class="request-action-card">
                <div class="request-action-title">Service Execution Notice</div>
                <div class="request-action-subtitle">
                    @if ($jobOrderStatus === 'in_progress')
                        This service is already in progress. Schedule change and cancellation requests are no longer available from this page.
                    @elseif ($jobOrderStatus === 'completed')
                        This service has already been completed. No further schedule change or cancellation request is available.
                    @elseif ($jobOrderStatus === 'cancelled')
                        This job order has already been cancelled. No further schedule change or cancellation request is available.
                    @endif
                </div>

                <div class="notes-box">
                    For urgent concerns, please contact support directly.
                </div>
            </div>
        @endif

        @if ($quotation->client_action_status === 'pending')
            <div class="request-action-card">
                <div class="request-action-title">Pending Client Request</div>
                <div class="request-action-subtitle">Your request is waiting for admin review.</div>

                <div class="notes-box">
                    <strong>Type:</strong> {{ ucfirst($quotation->client_action_request) }}<br>
                    @if ($quotation->client_requested_date)
                        <strong>Requested Date:</strong> {{ optional($quotation->client_requested_date)->format('Y-m-d') }}<br>
                    @endif
                    @if ($quotation->client_requested_time)
                        <strong>Requested Time:</strong> {{ date('h:i A', strtotime($quotation->client_requested_time)) }}<br>
                    @endif
                    <strong>Reason:</strong> {{ $quotation->client_request_reason }}
                </div>
            </div>
        @endif

@if ($canRequestScheduleChange)
    <div class="request-action-card">
        <div class="request-action-title">
            {{ $hasJobOrder ? 'Request Schedule Change' : 'Request Reschedule' }}
        </div>

        <div class="request-action-subtitle">
            @if ($hasJobOrder)
                Request a change to the scheduled service date/time. This still requires admin review.
            @else
                Choose a date with available inspectors, then enter your preferred time and reason.
            @endif
        </div>

        @if ($quotation->client_action_status === 'pending')
            <div class="pending-request-banner">
                You already have a pending schedule change request under admin review. You may still browse the calendar, but you cannot submit another request yet.
            </div>
        @endif

        <div class="current-appointment-box">
            <strong>Current {{ $hasJobOrder ? 'Service Schedule' : 'Appointment' }}:</strong>
            @if ($primaryScheduleDate && $primaryScheduleTime)
                {{ optional($primaryScheduleDate)->format('M d, Y') }} • {{ date('h:i A', strtotime($primaryScheduleTime)) }}
            @else
                Not yet scheduled
            @endif
        </div>

                <form method="POST" action="{{ route('client.requests.request-reschedule', $quotation) }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Requested Date</label>

                        <div class="reschedule-calendar-wrap" id="rescheduleCalendarWrap">
                            <div class="calendar-toolbar">
                                <button type="button" id="calendarPrevBtn">&larr;</button>
                                <div class="calendar-month-label" id="calendarMonthLabel">Month</div>
                                <button type="button" id="calendarNextBtn">&rarr;</button>
                            </div>

                            <div class="calendar-weekdays">
                                <div class="calendar-weekday">Sun</div>
                                <div class="calendar-weekday">Mon</div>
                                <div class="calendar-weekday">Tue</div>
                                <div class="calendar-weekday">Wed</div>
                                <div class="calendar-weekday">Thu</div>
                                <div class="calendar-weekday">Fri</div>
                                <div class="calendar-weekday">Sat</div>
                            </div>

                            <div
                                class="calendar-grid"
                                id="rescheduleCalendar"
                                data-endpoint="{{ route('client.requests.calendar-availability') }}">
                            </div>

                            <div class="calendar-legend">
                                <div><span class="legend-dot legend-green"></span>Available</div>
                                <div><span class="legend-dot legend-red"></span>No inspector</div>
                                <div><span class="legend-dot legend-gray"></span>Past date</div>
                                <div><span class="legend-dot" style="background:#2563eb;"></span>Current appointment</div>
                                <div><span class="legend-dot" style="background:#f59e0b;"></span>Pending requested date</div>
                                <div><span class="legend-dot" style="background:#22c55e;"></span>Selected date</div>
                            </div>

                            <div class="selected-date-box" id="selectedAvailabilityText">
                                Select a green date to continue.
                            </div>
                        </div>

                        <input
                            type="hidden"
                            name="client_requested_date"
                            id="client_requested_date"
                            value="{{ old('client_requested_date') }}"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Requested Time</label>
                        <input
                            type="time"
                            name="client_requested_time"
                            class="form-control"
                            value="{{ old('client_requested_time') }}"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea
                            name="client_request_reason"
                            class="form-control"
                            rows="3"
                            required
                        >{{ old('client_request_reason') }}</textarea>
                    </div>

                    @error('client_reschedule')
                        <small class="text-danger d-block mb-2">{{ $message }}</small>
                    @enderror

                    @error('client_requested_date')
                        <small class="text-danger d-block mb-2">{{ $message }}</small>
                    @enderror

                    @error('client_requested_time')
                        <small class="text-danger d-block mb-2">{{ $message }}</small>
                    @enderror

                    @error('client_request_reason')
                        <small class="text-danger d-block mb-2">{{ $message }}</small>
                    @enderror

                    <button type="submit" class="btn btn-outline-primary w-100 reschedule-submit-btn" id="rescheduleSubmitBtn" disabled>
                        Send Reschedule Request
                    </button>
                </form>
            </div>
        @endif

@if ($canRequestScheduleChange)
    <div class="request-action-card">
        <div class="request-action-title">
            {{ $hasJobOrder ? 'Request Schedule Change' : 'Request Reschedule' }}
        </div>

        <div class="request-action-subtitle">
            @if ($hasJobOrder)
                Request a change to the scheduled service date/time. This still requires admin review.
            @else
                Choose a date with available inspectors, then enter your preferred time and reason.
            @endif
        </div>

        @if ($quotation->client_action_status === 'pending')
            <div class="pending-request-banner">
                You already have a pending schedule change request under admin review. You may still browse the calendar, but you cannot submit another request yet.
            </div>
        @endif

        <div class="current-appointment-box">
            <strong>Current {{ $hasJobOrder ? 'Service Schedule' : 'Appointment' }}:</strong>
            @if ($primaryScheduleDate && $primaryScheduleTime)
                {{ optional($primaryScheduleDate)->format('M d, Y') }} • {{ date('h:i A', strtotime($primaryScheduleTime)) }}
            @else
                Not yet scheduled
            @endif
        </div>

        <form method="POST" action="{{ route('client.requests.request-reschedule', $quotation) }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Requested Date</label>

                <div class="reschedule-calendar-wrap" id="rescheduleCalendarWrap">
                    <div class="calendar-toolbar">
                        <button type="button" id="calendarPrevBtn">&larr;</button>
                        <div class="calendar-month-label" id="calendarMonthLabel">Month</div>
                        <button type="button" id="calendarNextBtn">&rarr;</button>
                    </div>

                    <div class="calendar-weekdays">
                        <div class="calendar-weekday">Sun</div>
                        <div class="calendar-weekday">Mon</div>
                        <div class="calendar-weekday">Tue</div>
                        <div class="calendar-weekday">Wed</div>
                        <div class="calendar-weekday">Thu</div>
                        <div class="calendar-weekday">Fri</div>
                        <div class="calendar-weekday">Sat</div>
                    </div>

                    <div
                        class="calendar-grid"
                        id="rescheduleCalendar"
                        data-endpoint="{{ route('client.requests.calendar-availability') }}">
                    </div>

                    <div class="calendar-legend">
                        <div><span class="legend-dot legend-green"></span>Available</div>
                        <div><span class="legend-dot legend-red"></span>No inspector</div>
                        <div><span class="legend-dot legend-gray"></span>Past date</div>
                        <div><span class="legend-dot" style="background:#2563eb;"></span>Current appointment</div>
                        <div><span class="legend-dot" style="background:#f59e0b;"></span>Pending requested date</div>
                        <div><span class="legend-dot" style="background:#22c55e;"></span>Selected date</div>
                    </div>

                    <div class="selected-date-box" id="selectedAvailabilityText">
                        Select a green date to continue.
                    </div>
                </div>

                <input
                    type="hidden"
                    name="client_requested_date"
                    id="client_requested_date"
                    value="{{ old('client_requested_date') }}"
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Requested Time</label>
                <input
                    type="time"
                    name="client_requested_time"
                    class="form-control"
                    value="{{ old('client_requested_time') }}"
                    required
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Reason</label>
                <textarea
                    name="client_request_reason"
                    class="form-control"
                    rows="3"
                    required
                >{{ old('client_request_reason') }}</textarea>
            </div>

            @error('client_reschedule')
                <small class="text-danger d-block mb-2">{{ $message }}</small>
            @enderror

            @error('client_requested_date')
                <small class="text-danger d-block mb-2">{{ $message }}</small>
            @enderror

            @error('client_requested_time')
                <small class="text-danger d-block mb-2">{{ $message }}</small>
            @enderror

            @error('client_request_reason')
                <small class="text-danger d-block mb-2">{{ $message }}</small>
            @enderror

            <button type="submit" class="btn btn-outline-primary w-100 reschedule-submit-btn" id="rescheduleSubmitBtn" disabled>
                {{ $hasJobOrder ? 'Send Schedule Change Request' : 'Send Reschedule Request' }}
            </button>
        </form>
    </div>
@endif

@if ($canRequestCancellation)
    <div class="request-action-card">
        <div class="request-action-title">Request Cancellation</div>
        <div class="request-action-subtitle">
            @if ($hasJobOrder)
                Use this only if you really need to cancel the scheduled service. This still requires admin review.
            @else
                Use this only if you really need to cancel the appointment or service request.
            @endif
        </div>

        <div class="cancel-warning-box">
            Cancellation requests still require admin review before they take effect.
        </div>

        <form method="POST" action="{{ route('client.requests.request-cancel', $quotation) }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Reason</label>
                <textarea
                    name="client_request_reason"
                    class="form-control"
                    rows="4"
                    required
                >{{ old('client_request_reason') }}</textarea>
            </div>

            @error('client_cancel')
                <small class="text-danger d-block mb-2">{{ $message }}</small>
            @enderror

            <button class="btn btn-outline-danger w-100">Send Cancellation Request</button>
        </form>
    </div>
@endif

<div class="d-flex gap-2 flex-wrap">
    <a href="{{ route('client.requests.index') }}" class="btn btn-outline-secondary">
        Back to My Requests
    </a>

    @if ($quotation->jobOrder)
        <a href="{{ route('client.job-orders.show', $quotation->jobOrder) }}" class="btn btn-dark">
            View Job Order
        </a>
    @endif
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendar = document.getElementById('rescheduleCalendar');
    if (!calendar) return;

    const endpoint = calendar.dataset.endpoint;
    const monthLabel = document.getElementById('calendarMonthLabel');
    const prevBtn = document.getElementById('calendarPrevBtn');
    const nextBtn = document.getElementById('calendarNextBtn');
    const selectedText = document.getElementById('selectedAvailabilityText');
    const hiddenDateInput = document.getElementById('client_requested_date');
    const submitBtn = document.getElementById('rescheduleSubmitBtn');

    const today = new Date();

    const currentAppointmentDate = @json(
        $primaryScheduleDate ? date('Y-m-d', strtotime($primaryScheduleDate)) : null
    );

    const hasPendingRequest = @json($quotation->client_action_status === 'pending');

    const pendingRequestedDate = @json(
        $quotation->client_requested_date ? date('Y-m-d', strtotime($quotation->client_requested_date)) : null
    );

    const preferredCalendarDate = hasPendingRequest
        ? pendingRequestedDate
        : (currentAppointmentDate || null);

    let currentMonth;
    if (preferredCalendarDate) {
        const seedDate = new Date(preferredCalendarDate + 'T00:00:00');
        currentMonth = new Date(seedDate.getFullYear(), seedDate.getMonth(), 1);
    } else {
        currentMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    }

    const currentMonthFloor = new Date(today.getFullYear(), today.getMonth(), 1);
    let availabilityMap = {};
    let selectedDate = hiddenDateInput.value || '';

    function pad(value) {
        return String(value).padStart(2, '0');
    }

    function formatMonthKey(date) {
        return `${date.getFullYear()}-${pad(date.getMonth() + 1)}`;
    }

    function formatDateKey(date) {
        return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
    }

    function prettyDate(dateKey) {
        const dateObj = new Date(dateKey + 'T00:00:00');
        return dateObj.toLocaleDateString('en-US', {
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        });
    }

    function isSameMonth(dateKey, monthDate) {
        if (!dateKey) return false;
        const d = new Date(dateKey + 'T00:00:00');
        return d.getFullYear() === monthDate.getFullYear() && d.getMonth() === monthDate.getMonth();
    }

    function updateSummary(dateKey, count) {
        selectedText.innerHTML = `
            <strong>Selected Date:</strong> ${prettyDate(dateKey)}<br>
            <strong>Available Inspectors:</strong> ${count}<br>
            <strong>Status:</strong> ${hasPendingRequest ? 'You have a pending request under review, so a new submission is disabled.' : 'You may now enter your preferred time and submit.'}
        `;
    }

    function updateDefaultMessage(hasAvailableDays) {
        if (hasPendingRequest && pendingRequestedDate) {
            const pendingCount = Number(availabilityMap[pendingRequestedDate] || 0);

            selectedText.innerHTML = `
                <strong>Pending Requested Date:</strong> ${prettyDate(pendingRequestedDate)}<br>
                <strong>Available Inspectors on that date now:</strong> ${pendingCount}<br>
                <strong>Status:</strong> Waiting for admin review.
            `;
            return;
        }

        if (!hasAvailableDays) {
            selectedText.innerHTML = `
                <strong>No available inspectors this month.</strong><br>
                Try another month using the arrows above.
            `;
            return;
        }

        selectedText.textContent = 'Select a green date to continue.';
    }

    function clearSelectionIfInvalid() {
        if (!selectedDate) return;

        const count = Number(availabilityMap[selectedDate] || 0);
        if (!isSameMonth(selectedDate, currentMonth) || count <= 0) {
            selectedDate = '';
            hiddenDateInput.value = '';
            submitBtn.disabled = true;
        }
    }

    function updateNavButtons() {
        prevBtn.disabled =
            currentMonth.getFullYear() === currentMonthFloor.getFullYear() &&
            currentMonth.getMonth() === currentMonthFloor.getMonth();
    }

    function loadAvailability() {
        const monthKey = formatMonthKey(currentMonth);

        fetch(`${endpoint}?month=${monthKey}`)
            .then(response => response.json())
            .then(data => {
                availabilityMap = data.days || {};
                clearSelectionIfInvalid();
                renderCalendar();
            })
            .catch(() => {
                selectedText.textContent = 'Unable to load calendar availability.';
                submitBtn.disabled = true;
            });
    }

    function renderCalendar() {
        calendar.innerHTML = '';
        updateNavButtons();

        const year = currentMonth.getFullYear();
        const month = currentMonth.getMonth();

        monthLabel.textContent = currentMonth.toLocaleString('en-US', {
            month: 'long',
            year: 'numeric'
        });

        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const startWeekday = firstDay.getDay();
        const totalDays = lastDay.getDate();

        let hasAvailableDays = false;

        for (let i = 0; i < startWeekday; i++) {
            const empty = document.createElement('div');
            empty.className = 'calendar-day empty';
            calendar.appendChild(empty);
        }

        for (let day = 1; day <= totalDays; day++) {
            const dateObj = new Date(year, month, day);
            const dateKey = formatDateKey(dateObj);
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'calendar-day';
            btn.dataset.date = dateKey;

            const count = Number(availabilityMap[dateKey] || 0);
            const isPast = dateObj < new Date(today.getFullYear(), today.getMonth(), today.getDate());

            if (count > 0) hasAvailableDays = true;

            btn.innerHTML = `
                <div>${day}</div>
                <span class="calendar-count">${count > 0 ? count + ' insp.' : ''}</span>
            `;

            if (isPast) {
                btn.classList.add('past');
                btn.disabled = true;
            } else if (count > 0) {
                btn.classList.add('available');
            } else {
                btn.classList.add('unavailable');
                btn.disabled = true;
            }

            let titleParts = [];

            if (currentAppointmentDate && dateKey === currentAppointmentDate) {
                btn.classList.add('current-appointment', 'has-badge');
                btn.dataset.badge = 'Current';
                titleParts.push(@json($hasJobOrder ? 'Current job order schedule' : 'Current appointment'));            
            }

            if (hasPendingRequest && pendingRequestedDate && dateKey === pendingRequestedDate) {
                btn.classList.remove('available', 'unavailable', 'past');
                btn.classList.add('pending-requested', 'has-badge');
                btn.dataset.badge = 'Requested';
                btn.disabled = false;

                titleParts.push('Pending requested date');
            }

            if (selectedDate === dateKey && count > 0 && !isPast) {
                btn.classList.add('selected');
                updateSummary(dateKey, count);
            }

            if (titleParts.length) {
                btn.title = titleParts.join(' • ');
            }

            btn.addEventListener('click', function () {
                if (btn.disabled) return;

                document.querySelectorAll('.calendar-day.selected').forEach(el => {
                    el.classList.remove('selected');
                });

                btn.classList.add('selected');
                selectedDate = dateKey;
                hiddenDateInput.value = dateKey;
                updateSummary(dateKey, count);
                submitBtn.disabled = hasPendingRequest ? true : false;
            });

            calendar.appendChild(btn);
        }

        if (!selectedDate) {
            updateDefaultMessage(hasAvailableDays);
        }

        if (!selectedDate) {
            submitBtn.disabled = true;
        } else {
            submitBtn.disabled = hasPendingRequest ? true : false;
        }
    }

    prevBtn.addEventListener('click', function () {
        if (prevBtn.disabled) return;
        currentMonth = new Date(currentMonth.getFullYear(), currentMonth.getMonth() - 1, 1);
        loadAvailability();
    });

    nextBtn.addEventListener('click', function () {
        currentMonth = new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1, 1);
        loadAvailability();
    });

    submitBtn.disabled = true;
    loadAvailability();
});
</script>
@endsection