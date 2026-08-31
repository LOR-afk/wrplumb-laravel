@extends('client.layouts.app')

@section('title', 'Request Details - WRPlumb')
@section('topbar_title', 'Request Details')
@section('topbar_subtitle', 'View the current status and service details of your request.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/client/request-show.css') }}?v=20260818a">
@endpush

@section('content')
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

@php
    $requestStatusKey = strtolower((string) ($primaryStatus ?? 'pending'));

    $requestStatusText = $hasJobOrder
        ? match ($requestStatusKey) {
            'scheduled' => 'Job Scheduled',
            'in_progress' => 'Job In Progress',
            'completed' => 'Job Completed',
            'cancelled', 'canceled' => 'Job Cancelled',
            default => ucfirst(str_replace('_', ' ', $requestStatusKey)),
        }
        : match ($requestStatusKey) {
            'pending' => 'Pending Review',
            'assigned' => 'Assigned',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled', 'canceled' => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $requestStatusKey)),
        };

    $requestStatusClass = match ($requestStatusKey) {
        'pending' => 'orange',
        'approved', 'accepted', 'assigned', 'scheduled', 'rescheduled' => 'blue',
        'ongoing', 'in_progress', 'in-progress' => 'green',
        'completed', 'done' => 'green',
        'cancelled', 'canceled', 'rejected', 'declined' => 'gray',
        default => 'gray',
    };

    $assignedRole = ($quotation->service_flow ?? null) === 'direct_service'
        ? 'Personnel'
        : 'Inspector';
@endphp

