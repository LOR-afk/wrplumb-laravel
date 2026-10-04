@extends('inspector.layouts.app')

@section('title', 'Inspector Dashboard - WRPlumb')
@section('topbar_title', 'Dashboard')
@section('topbar_subtitle', 'Monitor assigned requests, schedules, and field activity.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/inspector/dashboard.css') }}?v=inspector-dashboard-modern-01">
@endpush

@section('content')
<div class="inspector-dashboard">
    <section class="dashboard-greeting">
        <div class="dashboard-greeting-copy">
            <span class="greeting-label">Good day,</span>
            <h2>{{ auth()->user()->first_name ?? 'Inspector' }}!</h2>
            <p>Here is a quick overview of your assigned work and schedule.</p>

            <div class="greeting-meta">
                <span>
                    <i class="far fa-calendar"></i>
                    {{ now()->format('M d, Y') }}
                </span>

                <span>
                    <i class="far fa-clock"></i>
                    {{ now()->format('h:i A') }}
                </span>
            </div>
        </div>

        <div class="dashboard-greeting-side">
            <div class="greeting-status">
                <span>Availability</span>

                <strong class="{{ $availabilityStatus === 'Available' ? 'is-available' : 'is-busy' }}">
                    <i class="fas fa-circle"></i>
                    {{ $availabilityStatus }}
                </strong>

                <small>{{ $availabilityMessage }}</small>

                <a href="{{ route('inspector.availability.index') }}">
                    Manage schedule
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div class="greeting-art" aria-hidden="true">
                <i class="fas fa-faucet-drip"></i>
            </div>
        </div>
    </section>

    <section class="inspector-stat-grid">
        <article class="inspector-stat-card">
            <div>
                <span class="stat-label">Assigned Requests</span>
                <strong>{{ $assignedCount }}</strong>
                <small>Waiting for action</small>
            </div>

            <span class="stat-icon stat-blue">
                <i class="fas fa-clipboard-list"></i>
            </span>
        </article>

        <article class="inspector-stat-card">
            <div>
                <span class="stat-label">In Progress</span>
                <strong>{{ $inProgressCount }}</strong>
                <small>Active field work</small>
            </div>

            <span class="stat-icon stat-orange">
                <i class="fas fa-screwdriver-wrench"></i>
            </span>
        </article>

        <article class="inspector-stat-card">
            <div>
                <span class="stat-label">Completed</span>
                <strong>{{ $completedCount }}</strong>
                <small>Finished requests</small>
            </div>

            <span class="stat-icon stat-green">
                <i class="fas fa-circle-check"></i>
            </span>
        </article>

        <article class="inspector-stat-card">
            <div>
                <span class="stat-label">Today's Schedule</span>
                <strong>{{ $todayScheduleCount }}</strong>
                <small>Scheduled visits</small>
            </div>

            <span class="stat-icon stat-violet">
                <i class="fas fa-calendar-day"></i>
            </span>
        </article>
    </section>

    <section class="dashboard-work-grid">
        <article class="dashboard-panel visits-panel">
            <header class="dashboard-panel-header">
                <div>
                    <h5>Today's Assigned Visits</h5>
                    <p>Scheduled appointments and service requests.</p>
                </div>

                <a href="{{ route('inspector.quotations.index') }}">
                    View all
                    <i class="fas fa-chevron-right"></i>
                </a>
            </header>

            <div class="inspector-table-wrap">
                <table class="inspector-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Customer</th>
                            <th>Location</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($todayVisits as $visit)
                            <tr
                                data-inspector-search="{{ strtolower(
                                    ($visit->full_name ?? '') . ' ' .
                                    ($visit->address ?? '') . ' ' .
                                    ($visit->service_type ?? '') . ' ' .
                                    ($visit->status ?? '')
                                ) }}"
                            >
                                <td>
                                    <span class="visit-time">
                                        <span class="visit-time-dot"></span>

                                        {{ $visit->appointment_time
                                            ? \Carbon\Carbon::parse($visit->appointment_time)->format('h:i A')
                                            : '—' }}
                                    </span>
                                </td>

                                <td>
                                    <strong class="visit-customer">{{ $visit->full_name }}</strong>
                                </td>

                                <td>
                                    <span class="visit-location">
                                        {{ \Illuminate\Support\Str::limit($visit->address ?? 'No address', 34) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="visit-service">
                                        <i class="fas fa-droplet"></i>
                                        {{ $visit->service_type ?? 'Plumbing Service' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="inspector-status-badge {{ str_replace('_', '-', $visit->status) }}">
                                        {{ ucwords(str_replace('_', ' ', $visit->status)) }}
                                    </span>
                                </td>

                                <td class="text-end">
                                    <a
                                        href="{{ route('inspector.quotations.show', $visit) }}"
                                        class="inspector-row-action"
                                        aria-label="View request"
                                        title="View request"
                                    >
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="inspector-empty-state">
                                        <span class="empty-icon">
                                            <i class="fas fa-calendar-check"></i>
                                        </span>

                                        <strong>No assigned visits today</strong>
                                        <span>Your scheduled appointments will appear here.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <aside class="dashboard-side-column">
            <article class="dashboard-panel">
                <header class="dashboard-panel-header">
                    <div>
                        <h5>Recent Activity</h5>
                        <p>Latest request updates.</p>
                    </div>
                </header>

                <div class="inspector-activity-list">
                    @forelse ($recentActivities as $activity)
                        @php
                            $activityClass = match ($activity->status) {
                                'completed' => 'green',
                                'in_progress' => 'orange',
                                'assigned' => 'blue',
                                default => 'violet',
                            };

                            $activityIcon = match ($activity->status) {
                                'completed' => 'fa-circle-check',
                                'in_progress' => 'fa-screwdriver-wrench',
                                'assigned' => 'fa-clipboard-list',
                                default => 'fa-calendar-check',
                            };
                        @endphp

                        <a
                            href="{{ route('inspector.quotations.show', $activity) }}"
                            class="inspector-activity-item"
                            data-inspector-search="{{ strtolower(
                                ($activity->status ?? '') . ' ' .
                                ($activity->service_type ?? '') . ' ' .
                                ($activity->full_name ?? '')
                            ) }}"
                        >
                            <span class="activity-icon {{ $activityClass }}">
                                <i class="fas {{ $activityIcon }}"></i>
                            </span>

                            <span class="activity-copy">
                                <strong>{{ ucwords(str_replace('_', ' ', $activity->status)) }}</strong>
                                <small>
                                    {{ $activity->service_type ?? 'Service request' }}
                                    for {{ $activity->full_name }}
                                </small>
                            </span>

                            <time>{{ optional($activity->updated_at)->diffForHumans() }}</time>
                        </a>
                    @empty
                        <div class="inspector-empty-state compact">
                            <span class="empty-icon">
                                <i class="fas fa-clock-rotate-left"></i>
                            </span>

                            <strong>No recent activity</strong>
                            <span>Your latest updates will appear here.</span>
                        </div>
                    @endforelse
                </div>
            </article>

            <article class="dashboard-panel quick-actions-panel">
                <header class="dashboard-panel-header">
                    <div>
                        <h5>Quick Actions</h5>
                        <p>Common inspector tasks.</p>
                    </div>
                </header>

                <div class="quick-actions-list">
                    <a href="{{ route('inspector.quotations.index') }}" class="quick-action-link">
                        <span>
                            <i class="fas fa-clipboard-list"></i>
                            Assigned Requests
                        </span>
                        <i class="fas fa-arrow-right"></i>
                    </a>

                    <a href="{{ route('inspector.availability.index') }}" class="quick-action-link">
                        <span>
                            <i class="fas fa-calendar-check"></i>
                            Availability
                        </span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </article>
        </aside>
    </section>
</div>
@endsection
