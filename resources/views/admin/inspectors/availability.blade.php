@extends('admin.layouts.app')

@section('title', 'Inspector Availability - WRPlumb')
@section('topbar_title', 'Inspector Availability')
@section('topbar_subtitle', 'Check inspector schedules and manage availability.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/inspectors.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/inspector-availability.css') }}">
@endpush

@section('content')
@php
    $totalInspectors = $inspectorGroups->count();
    $availableDays = $availabilities->where('status', 'available')->count();
    $onDutyDays = $availabilities->where('status', 'on_duty')->count();
    $offLeaveDays = $availabilities->whereIn('status', ['off_duty', 'on_leave'])->count();
    $monthLabel = $monthStart->format('F Y');
@endphp

<div class="ia-page">
    <div class="ia-top-grid">
        <form method="GET" action="{{ route('admin.inspectors.availability') }}" class="ia-filter-card">
            <div class="ia-filter-item">
                <label class="form-label">Month</label>
                <div class="ia-input-icon">
                    <i class="fas fa-calendar-day"></i>
                    <input type="month" name="month" class="form-control" value="{{ $calendarMonth }}">
                </div>
            </div>

            <div class="ia-filter-item">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All statuses</option>
                    <option value="available" @selected(request('status') === 'available')>Available</option>
                    <option value="on_duty" @selected(request('status') === 'on_duty')>On Duty / Assigned</option>
                    <option value="off_duty" @selected(request('status') === 'off_duty')>Off Duty</option>
                    <option value="on_leave" @selected(request('status') === 'on_leave')>On Leave</option>
                </select>
            </div>

            <div class="ia-filter-actions">
                <button class="btn btn-primary"><i class="fas fa-filter me-1"></i> Filter</button>
                <a href="{{ route('admin.inspectors.availability') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>

        <div class="ia-stats-row">
            <div class="ia-mini-stat">
                <div class="ia-mini-icon blue"><i class="fas fa-user"></i></div>
                <span>Inspectors</span>
                <strong>{{ $totalInspectors }}</strong>
            </div>
            <div class="ia-mini-stat">
                <div class="ia-mini-icon green"><i class="fas fa-calendar-check"></i></div>
                <span>Available Days</span>
                <strong>{{ $availableDays }}</strong>
            </div>
            <div class="ia-mini-stat">
                <div class="ia-mini-icon orange"><i class="fas fa-briefcase"></i></div>
                <span>On Duty</span>
                <strong>{{ $onDutyDays }}</strong>
            </div>
            <div class="ia-mini-stat">
                <div class="ia-mini-icon red"><i class="fas fa-calendar-xmark"></i></div>
                <span>Off / Leave</span>
                <strong>{{ $offLeaveDays }}</strong>
            </div>
        </div>
    </div>

    @if ($inspectorGroups->count())
        <div class="ia-workspace">
            <aside class="ia-panel ia-inspector-panel">
                <div class="ia-panel-head compact">
                    <div>
                        <h5>Inspectors</h5>
                        <p>{{ $totalInspectors }} inspector{{ $totalInspectors === 1 ? '' : 's' }}</p>
                    </div>
                </div>

                <div class="ia-search-wrap">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="text" id="inspectorSearch" class="form-control" placeholder="Search inspectors...">
                </div>

                <div class="ia-inspector-list" id="inspectorList">
                    @foreach ($inspectorGroups as $index => $inspector)
                        <button type="button"
                                class="ia-inspector-card {{ $index === 0 ? 'active' : '' }}"
                                data-inspector-id="{{ $inspector['id'] }}"
                                data-inspector-name="{{ strtolower($inspector['name']) }}"
                                data-inspector-email="{{ strtolower($inspector['email'] ?? '') }}">
                            <span class="ia-inspector-active-dot" aria-hidden="true"></span>
                            <div class="ia-inspector-main">
                                <div class="ia-avatar">{{ strtoupper(substr($inspector['name'], 0, 1)) }}</div>
                                <div class="ia-inspector-copy">
                                    <strong>{{ $inspector['name'] }}</strong>
                                    <span>{{ $inspector['email'] ?? 'No email listed' }}</span>
                                    <small>Latest record: {{ $inspector['latest_date'] ?? '—' }}</small>
                                </div>
                            </div>

                            <div class="ia-inspector-counts">
                                <div><strong>{{ $inspector['available_count'] }}</strong><span>Available</span></div>
                                <div><strong>{{ $inspector['on_duty_count'] }}</strong><span>On Duty</span></div>
                                <div><strong>{{ $inspector['off_leave_count'] }}</strong><span>Off / Leave</span></div>
                            </div>
                        </button>
                    @endforeach
                </div>
            </aside>

            <main class="ia-panel ia-calendar-panel">
                <div class="ia-calendar-profile">
                    <div class="ia-avatar lg" id="selectedInspectorInitial">I</div>
                    <div>
                        <h4 id="selectedInspectorName">Inspector</h4>
                        <p id="selectedInspectorMeta">Select an inspector to view records.</p>
                    </div>
                    <span class="ia-record-pill" id="selectedInspectorBadge">0 records this month</span>
                </div>

                <div class="ia-calendar-topline">
                    <div class="ia-month-nav">
                        <a class="ia-icon-btn" href="{{ route('admin.inspectors.availability', ['month' => $monthStart->copy()->subMonth()->format('Y-m'), 'status' => request('status')]) }}" title="Previous month">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <h3>{{ $monthLabel }}</h3>
                        <a class="ia-icon-btn" href="{{ route('admin.inspectors.availability', ['month' => $monthStart->copy()->addMonth()->format('Y-m'), 'status' => request('status')]) }}" title="Next month">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a class="btn btn-sm btn-outline-primary ia-today-btn" href="{{ route('admin.inspectors.availability', ['month' => now()->format('Y-m')]) }}">Today</a>
                    </div>

                    <div class="ia-legend">
                        <span><i class="available"></i>Available</span>
                        <span><i class="on_duty"></i>On Duty / Assigned</span>
                        <span><i class="off_duty"></i>Off Duty</span>
                        <span><i class="on_leave"></i>On Leave</span>
                        <span><i class="no_record"></i>No Record</span>
                    </div>
                </div>

                <div class="ia-weekdays">
                    <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
                </div>

                <div class="ia-calendar-grid" id="availabilityCalendar"></div>
            </main>

            <aside class="ia-panel ia-day-panel">
                <div class="ia-panel-head ia-day-head">
                    <div>
                        <h5 id="detailDateTitle">Day Details</h5>
                        <p id="detailDateSub">Select a day from the calendar.</p>
                    </div>
                    <button type="button" class="ia-close-details" id="clearSelectedDate" title="Clear selection">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>

                <form id="availabilityDayForm" class="ia-day-form" method="POST" action="{{ route('admin.inspectors.availability.day') }}">
                    @csrf
                    <input type="hidden" name="inspector_id" id="dayInspectorId">
                    <input type="hidden" name="availability_date" id="dayAvailabilityDate">

                    <div class="ia-status-preview" id="statusPreview">
                        <span class="ia-status-dot no_record"></span>
                        <strong>No Record</strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="dayStatus" class="form-select" required>
                            <option value="available">Available</option>
                            <option value="on_duty">On Duty / Assigned</option>
                            <option value="off_duty">Off Duty</option>
                            <option value="on_leave">On Leave</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Start Time</label>
                        <div class="ia-input-icon right">
                            <input type="time" name="start_time" id="dayStartTime" class="form-control">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">End Time</label>
                        <div class="ia-input-icon right">
                            <input type="time" name="end_time" id="dayEndTime" class="form-control">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" id="dayNotes" class="form-control" rows="4" placeholder="Add notes for this day..."></textarea>
                    </div>

                    <div class="ia-last-updated">
                        <i class="fas fa-clock-rotate-left"></i>
                        <span id="dayLastUpdated">No saved record yet.</span>
                    </div>

                    <div class="ia-day-actions">
                        <button type="button" class="btn btn-outline-secondary" id="resetDayForm">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                    </div>
                </form>

                <div class="ia-help-box">
                    <i class="fas fa-circle-info"></i>
                    <span>Select any date on the calendar to view or edit availability details.</span>
                </div>
            </aside>
        </div>
    @else
        <div class="ia-panel ia-empty-state">
            <div class="ia-empty-icon"><i class="fas fa-calendar-xmark"></i></div>
            <strong>No availability records found</strong>
            <p>No inspector availability records match the selected month or status filter.</p>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const inspectors = @json($inspectorGroups->values());
    const monthStart = @json($monthStart->format('Y-m-d'));
    const monthEnd = @json($monthEnd->format('Y-m-d'));
    const saveUrl = @json(route('admin.inspectors.availability.day'));

    const calendar = document.getElementById('availabilityCalendar');
    const inspectorButtons = document.querySelectorAll('.ia-inspector-card');
    const inspectorSearch = document.getElementById('inspectorSearch');
    const selectedInspectorInitial = document.getElementById('selectedInspectorInitial');
    const selectedInspectorName = document.getElementById('selectedInspectorName');
    const selectedInspectorMeta = document.getElementById('selectedInspectorMeta');
    const selectedInspectorBadge = document.getElementById('selectedInspectorBadge');

    const detailDateTitle = document.getElementById('detailDateTitle');
    const detailDateSub = document.getElementById('detailDateSub');
    const statusPreview = document.getElementById('statusPreview');
    const dayForm = document.getElementById('availabilityDayForm');
    const dayInspectorId = document.getElementById('dayInspectorId');
    const dayAvailabilityDate = document.getElementById('dayAvailabilityDate');
    const dayStatus = document.getElementById('dayStatus');
    const dayStartTime = document.getElementById('dayStartTime');
    const dayEndTime = document.getElementById('dayEndTime');
    const dayNotes = document.getElementById('dayNotes');
    const dayLastUpdated = document.getElementById('dayLastUpdated');
    const resetDayForm = document.getElementById('resetDayForm');
    const clearSelectedDate = document.getElementById('clearSelectedDate');

    if (!calendar || !inspectors.length) return;

    let selectedInspector = inspectors[0];
    let selectedDate = null;
    let selectedRecord = null;

    function prettyStatus(status) {
        if (!status || status === 'no_record') return 'No Record';
        return status.replace(/_/g, ' ').replace(/\b\w/g, char => char.toUpperCase());
    }

    function formatDate(dateKey) {
        const d = new Date(dateKey + 'T00:00:00');
        return d.toLocaleDateString('en-US', {
            weekday: 'long',
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        });
    }

    function shortDate(dateKey) {
        const d = new Date(dateKey + 'T00:00:00');
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function timeDisplay(record) {
        if (!record) return 'No schedule';
        if (!record.start_time_display && !record.end_time_display) return 'Time not specified';
        if (record.start_time_display && record.end_time_display) return `${record.start_time_display} - ${record.end_time_display}`;
        return record.start_time_display || record.end_time_display || 'Time not specified';
    }

    function buildRecordMap(records) {
        const map = {};
        (records || []).forEach(record => {
            if (record.date) map[record.date] = record;
        });
        return map;
    }

    function setStatusPreview(status) {
        const key = status || 'no_record';
        statusPreview.className = `ia-status-preview ${key}`;
        statusPreview.innerHTML = `<span class="ia-status-dot ${key}"></span><strong>${prettyStatus(key)}</strong>`;
    }

    function updateDetails(dateKey, record) {
        selectedDate = dateKey;
        selectedRecord = record || null;

        const status = record?.status || 'no_record';
        detailDateTitle.textContent = formatDate(dateKey);
        detailDateSub.textContent = record ? timeDisplay(record) : 'No availability record yet.';
        setStatusPreview(status);

        dayInspectorId.value = selectedInspector.id;
        dayAvailabilityDate.value = dateKey;
        dayStatus.value = record?.status || 'available';
        dayStartTime.value = record?.start_time || '';
        dayEndTime.value = record?.end_time || '';
        dayNotes.value = record?.notes || '';
        dayLastUpdated.textContent = record?.updated_at ? `Last updated: ${record.updated_at}` : 'No saved record yet.';
    }

    function renderProfile(inspector) {
        selectedInspectorInitial.textContent = (inspector.name || 'I').substring(0, 1).toUpperCase();
        selectedInspectorName.textContent = inspector.name;
        selectedInspectorMeta.textContent = `${inspector.email || 'No email listed'} • Latest record: ${inspector.latest_date || '—'}`;
        selectedInspectorBadge.textContent = `${inspector.record_count} record${inspector.record_count === 1 ? '' : 's'} this month`;
    }

    function renderCalendar(inspector) {
        calendar.innerHTML = '';
        renderProfile(inspector);

        const records = buildRecordMap(inspector.records || []);
        const start = new Date(monthStart + 'T00:00:00');
        const end = new Date(monthEnd + 'T00:00:00');
        const startWeekday = start.getDay();
        const totalDays = end.getDate();
        const year = start.getFullYear();
        const month = start.getMonth();

        selectedDate = selectedDate || (inspector.records?.[0]?.date || monthStart);

        for (let i = 0; i < startWeekday; i++) {
            const blank = document.createElement('div');
            blank.className = 'ia-day empty';
            calendar.appendChild(blank);
        }

        for (let day = 1; day <= totalDays; day++) {
            const dateObj = new Date(year, month, day);
            const dateKey = `${dateObj.getFullYear()}-${String(dateObj.getMonth() + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const record = records[dateKey];
            const status = record?.status || 'no_record';

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `ia-day day-${status}`;
            if (selectedDate === dateKey) btn.classList.add('selected');
            btn.dataset.date = dateKey;

            btn.innerHTML = `
                <span class="ia-day-number">${day}</span>
                <span class="ia-day-status ${status}">${prettyStatus(status)}</span>
                <span class="ia-day-time">${timeDisplay(record)}</span>
            `;

            btn.addEventListener('click', function () {
                document.querySelectorAll('.ia-day.selected').forEach(item => item.classList.remove('selected'));
                btn.classList.add('selected');
                updateDetails(dateKey, record);
            });

            calendar.appendChild(btn);
        }

        updateDetails(selectedDate, records[selectedDate]);
    }

    function replaceRecord(record) {
        const records = selectedInspector.records || [];
        const index = records.findIndex(item => item.date === record.date);

        if (index >= 0) {
            records[index] = record;
        } else {
            records.push(record);
        }

        selectedInspector.records = records.sort((a, b) => String(a.date).localeCompare(String(b.date)));
        selectedInspector.record_count = selectedInspector.records.length;
        selectedInspector.available_count = selectedInspector.records.filter(item => item.status === 'available').length;
        selectedInspector.on_duty_count = selectedInspector.records.filter(item => item.status === 'on_duty').length;
        selectedInspector.off_duty_count = selectedInspector.records.filter(item => item.status === 'off_duty').length;
        selectedInspector.on_leave_count = selectedInspector.records.filter(item => item.status === 'on_leave').length;
        selectedInspector.off_leave_count = selectedInspector.off_duty_count + selectedInspector.on_leave_count;
        selectedInspector.latest_date = selectedInspector.records[selectedInspector.records.length - 1]?.date || selectedInspector.latest_date;
    }

    function showToast(message) {
        let stack = document.getElementById('iaToastStack');
        if (!stack) {
            stack = document.createElement('div');
            stack.id = 'iaToastStack';
            stack.className = 'ia-toast-stack';
            document.body.appendChild(stack);
        }

        const toast = document.createElement('div');
        toast.className = 'ia-toast';
        toast.innerHTML = `<i class="fas fa-check-circle"></i><div><strong>Success</strong><span>${message}</span></div>`;
        stack.appendChild(toast);
        setTimeout(() => toast.remove(), 3500);
    }

    inspectorButtons.forEach(button => {
        button.addEventListener('click', function () {
            inspectorButtons.forEach(item => item.classList.remove('active'));
            button.classList.add('active');
            selectedInspector = inspectors.find(item => Number(item.id) === Number(button.dataset.inspectorId)) || inspectors[0];
            selectedDate = null;
            renderCalendar(selectedInspector);
        });
    });

    if (inspectorSearch) {
        inspectorSearch.addEventListener('input', function () {
            const needle = inspectorSearch.value.trim().toLowerCase();
            inspectorButtons.forEach(button => {
                const haystack = `${button.dataset.inspectorName || ''} ${button.dataset.inspectorEmail || ''}`;
                button.classList.toggle('d-none', needle && !haystack.includes(needle));
            });
        });
    }

    if (resetDayForm) {
        resetDayForm.addEventListener('click', function () {
            updateDetails(selectedDate, selectedRecord);
        });
    }

    if (clearSelectedDate) {
        clearSelectedDate.addEventListener('click', function () {
            document.querySelectorAll('.ia-day.selected').forEach(item => item.classList.remove('selected'));
        });
    }

    if (dayForm) {
        dayForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            const submitButton = dayForm.querySelector('button[type="submit"]');
            const originalHtml = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

            try {
                const response = await fetch(saveUrl, {
                    method: 'POST',
                    body: new FormData(dayForm),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Unable to save availability.');
                }

                replaceRecord(data.record);
                selectedDate = data.record.date;
                renderCalendar(selectedInspector);
                showToast(data.message || 'Availability saved successfully.');
            } catch (error) {
                alert(error.message || 'Something went wrong. Please try again.');
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = originalHtml;
            }
        });
    }

    renderCalendar(selectedInspector);
});
</script>
@endpush