<div class="request-show-page">
    <section class="request-show-hero">
        <div class="request-show-hero-copy">
            <span>Service Request</span>

            <div class="request-show-title-row">
                <h2>{{ $quotation->service_type }}</h2>
                <em class="request-show-status {{ $requestStatusClass }}">
                    {{ $requestStatusText }}
                </em>
            </div>

            <p>
                <i class="fas fa-location-dot"></i>
                {{ $quotation->address ?: 'No service address provided' }}
            </p>
        </div>

        <div class="request-show-actions">
            <a href="{{ route('client.requests.index') }}" class="request-show-btn secondary">
                <i class="fas fa-arrow-left"></i>
                My Requests
            </a>

            @if ($quotation->jobOrder)
                <a href="{{ route('client.job-orders.show', $quotation->jobOrder) }}" class="request-show-btn primary">
                    <i class="fas fa-clipboard-check"></i>
                    View Job Order
                </a>
            @endif
        </div>
    </section>

    <section class="request-show-summary">
        <article>
            <span>Category</span>
            <strong>{{ ucfirst($quotation->service_category) }}</strong>
        </article>

        <article>
            <span>Project Type</span>
            <strong>{{ $quotation->project_type ?? '—' }}</strong>
        </article>

        <article>
            <span>Preferred Date</span>
            <strong>{{ optional($quotation->preferred_date)->format('M d, Y') ?? '—' }}</strong>
        </article>

        <article>
            <span>Preferred Time</span>
            <strong>{{ $quotation->preferred_time ? date('h:i A', strtotime($quotation->preferred_time)) : 'Not specified' }}</strong>
        </article>

        <article>
            <span>Assigned {{ $assignedRole }}</span>
            <strong>{{ $quotation->worker?->name ?? 'Not assigned yet' }}</strong>
        </article>
    </section>

    <section class="request-show-card timeline-card">
        <div class="request-show-card-head">
            <span class="request-show-card-icon blue">
                <i class="fas fa-timeline"></i>
            </span>

            <div>
                <h3>Request Timeline</h3>
                <p>Follow your request from submission to service completion.</p>
            </div>
        </div>

        <div class="request-show-card-body timeline-body">
            @include('partials.request-timeline', ['quotation' => $quotation])
        </div>
    </section>

    <div class="request-show-grid">
        <section class="request-show-card">
            <div class="request-show-card-head">
                <span class="request-show-card-icon violet">
                    <i class="fas fa-circle-info"></i>
                </span>

                <div>
                    <h3>Request Information</h3>
                    <p>Service and assignment details for this request.</p>
                </div>
            </div>

            <div class="request-show-card-body">
                <div class="request-info-grid">
                    <div>
                        <span>Service Type</span>
                        <strong>{{ $quotation->service_type }}</strong>
                    </div>

                    <div>
                        <span>Service Category</span>
                        <strong>{{ ucfirst($quotation->service_category) }}</strong>
                    </div>

                    <div>
                        <span>Status</span>
                        <strong>{{ $requestStatusText }}</strong>
                    </div>

                    <div>
                        <span>Assigned {{ $assignedRole }}</span>
                        <strong>{{ $quotation->worker?->name ?? 'Not assigned yet' }}</strong>
                    </div>

                    <div>
                        <span>Assigned At</span>
                        <strong>{{ optional($quotation->assigned_at)->format('M d, Y · h:i A') ?? '—' }}</strong>
                    </div>

                    <div>
                        <span>Project Type</span>
                        <strong>{{ $quotation->project_type ?? '—' }}</strong>
                    </div>

                    @if ($hasJobOrder)
                        <div>
                            <span>Job Order No.</span>
                            <strong>{{ $quotation->jobOrder->job_order_no }}</strong>
                        </div>

                        <div>
                            <span>Service Flow</span>
                            <strong>{{ ucfirst(str_replace('_', ' ', $quotation->jobOrder->service_flow ?? $quotation->service_flow ?? '—')) }}</strong>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <section class="request-show-card">
            <div class="request-show-card-head">
                <span class="request-show-card-icon green">
                    <i class="fas fa-calendar-check"></i>
                </span>

                <div>
                    <h3>{{ $hasJobOrder ? 'Service Schedule' : 'Appointment' }}</h3>
                    <p>Current confirmed date and time for this request.</p>
                </div>
            </div>

            <div class="request-show-card-body">
                <div class="schedule-display">
                    <div>
                        <span>{{ $hasJobOrder ? 'Job Order Date' : 'Appointment Date' }}</span>
                        <strong>
                            {{ optional($primaryScheduleDate)->format('M d, Y') ?? 'Not yet scheduled' }}
                        </strong>
                    </div>

                    <div>
                        <span>{{ $hasJobOrder ? 'Job Order Time' : 'Appointment Time' }}</span>
                        <strong>
                            {{ $primaryScheduleTime ? date('h:i A', strtotime($primaryScheduleTime)) : 'Not yet scheduled' }}
                        </strong>
                    </div>
                </div>

                @if (!$hasJobOrder)
                    <div class="appointment-state">
                        <span>Appointment Status</span>

                        @php
                            $appointmentStatusKey = strtolower((string) ($quotation->appointment_status ?? 'pending'));
                        @endphp

                        <strong>
                            {{ ucfirst(str_replace('_', ' ', $appointmentStatusKey)) }}
                        </strong>
                    </div>
                @endif

                @if (!$hasJobOrder && $quotation->appointment_status === 'cancelled' && $quotation->cancel_reason)
                    <div class="request-note-box warning">
                        <span>Cancellation Reason</span>
                        <p>{{ $quotation->cancel_reason }}</p>
                    </div>
                @endif
            </div>
        </section>
    </div>

    <section class="request-show-card">
        <div class="request-show-card-head">
            <span class="request-show-card-icon orange">
                <i class="fas fa-message"></i>
            </span>

            <div>
                <h3>Service Notes</h3>
                <p>Problem description and updates from the WRPlumb team.</p>
            </div>
        </div>

        <div class="request-show-card-body">
            <div class="request-notes-grid">
                <article>
                    <span>Problem Details</span>
                    <p>{{ $quotation->details ?: 'No problem details provided.' }}</p>
                </article>

                <article>
                    <span>Admin Notes</span>
                    <p>{{ $quotation->admin_notes ?? 'No admin notes yet.' }}</p>
                </article>

                <article>
                    <span>Inspector Notes</span>
                    <p>{{ $quotation->inspector_notes ?? 'No inspector notes yet.' }}</p>
                </article>
            </div>
        </div>
    </section>

    @if ($showExecutionLockedNotice)
        <section class="request-status-notice locked">
            <span><i class="fas fa-lock"></i></span>
            <div>
                <strong>Service actions are locked</strong>

                <p>
                    @if ($jobOrderStatus === 'in_progress')
                        This service is already in progress. Schedule change and cancellation requests are no longer available.
                    @elseif ($jobOrderStatus === 'completed')
                        This service has already been completed. No further schedule change or cancellation request is available.
                    @elseif ($jobOrderStatus === 'cancelled')
                        This job order has already been cancelled. No further schedule change or cancellation request is available.
                    @endif
                </p>

                <small>For urgent concerns, please contact customer support.</small>
            </div>
        </section>
    @endif

    @if ($quotation->client_action_status === 'pending')
        <section class="request-status-notice pending">
            <span><i class="fas fa-clock"></i></span>

            <div>
                <strong>Request awaiting admin review</strong>

                <p>
                    {{ ucfirst(str_replace('_', ' ', $quotation->client_action_request ?? 'request')) }}
                    @if ($quotation->client_requested_date)
                        · {{ optional($quotation->client_requested_date)->format('M d, Y') }}
                    @endif
                    @if ($quotation->client_requested_time)
                        · {{ date('h:i A', strtotime($quotation->client_requested_time)) }}
                    @endif
                </p>

                @if ($quotation->client_request_reason)
                    <small>{{ $quotation->client_request_reason }}</small>
                @endif
            </div>
        </section>
    @endif

    @if ($canRequestScheduleChange)
        <section class="request-show-card request-action-section">
            <div class="request-show-card-head">
                <span class="request-show-card-icon blue">
                    <i class="fas fa-calendar-days"></i>
                </span>

                <div>
                    <h3>{{ $hasJobOrder ? 'Request Schedule Change' : 'Request Reschedule' }}</h3>
                    <p>
                        Choose an available date and submit your preferred time for admin review.
                    </p>
                </div>
            </div>

            <div class="request-show-card-body">
                @if ($quotation->client_action_status === 'pending')
                    <div class="pending-request-banner">
                        You already have a pending schedule change request. You may browse availability, but a second request cannot be submitted yet.
                    </div>
                @endif

                <div class="current-schedule-box">
                    <span>Current {{ $hasJobOrder ? 'Service Schedule' : 'Appointment' }}</span>
                    <strong>
                        @if ($primaryScheduleDate && $primaryScheduleTime)
                            {{ optional($primaryScheduleDate)->format('M d, Y') }}
                            ·
                            {{ date('h:i A', strtotime($primaryScheduleTime)) }}
                        @else
                            Not yet scheduled
                        @endif
                    </strong>
                </div>

                <form method="POST" action="{{ route('client.requests.request-reschedule', $quotation) }}">
                    @csrf

                    <div class="reschedule-calendar-wrap" id="rescheduleCalendarWrap">
                        <div class="calendar-toolbar">
                            <button type="button" id="calendarPrevBtn">
                                <i class="fas fa-chevron-left"></i>
                            </button>

                            <div class="calendar-month-label" id="calendarMonthLabel">Month</div>

                            <button type="button" id="calendarNextBtn">
                                <i class="fas fa-chevron-right"></i>
                            </button>
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
                            <div><span class="legend-dot legend-blue"></span>Current schedule</div>
                            <div><span class="legend-dot legend-orange"></span>Pending request</div>
                        </div>

                        <div class="selected-date-box" id="selectedAvailabilityText">
                            Select an available date to continue.
                        </div>
                    </div>

                    <input
                        type="hidden"
                        name="client_requested_date"
                        id="client_requested_date"
                        value="{{ old('client_requested_date') }}"
                        required
                    >

                    <div class="request-action-fields">
                        <div>
                            <label for="client_requested_time">Requested Time</label>
                            <input
                                type="time"
                                id="client_requested_time"
                                name="client_requested_time"
                                class="form-control"
                                value="{{ old('client_requested_time') }}"
                                required
                            >
                        </div>

                        <div>
                            <label for="client_request_reason">Reason</label>
                            <textarea
                                id="client_request_reason"
                                name="client_request_reason"
                                class="form-control"
                                rows="3"
                                placeholder="Briefly explain why you need to change the schedule."
                                required
                            >{{ old('client_request_reason') }}</textarea>
                        </div>
                    </div>

                    @error('client_reschedule')
                        <small class="text-danger d-block mt-2">{{ $message }}</small>
                    @enderror

                    @error('client_requested_date')
                        <small class="text-danger d-block mt-2">{{ $message }}</small>
                    @enderror

                    @error('client_requested_time')
                        <small class="text-danger d-block mt-2">{{ $message }}</small>
                    @enderror

                    @error('client_request_reason')
                        <small class="text-danger d-block mt-2">{{ $message }}</small>
                    @enderror

                    <button
                        type="submit"
                        class="request-action-submit"
                        id="rescheduleSubmitBtn"
                        disabled
                    >
                        <i class="fas fa-paper-plane"></i>
                        {{ $hasJobOrder ? 'Send Schedule Change Request' : 'Send Reschedule Request' }}
                    </button>
                </form>
            </div>
        </section>
    @endif

    @if ($canRequestCancellation)
        <section class="request-show-card request-action-section danger">
            <div class="request-show-card-head">
                <span class="request-show-card-icon red">
                    <i class="fas fa-ban"></i>
                </span>

                <div>
                    <h3>Request Cancellation</h3>
                    <p>Use this only when the scheduled service should no longer proceed.</p>
                </div>
            </div>

            <div class="request-show-card-body">
                <div class="cancel-warning-box">
                    <i class="fas fa-triangle-exclamation"></i>
                    Cancellation is not immediate. Your request will still require admin review.
                </div>

                <form method="POST" action="{{ route('client.requests.request-cancel', $quotation) }}">
                    @csrf

                    <label for="cancel_reason" class="request-action-label">Reason for cancellation</label>

                    <textarea
                        id="cancel_reason"
                        name="client_request_reason"
                        class="form-control"
                        rows="4"
                        placeholder="Please explain why you need to cancel this service."
                        required
                    >{{ old('client_request_reason') }}</textarea>

                    @error('client_cancel')
                        <small class="text-danger d-block mt-2">{{ $message }}</small>
                    @enderror

                    <button class="request-cancel-submit">
                        <i class="fas fa-ban"></i>
                        Send Cancellation Request
                    </button>
                </form>
            </div>
        </section>
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