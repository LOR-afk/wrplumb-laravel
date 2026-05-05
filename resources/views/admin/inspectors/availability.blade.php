@extends('admin.layouts.app')

@section('title', 'Inspector Availability - WRPlumb')
@section('topbar_title', 'Inspector Availability')
@section('topbar_subtitle', 'Check inspector availability using a compact calendar view.')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/inspectors.css') }}">
@endpush
@section('content')


<div class="availability-page">
    <div class="page-header">
        <h1>Inspector Availability</h1>
        <p>View inspector schedules in a compact card and calendar-based layout.</p>
    </div>

    <div class="availability-toolbar">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Month</label>
                <input
                    type="month"
                    name="month"
                    class="form-control"
                    value="{{ $calendarMonth }}"
                >
            </div>

            <div class="col-md-4">
                <label class="form-label">Status Focus</label>
                <select name="status" class="form-select">
                    <option value="">All statuses</option>
                    <option value="available" @selected(request('status') === 'available')>Available</option>
                    <option value="on_duty" @selected(request('status') === 'on_duty')>On Duty</option>
                    <option value="off_duty" @selected(request('status') === 'off_duty')>Off Duty</option>
                    <option value="on_leave" @selected(request('status') === 'on_leave')>On Leave</option>
                </select>
            </div>

            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary flex-fill">
                    <i class="fas fa-filter me-2"></i>Apply Filter
                </button>
                <a href="{{ route('admin.inspectors.availability') }}" class="btn btn-outline-secondary">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="availability-stats">
        <div class="availability-stat">
            <div class="availability-stat-icon blue">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="availability-stat-label">Inspectors</div>
            <div class="availability-stat-value">{{ $inspectorGroups->count() }}</div>
        </div>

        <div class="availability-stat">
            <div class="availability-stat-icon green">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="availability-stat-label">Available Days</div>
            <div class="availability-stat-value">{{ $availabilities->where('status', 'available')->count() }}</div>
        </div>

        <div class="availability-stat">
            <div class="availability-stat-icon orange">
                <i class="fas fa-briefcase"></i>
            </div>
            <div class="availability-stat-label">On Duty</div>
            <div class="availability-stat-value">{{ $availabilities->where('status', 'on_duty')->count() }}</div>
        </div>

        <div class="availability-stat">
            <div class="availability-stat-icon red">
                <i class="fas fa-calendar-xmark"></i>
            </div>
            <div class="availability-stat-label">Off / Leave</div>
            <div class="availability-stat-value">
                {{ $availabilities->whereIn('status', ['off_duty', 'on_leave'])->count() }}
            </div>
        </div>
    </div>

    @if ($inspectorGroups->count())
        <div class="availability-layout">
            <div class="inspector-list-card">
                <div class="section-card-header">
                    <div>
                        <h5 class="section-card-title">Inspectors</h5>
                        <p class="section-card-subtitle">Click an inspector to view calendar records.</p>
                    </div>
                </div>

                <div class="inspector-list-body" id="inspectorList">
                    @foreach ($inspectorGroups as $index => $inspector)
                        <button
                            type="button"
                            class="inspector-card {{ $index === 0 ? 'active' : '' }}"
                            data-inspector-id="{{ $inspector['id'] }}"
                        >
                            <div class="inspector-card-top">
                                <div class="inspector-avatar">
                                    {{ strtoupper(substr($inspector['name'], 0, 1)) }}
                                </div>

                                <div class="flex-grow-1">
                                    <div class="inspector-name">{{ $inspector['name'] }}</div>
                                    <div class="inspector-meta">
                                        {{ $inspector['email'] ?? 'No email listed' }}<br>
                                        Latest record: {{ $inspector['latest_date'] ?? '—' }}
                                    </div>
                                </div>
                            </div>

                            <div class="mini-stats">
                                <div class="mini-stat">
                                    <div class="mini-stat-label">Available</div>
                                    <div class="mini-stat-value">{{ $inspector['available_count'] }}</div>
                                </div>
                                <div class="mini-stat">
                                    <div class="mini-stat-label">On Duty</div>
                                    <div class="mini-stat-value">{{ $inspector['on_duty_count'] }}</div>
                                </div>
                                <div class="mini-stat">
                                    <div class="mini-stat-label">Records</div>
                                    <div class="mini-stat-value">{{ $inspector['record_count'] }}</div>
                                </div>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="calendar-card">
                <div class="section-card-header">
                    <div>
                        <h5 class="section-card-title">Availability Calendar</h5>
                        <p class="section-card-subtitle">Green means available, red means unavailable, gray means no record.</p>
                    </div>
                </div>

                <div class="calendar-body">
                    <div class="calendar-profile">
                        <div>
                            <div class="profile-name" id="selectedInspectorName">Inspector</div>
                            <div class="profile-sub" id="selectedInspectorMeta">Select an inspector to view records.</div>
                        </div>
                        <div class="profile-badge" id="selectedInspectorBadge">
                            0 records
                        </div>
                    </div>

                    <div class="calendar-top">
                        <div class="calendar-month-title" id="calendarMonthTitle">
                            {{ $monthStart->format('F Y') }}
                        </div>

                        <div class="calendar-legend">
                            <span class="legend-item"><span class="legend-dot available"></span>Available</span>
                            <span class="legend-item"><span class="legend-dot on_duty"></span>On Duty / Assigned</span>
                            <span class="legend-item"><span class="legend-dot off_duty"></span>Off Duty</span>
                            <span class="legend-item"><span class="legend-dot on_leave"></span>On Leave</span>
                            <span class="legend-item"><span class="legend-dot empty"></span>No Record</span>
                        </div>
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

                    <div class="calendar-grid" id="availabilityCalendar"></div>

                    <div class="selected-details" id="selectedDetails">
                        <div class="selected-details-title">Selected Date Details</div>

                        <div class="selected-detail-grid">
                            <div class="selected-detail-item">
                                <div class="selected-detail-label">Date</div>
                                <div class="selected-detail-value" id="detailDate">—</div>
                            </div>

                            <div class="selected-detail-item">
                                <div class="selected-detail-label">Status</div>
                                <div class="selected-detail-value" id="detailStatus">—</div>
                            </div>

                            <div class="selected-detail-item">
                                <div class="selected-detail-label">Time</div>
                                <div class="selected-detail-value" id="detailTime">—</div>
                            </div>

                            <div class="selected-detail-item">
                                <div class="selected-detail-label">Notes</div>
                                <div class="selected-detail-value" id="detailNotes">—</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <details class="raw-card">
            <summary>
                <span><i class="fas fa-table me-2 text-primary"></i>View Raw Availability Records</span>
                <i class="fas fa-chevron-down"></i>
            </summary>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Inspector</th>
                            <th>Date</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($availabilities as $availability)
                            <tr>
                                <td>{{ $availability->inspector->name }}</td>
                                <td>{{ $availability->availability_date->format('Y-m-d') }}</td>
                                <td>{{ $availability->start_time ?? '—' }}</td>
                                <td>{{ $availability->end_time ?? '—' }}</td>
                                <td>
                                    <span class="badge-soft {{ $availability->status === 'available' ? 'green' : ($availability->status === 'on_duty' ? 'blue' : ($availability->status === 'off_duty' ? 'red' : 'orange')) }}">
                                        {{ ucfirst(str_replace('_', ' ', $availability->status)) }}
                                    </span>
                                </td>
                                <td>{{ $availability->notes ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>
    @else
        <div class="calendar-card">
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-calendar-xmark"></i>
                </div>
                <div class="fw-bold text-dark mb-1">No availability records found</div>
                <div>No inspector availability records match the selected month or status filter.</div>
            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const inspectors = @json($inspectorGroups->values());
    const calendarMonth = @json($calendarMonth);
    const monthStart = @json($monthStart->format('Y-m-d'));
    const monthEnd = @json($monthEnd->format('Y-m-d'));

    const calendar = document.getElementById('availabilityCalendar');
    const inspectorButtons = document.querySelectorAll('.inspector-card');
    const selectedInspectorName = document.getElementById('selectedInspectorName');
    const selectedInspectorMeta = document.getElementById('selectedInspectorMeta');
    const selectedInspectorBadge = document.getElementById('selectedInspectorBadge');
    const detailDate = document.getElementById('detailDate');
    const detailStatus = document.getElementById('detailStatus');
    const detailTime = document.getElementById('detailTime');
    const detailNotes = document.getElementById('detailNotes');

    if (!calendar || inspectors.length === 0) return;

    let selectedInspector = inspectors[0];
    let selectedDate = null;

    function prettyStatus(status) {
        if (!status) return 'No Record';
        return status.replace(/_/g, ' ').replace(/\b\w/g, char => char.toUpperCase());
    }

    function formatTime(value) {
        if (!value) return '—';
        return value;
    }

    function formatDate(dateKey) {
        const d = new Date(dateKey + 'T00:00:00');
        return d.toLocaleDateString('en-US', {
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        });
    }

    function buildRecordMap(records) {
        const map = {};
        records.forEach(record => {
            map[record.date] = record;
        });
        return map;
    }

    function formatTimeRange(record) {
        if (!record) return 'No schedule';

        if (!record.start_time && !record.end_time) {
            return 'Time not specified';
        }

        return `${formatTime(record.start_time)} - ${formatTime(record.end_time)}`;
    }

    function setSelectedDetails(dateKey, record) {
        detailDate.textContent = formatDate(dateKey);
        detailStatus.textContent = record ? prettyStatus(record.status) : 'No Record';
        detailTime.textContent = formatTimeRange(record);
        detailNotes.textContent = record
            ? (record.notes || 'No notes provided.')
            : 'No availability record for this date.';
    }

    function renderProfile(inspector) {
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

        const firstRecord = inspector.records && inspector.records.length
            ? inspector.records[0]
            : null;

        selectedDate = selectedDate || (firstRecord ? firstRecord.date : monthStart);

        for (let i = 0; i < startWeekday; i++) {
            const blank = document.createElement('div');
            blank.className = 'calendar-day empty';
            calendar.appendChild(blank);
        }

        for (let day = 1; day <= totalDays; day++) {
            const dateObj = new Date(year, month, day);
            const dateKey = `${dateObj.getFullYear()}-${String(dateObj.getMonth() + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const record = records[dateKey];

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'calendar-day';

            if (record) {
                btn.classList.add('has-record');
            }

            const status = record?.status || 'no_record';
            const label = prettyStatus(record?.status);

            if (record) {
                btn.classList.add(`day-${status}`);
            }

            if (selectedDate === dateKey) {
                btn.classList.add('selected');
            }

            btn.innerHTML = `
                <div class="calendar-date">${day}</div>
                <span class="calendar-status-pill ${status}">
                    ${label}
                </span>
                <span class="calendar-time">
                    ${formatTimeRange(record)}
                </span>
            `;

            btn.addEventListener('click', function () {
                document.querySelectorAll('.calendar-day.selected').forEach(el => {
                    el.classList.remove('selected');
                });

                selectedDate = dateKey;
                btn.classList.add('selected');
                setSelectedDetails(dateKey, record);
            });

            calendar.appendChild(btn);
        }

        setSelectedDetails(selectedDate, records[selectedDate]);
    }

    inspectorButtons.forEach(button => {
        button.addEventListener('click', function () {
            inspectorButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');

            const inspectorId = Number(button.dataset.inspectorId);
            selectedInspector = inspectors.find(item => Number(item.id) === inspectorId) || inspectors[0];
            selectedDate = null;
            renderCalendar(selectedInspector);
        });
    });

    renderCalendar(selectedInspector);
});
</script>
@endsection