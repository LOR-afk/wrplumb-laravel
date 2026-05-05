@extends('inspector.layouts.app')

@section('title', 'Inspector Availability - WRPlumb')
@section('topbar_title', 'Availability Schedule')
@section('topbar_subtitle', 'Set your available dates and duty schedule.')

@section('content')
<style>
    .availability-page {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .availability-hero {
        background: linear-gradient(135deg, #ffffff, #f8fbff);
        border: 1px solid var(--wr-border);
        border-radius: 22px;
        padding: 24px;
        box-shadow: var(--wr-shadow);
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
        flex-wrap: wrap;
    }

    .availability-hero h1 {
        margin: 0 0 8px;
        font-size: 2rem;
        font-weight: 900;
        color: #0f172a;
    }

    .availability-hero p {
        margin: 0;
        color: var(--wr-muted);
    }

    .month-toolbar {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .month-pill {
        border: 1px solid var(--wr-border);
        background: #fff;
        border-radius: 16px;
        padding: 10px 14px;
        font-weight: 900;
        color: #0f172a;
    }

    .availability-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .availability-summary-card {
        background: #ffffff;
        border: 1px solid var(--wr-border);
        border-radius: 20px;
        padding: 18px;
        box-shadow: var(--wr-shadow);
    }

    .availability-summary-label {
        color: var(--wr-muted);
        font-size: 0.82rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 8px;
    }

    .availability-summary-value {
        font-size: 1.85rem;
        font-weight: 900;
        color: #0f172a;
        line-height: 1;
    }

    .availability-workbench {
        display: grid;
        grid-template-columns: 0.9fr 1.55fr;
        gap: 18px;
        align-items: start;
    }

    .availability-card {
        background: #fff;
        border: 1px solid var(--wr-border);
        border-radius: 22px;
        box-shadow: var(--wr-shadow);
        overflow: hidden;
    }

    .availability-card-header {
        padding: 18px 20px;
        border-bottom: 1px solid var(--wr-border);
        background: #fbfdff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .availability-card-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 900;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .availability-card-body {
        padding: 20px;
    }

    .availability-help {
        background: #f0f9ff;
        color: #075985;
        border: 1px solid #bae6fd;
        border-radius: 16px;
        padding: 12px 14px;
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 16px;
    }

    .availability-calendar-wrap {
        padding: 16px;
    }

    .calendar-weekdays,
    .availability-calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 8px;
    }

    .calendar-weekday {
        text-align: center;
        color: var(--wr-muted);
        font-size: 0.78rem;
        font-weight: 900;
        text-transform: uppercase;
        padding: 8px 0;
    }

    .calendar-day {
        min-height: 92px;
        border: 1px solid #e5edf5;
        border-radius: 16px;
        background: #fff;
        padding: 10px;
        text-align: left;
        cursor: pointer;
        transition: all 0.18s ease;
    }

    .calendar-day-empty {
        visibility: hidden;
        pointer-events: none;
        box-shadow: none !important;
        border: 0 !important;
        background: transparent !important;
    }

    .calendar-day:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
    }

    .calendar-day.muted {
        opacity: 0.45;
        background: #f8fafc;
    }

    .calendar-day.selected {
        border-color: #1d9bf0;
        box-shadow: 0 0 0 3px rgba(29, 155, 240, 0.15);
    }

    .calendar-day.today {
        outline: 2px dashed rgba(29, 155, 240, 0.35);
        outline-offset: 2px;
    }

    .calendar-day-number {
        font-weight: 900;
        color: #0f172a;
        margin-bottom: 8px;
    }

    .status-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 6px 9px;
        font-size: 0.72rem;
        font-weight: 900;
        line-height: 1;
        margin-bottom: 8px;
    }

    .status-chip.available { background: #dcfce7; color: #166534; }
    .status-chip.on-duty { background: #dbeafe; color: #1d4ed8; }
    .status-chip.off-duty { background: #ffedd5; color: #c2410c; }
    .status-chip.on-leave { background: #f3e8ff; color: #7e22ce; }
    .status-chip.no-record { background: #eef2f7; color: #475569; }

    .calendar-day-time {
        font-size: 0.78rem;
        color: #64748b;
        line-height: 1.4;
    }

    .calendar-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 14px;
        color: #64748b;
        font-size: 0.84rem;
    }

    .legend-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        display: inline-block;
    }

    .legend-dot.available { background: #22c55e; }
    .legend-dot.on-duty { background: #3b82f6; }
    .legend-dot.off-duty { background: #f97316; }
    .legend-dot.on-leave { background: #a855f7; }
    .legend-dot.no-record { background: #94a3b8; }

    .selected-detail-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-top: 14px;
    }

    .selected-detail-box {
        background: #f8fbff;
        border: 1px solid #e6eef8;
        border-radius: 16px;
        padding: 12px 14px;
    }

    .selected-detail-label {
        color: var(--wr-muted);
        text-transform: uppercase;
        font-size: 0.74rem;
        font-weight: 900;
        letter-spacing: 0.04em;
        margin-bottom: 4px;
    }

    .selected-detail-value {
        color: #0f172a;
        font-weight: 900;
        line-height: 1.4;
    }

    .recent-records-table .table tbody td,
    .recent-records-table .table thead th {
        vertical-align: middle;
    }

    @media (max-width: 1199.98px) {
        .availability-workbench,
        .availability-summary-grid,
        .selected-detail-grid {
            grid-template-columns: 1fr;
        }

        .calendar-day {
            min-height: 82px;
        }
    }

    @media (max-width: 767.98px) {
        .availability-calendar-grid,
        .calendar-weekdays {
            gap: 5px;
        }

        .calendar-day {
            min-height: 74px;
            padding: 8px;
        }

        .status-chip {
            font-size: 0.64rem;
            padding: 5px 7px;
        }

        .calendar-day-time {
            display: none;
        }
    }
</style>

<div class="availability-page">
    <div class="availability-hero">
        <div>
            <h1>Set Your Duty Schedule</h1>
            <p>Set your duty status so Admin can view when you are available for assignment.</p>
        </div>

        <form method="GET" class="month-toolbar">
            <a href="{{ route('inspector.availability.index', ['month' => $previousMonth]) }}" class="btn btn-outline-secondary">
                <i class="fas fa-chevron-left me-1"></i> Previous
            </a>

            <input type="month" name="month" class="form-control" value="{{ $selectedMonth }}" style="max-width: 180px;">

            <button class="btn btn-primary">
                <i class="fas fa-calendar-days me-1"></i> View Month
            </button>

            <a href="{{ route('inspector.availability.index', ['month' => $nextMonth]) }}" class="btn btn-outline-secondary">
                Next <i class="fas fa-chevron-right ms-1"></i>
            </a>
        </form>
    </div>

    <div class="availability-summary-grid">
        <div class="availability-summary-card">
            <div class="availability-summary-label">Available Days</div>
            <div class="availability-summary-value">{{ $monthlySummary['available'] }}</div>
        </div>
        <div class="availability-summary-card">
            <div class="availability-summary-label">On Duty / Assigned</div>
            <div class="availability-summary-value">{{ $monthlySummary['on_duty'] }}</div>
        </div>
        <div class="availability-summary-card">
            <div class="availability-summary-label">Off Duty</div>
            <div class="availability-summary-value">{{ $monthlySummary['off_duty'] }}</div>
        </div>
        <div class="availability-summary-card">
            <div class="availability-summary-label">On Leave</div>
            <div class="availability-summary-value">{{ $monthlySummary['on_leave'] }}</div>
        </div>
    </div>

    <div class="availability-workbench">
        <div class="availability-card">
            <div class="availability-card-header">
                <h5 class="availability-card-title">
                    <i class="fas fa-calendar-plus text-primary"></i> Set Availability
                </h5>
            </div>
            <div class="availability-card-body">
                <div class="availability-help">
                    <strong>Guide:</strong> Use <strong>Available</strong> when you are free for assignment. Use <strong>On Duty / Assigned</strong> when you are already on site, inspecting, or assigned to a task.
                </div>

                <form method="POST" action="{{ route('inspector.availability.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="availability_date" id="availabilityDateInput" class="form-control" value="{{ old('availability_date', now()->format('Y-m-d')) }}" required>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Start Time</label>
                            <input type="time" name="start_time" class="form-control" value="{{ old('start_time') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">End Time</label>
                            <input type="time" name="end_time" class="form-control" value="{{ old('end_time') }}">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="available" @selected(old('status') === 'available')>Available</option>
                            <option value="on_duty" @selected(old('status') === 'on_duty')>On Duty / Assigned</option>
                            <option value="off_duty" @selected(old('status') === 'off_duty')>Off Duty</option>
                            <option value="on_leave" @selected(old('status') === 'on_leave')>On Leave</option>
                        </select>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="4" placeholder="Example: Available for field work, assigned to inspection site, on leave, etc.">{{ old('notes') }}</textarea>
                    </div>

                    <button class="btn btn-primary w-100 mt-3">
                        <i class="fas fa-floppy-disk me-2"></i>Save Availability
                    </button>
                </form>
            </div>
        </div>

        <div class="availability-card">
            <div class="availability-card-header">
                <h5 class="availability-card-title">
                    <i class="fas fa-calendar-days text-primary"></i> {{ $monthStart->format('F Y') }} Calendar
                </h5>
                <span class="month-pill">{{ $monthlySummary['total'] }} saved record{{ $monthlySummary['total'] === 1 ? '' : 's' }}</span>
            </div>

            <div class="availability-calendar-wrap">
                <div class="calendar-weekdays">
                    <div class="calendar-weekday">Sun</div>
                    <div class="calendar-weekday">Mon</div>
                    <div class="calendar-weekday">Tue</div>
                    <div class="calendar-weekday">Wed</div>
                    <div class="calendar-weekday">Thu</div>
                    <div class="calendar-weekday">Fri</div>
                    <div class="calendar-weekday">Sat</div>
                </div>

                <div class="availability-calendar-grid" id="availabilityCalendarGrid">
                    @foreach ($calendarDays as $day)
                        @if (!is_array($day) || empty($day['date_key']))
                            <div class="calendar-day calendar-day-empty"></div>
                            @continue
                        @endif

                        @php
                            $record = $day['record'] ?? null;
                            $status = $record['status'] ?? 'no_record';
                            $statusClass = str_replace('_', '-', $status);
                            $statusLabel = $record['status_label'] ?? 'No Record';
                            $timeDisplay = $record['time_display'] ?? 'No schedule';
                            $notes = ($record && !empty($record['notes'])) ? $record['notes'] : 'No notes provided.';
                            $dateKey = $day['date_key'];
                            $dateLabel = \Carbon\Carbon::parse($dateKey)->format('M d, Y');
                        @endphp

                        <button
                            type="button"
                            class="calendar-day {{ ($day['is_current_month'] ?? false) ? '' : 'muted' }} {{ ($day['is_today'] ?? false) ? 'today' : '' }}"
                            data-date-key="{{ $dateKey }}"
                            data-date-label="{{ $dateLabel }}"
                            data-status="{{ $status }}"
                            data-status-label="{{ $statusLabel }}"
                            data-time="{{ $timeDisplay }}"
                            data-notes="{{ $record ? $notes : 'No availability record for this date.' }}"
                        >
                            <div class="calendar-day-number">{{ $day['day'] ?? '' }}</div>
                            <span class="status-chip {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                            <div class="calendar-day-time">{{ $timeDisplay }}</div>
                        </button>
                    @endforeach
                </div>

                <div class="calendar-legend">
                    <span class="legend-item"><span class="legend-dot available"></span> Available</span>
                    <span class="legend-item"><span class="legend-dot on-duty"></span> On Duty / Assigned</span>
                    <span class="legend-item"><span class="legend-dot off-duty"></span> Off Duty</span>
                    <span class="legend-item"><span class="legend-dot on-leave"></span> On Leave</span>
                    <span class="legend-item"><span class="legend-dot no-record"></span> No Record</span>
                </div>

                <div class="selected-detail-grid" id="selectedDateDetails">
                    <div class="selected-detail-box">
                        <div class="selected-detail-label">Date</div>
                        <div class="selected-detail-value" id="selectedDateLabel">Select a date</div>
                    </div>
                    <div class="selected-detail-box">
                        <div class="selected-detail-label">Status</div>
                        <div class="selected-detail-value" id="selectedStatusLabel">—</div>
                    </div>
                    <div class="selected-detail-box">
                        <div class="selected-detail-label">Time</div>
                        <div class="selected-detail-value" id="selectedTimeLabel">—</div>
                    </div>
                    <div class="selected-detail-box">
                        <div class="selected-detail-label">Notes</div>
                        <div class="selected-detail-value" id="selectedNotesLabel">Click a calendar day to view details.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="availability-card recent-records-table">
        <div class="availability-card-header">
            <h5 class="availability-card-title">
                <i class="fas fa-table-list text-primary"></i> Recent Availability Records
            </h5>
        </div>

        <div class="availability-card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Status</th>
                            <th>Notes</th>
                            <th width="120">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($availabilities as $availability)
                            <tr>
                                <td class="fw-bold">{{ $availability->availability_date->format('Y-m-d') }}</td>
                                <td>{{ $availability->start_time ?? '—' }}</td>
                                <td>{{ $availability->end_time ?? '—' }}</td>
                                <td>
                                    @if ($availability->status === 'available')
                                        <span class="badge-soft green">Available</span>
                                    @elseif ($availability->status === 'on_duty')
                                        <span class="badge-soft blue">On Duty / Assigned</span>
                                    @elseif ($availability->status === 'off_duty')
                                        <span class="badge-soft orange">Off Duty</span>
                                    @else
                                        <span class="badge-soft gray">On Leave</span>
                                    @endif
                                </td>
                                <td>{{ $availability->notes ?? '—' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('inspector.availability.destroy', $availability) }}" onsubmit="return confirm('Delete this availability?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center text-muted">
                                        <div class="mb-3" style="width:64px;height:64px;border-radius:18px;background:#eef6ff;color:#1d9bf0;display:flex;align-items:center;justify-content:center;font-size:24px;">
                                            <i class="fas fa-calendar-xmark"></i>
                                        </div>
                                        <div class="fw-bold text-dark mb-1">No availability records yet</div>
                                        <div class="text-muted">Your saved availability schedule will appear here.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $availabilities->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarDays = document.querySelectorAll('.calendar-day');
    const dateInput = document.getElementById('availabilityDateInput');
    const selectedDateLabel = document.getElementById('selectedDateLabel');
    const selectedStatusLabel = document.getElementById('selectedStatusLabel');
    const selectedTimeLabel = document.getElementById('selectedTimeLabel');
    const selectedNotesLabel = document.getElementById('selectedNotesLabel');

    function selectDay(dayButton) {
        calendarDays.forEach(day => day.classList.remove('selected'));
        dayButton.classList.add('selected');

        const dateKey = dayButton.dataset.dateKey;
        selectedDateLabel.textContent = dayButton.dataset.dateLabel || dateKey;
        selectedStatusLabel.textContent = dayButton.dataset.statusLabel || 'No Record';
        selectedTimeLabel.textContent = dayButton.dataset.time || 'No schedule';
        selectedNotesLabel.textContent = dayButton.dataset.notes || 'No availability record for this date.';

        if (dateInput && dateKey) {
            dateInput.value = dateKey;
        }
    }

    calendarDays.forEach(dayButton => {
        dayButton.addEventListener('click', function () {
            selectDay(dayButton);
        });
    });

    const todayButton = document.querySelector('.calendar-day.today') || document.querySelector('.calendar-day:not(.muted)');
    if (todayButton) {
        selectDay(todayButton);
    }
});
</script>
@endsection
