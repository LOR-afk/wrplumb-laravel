@extends('inspector.layouts.app')

@section('title', 'Inspector Availability - WRPlumb')
@section('topbar_title', 'Availability Schedule')
@section('topbar_subtitle', 'Set your available dates and duty schedule.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/inspector/availability.css') }}?v=availability-v2">
@endpush

@section('content')
<div class="availability-page">

    <section class="availability-control-bar">
        <div class="availability-control-copy">
            <span class="availability-kicker">Duty Planning</span>
            <h2>Manage your availability</h2>
            <p>Set your status so Admin can see when you are free, assigned, off duty, or on leave.</p>
        </div>

        <form method="GET" class="month-toolbar">
            <a
                href="{{ route('inspector.availability.index', ['month' => $previousMonth]) }}"
                class="month-nav-btn"
                title="Previous month"
            >
                <i class="fas fa-chevron-left"></i>
                <span>Previous</span>
            </a>

            <label class="month-picker">
                <i class="fas fa-calendar-days"></i>
                <input type="month" name="month" value="{{ $selectedMonth }}">
            </label>

            <button class="view-month-btn" type="submit">
                <i class="fas fa-calendar-check"></i>
                <span>View Month</span>
            </button>

            <a
                href="{{ route('inspector.availability.index', ['month' => $nextMonth]) }}"
                class="month-nav-btn"
                title="Next month"
            >
                <span>Next</span>
                <i class="fas fa-chevron-right"></i>
            </a>
        </form>
    </section>

    <section class="availability-summary-grid">
        <article class="availability-summary-item summary-green">
            <span class="summary-icon"><i class="fas fa-circle-check"></i></span>
            <div>
                <span class="availability-summary-label">Available</span>
                <strong>{{ $monthlySummary['available'] }}</strong>
                <small>days free for assignment</small>
            </div>
        </article>

        <article class="availability-summary-item summary-blue">
            <span class="summary-icon"><i class="fas fa-user-clock"></i></span>
            <div>
                <span class="availability-summary-label">On Duty</span>
                <strong>{{ $monthlySummary['on_duty'] }}</strong>
                <small>assigned or active days</small>
            </div>
        </article>

        <article class="availability-summary-item summary-orange">
            <span class="summary-icon"><i class="fas fa-moon"></i></span>
            <div>
                <span class="availability-summary-label">Off Duty</span>
                <strong>{{ $monthlySummary['off_duty'] }}</strong>
                <small>non-working days</small>
            </div>
        </article>

        <article class="availability-summary-item summary-violet">
            <span class="summary-icon"><i class="fas fa-plane-departure"></i></span>
            <div>
                <span class="availability-summary-label">On Leave</span>
                <strong>{{ $monthlySummary['on_leave'] }}</strong>
                <small>leave days recorded</small>
            </div>
        </article>
    </section>

    <section class="availability-workbench">
        <aside class="availability-editor-card">
            <header class="availability-panel-header">
                <div>
                    <span class="panel-icon"><i class="fas fa-calendar-plus"></i></span>
                    <div>
                        <h5>Set Availability</h5>
                        <p>Select a date and update your duty status.</p>
                    </div>
                </div>
            </header>

            <div class="availability-editor-body">
                <div class="availability-help">
                    <i class="fas fa-circle-info"></i>
                    <div>
                        <strong>Quick guide</strong>
                        <span>
                            Use <b>Available</b> when you are free for assignment and
                            <b>On Duty / Assigned</b> when you are already working on a task.
                        </span>
                    </div>
                </div>

                <form method="POST" action="{{ route('inspector.availability.store') }}">
                    @csrf

                    <div class="availability-field">
                        <label for="availabilityDateInput">Date</label>
                        <input
                            type="date"
                            name="availability_date"
                            id="availabilityDateInput"
                            class="form-control"
                            value="{{ old('availability_date', now()->format('Y-m-d')) }}"
                            required
                        >
                    </div>

                    <div class="availability-time-grid">
                        <div class="availability-field">
                            <label>Start Time</label>
                            <input
                                type="time"
                                name="start_time"
                                class="form-control"
                                value="{{ old('start_time') }}"
                            >
                        </div>

                        <div class="availability-field">
                            <label>End Time</label>
                            <input
                                type="time"
                                name="end_time"
                                class="form-control"
                                value="{{ old('end_time') }}"
                            >
                        </div>
                    </div>

                    <div class="availability-field">
                        <label>Status</label>
                        <select name="status" class="form-select" required>
                            <option value="available" @selected(old('status') === 'available')>
                                Available
                            </option>
                            <option value="on_duty" @selected(old('status') === 'on_duty')>
                                On Duty / Assigned
                            </option>
                            <option value="off_duty" @selected(old('status') === 'off_duty')>
                                Off Duty
                            </option>
                            <option value="on_leave" @selected(old('status') === 'on_leave')>
                                On Leave
                            </option>
                        </select>
                    </div>

                    <div class="availability-field">
                        <label>Notes <span>Optional</span></label>
                        <textarea
                            name="notes"
                            class="form-control"
                            rows="3"
                            placeholder="Add a short note about your schedule..."
                        >{{ old('notes') }}</textarea>
                    </div>

                    <button class="availability-save-btn" type="submit">
                        <i class="fas fa-floppy-disk"></i>
                        Save Availability
                    </button>
                </form>
            </div>
        </aside>

        <article class="availability-calendar-card">
            <header class="availability-panel-header calendar-header">
                <div>
                    <span class="panel-icon"><i class="fas fa-calendar-days"></i></span>
                    <div>
                        <h5>{{ $monthStart->format('F Y') }} Calendar</h5>
                        <p>Click a day to view details or load it into the form.</p>
                    </div>
                </div>

                <span class="saved-record-pill">
                    {{ $monthlySummary['total'] }}
                    saved record{{ $monthlySummary['total'] === 1 ? '' : 's' }}
                </span>
            </header>

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
                            $notes = ($record && !empty($record['notes']))
                                ? $record['notes']
                                : 'No notes provided.';
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
                            <div class="calendar-day-top">
                                <span class="calendar-day-number">{{ $day['day'] ?? '' }}</span>

                                @if ($day['is_today'] ?? false)
                                    <span class="today-dot" title="Today"></span>
                                @endif
                            </div>

                            <span class="status-chip {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>

                            <div class="calendar-day-time">{{ $timeDisplay }}</div>
                        </button>
                    @endforeach
                </div>

                <div class="calendar-meta-row">
                    <div class="calendar-legend">
                        <span class="legend-item"><span class="legend-dot available"></span>Available</span>
                        <span class="legend-item"><span class="legend-dot on-duty"></span>On Duty</span>
                        <span class="legend-item"><span class="legend-dot off-duty"></span>Off Duty</span>
                        <span class="legend-item"><span class="legend-dot on-leave"></span>On Leave</span>
                        <span class="legend-item"><span class="legend-dot no-record"></span>No Record</span>
                    </div>
                </div>

                <div class="selected-detail-grid" id="selectedDateDetails">
                    <div class="selected-detail-box">
                        <span class="selected-detail-label">Date</span>
                        <strong id="selectedDateLabel">Select a date</strong>
                    </div>

                    <div class="selected-detail-box">
                        <span class="selected-detail-label">Status</span>
                        <strong id="selectedStatusLabel">—</strong>
                    </div>

                    <div class="selected-detail-box">
                        <span class="selected-detail-label">Time</span>
                        <strong id="selectedTimeLabel">—</strong>
                    </div>

                    <div class="selected-detail-box notes-box">
                        <span class="selected-detail-label">Notes</span>
                        <strong id="selectedNotesLabel">Click a calendar day to view details.</strong>
                    </div>
                </div>
            </div>
        </article>
    </section>

    <section class="availability-records-card">
        <header class="availability-panel-header">
            <div>
                <span class="panel-icon"><i class="fas fa-clock-rotate-left"></i></span>
                <div>
                    <h5>Recent Availability Records</h5>
                    <p>Your latest saved availability entries.</p>
                </div>
            </div>
        </header>

        <div class="availability-records-body">
            <div class="table-responsive">
                <table class="table availability-table align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Status</th>
                            <th>Notes</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($availabilities as $availability)
                            <tr>
                                <td>
                                    <strong>{{ $availability->availability_date->format('M d, Y') }}</strong>
                                </td>

                                <td>{{ $availability->start_time ?? '—' }}</td>
                                <td>{{ $availability->end_time ?? '—' }}</td>

                                <td>
                                    @if ($availability->status === 'available')
                                        <span class="record-status green">Available</span>
                                    @elseif ($availability->status === 'on_duty')
                                        <span class="record-status blue">On Duty / Assigned</span>
                                    @elseif ($availability->status === 'off_duty')
                                        <span class="record-status orange">Off Duty</span>
                                    @else
                                        <span class="record-status violet">On Leave</span>
                                    @endif
                                </td>

                                <td class="notes-cell">{{ $availability->notes ?? '—' }}</td>

                                <td class="text-end">
                                    <form
                                        method="POST"
                                        action="{{ route('inspector.availability.destroy', $availability) }}"
                                        onsubmit="return confirm('Delete this availability?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button class="record-delete-btn" type="submit" title="Delete availability">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="availability-empty-state">
                                        <span><i class="fas fa-calendar-xmark"></i></span>
                                        <strong>No availability records yet</strong>
                                        <small>Your saved availability schedule will appear here.</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="availability-pagination">
                {{ $availabilities->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarDays = document.querySelectorAll('.calendar-day[data-date-key]');
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

    const todayButton =
        document.querySelector('.calendar-day.today[data-date-key]') ||
        document.querySelector('.calendar-day:not(.muted)[data-date-key]');

    if (todayButton) {
        selectDay(todayButton);
    }
});
</script>
@endpush
