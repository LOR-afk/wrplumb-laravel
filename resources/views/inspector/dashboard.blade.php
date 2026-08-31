@extends('inspector.layouts.app')

@section('title', 'Inspector Dashboard - WRPlumb')
@section('topbar_title', 'Inspector Dashboard')
@section('topbar_subtitle', 'Monitor assigned service requests and field activity.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/inspector/dashboard.css') }}?v=inspector-dashboard-01">
@endpush

@section('content')
<div class="inspector-dashboard">
    <section class="inspector-welcome-card">
        <div class="inspector-welcome-main">
            <div class="inspector-welcome-icon">
                <i class="fas fa-helmet-safety"></i>
            </div>

            <div>
                <span class="inspector-eyebrow">Field Operations</span>
                <h2>Welcome back, {{ auth()->user()->first_name ?? 'Inspector' }}!</h2>
                <p>Here is an overview of your workload and scheduled field activity for today.</p>
            </div>
        </div>

        <div class="inspector-welcome-art">
            <div class="inspector-van">
                <i class="fas fa-truck"></i>
                <span>WR</span>
            </div>
            <div class="inspector-city"></div>
        </div>
    </section>

    <section class="inspector-stat-grid">
        <article class="inspector-stat-card">
            <div class="inspector-stat-head">
                <div>
                    <span>Assigned Requests</span>
                    <strong>{{ $assignedCount }}</strong>
                </div>

                <i class="fas fa-clipboard-list stat-blue"></i>
            </div>

            <p>Requests assigned to you and waiting for action.</p>
            <div class="inspector-stat-line blue"><span></span></div>
        </article>

        <article class="inspector-stat-card">
            <div class="inspector-stat-head">
                <div>
                    <span>In Progress</span>
                    <strong>{{ $inProgressCount }}</strong>
                </div>

                <i class="fas fa-screwdriver-wrench stat-orange"></i>
            </div>

            <p>Active field work and ongoing service tasks.</p>
            <div class="inspector-stat-line orange"><span></span></div>
        </article>

        <article class="inspector-stat-card">
            <div class="inspector-stat-head">
                <div>
                    <span>Completed</span>
                    <strong>{{ $completedCount }}</strong>
                </div>

                <i class="fas fa-circle-check stat-green"></i>
            </div>

            <p>Finished service requests with completed updates.</p>
            <div class="inspector-stat-line green"><span></span></div>
        </article>

        <article class="inspector-stat-card">
            <div class="inspector-stat-head">
                <div>
                    <span>Today's Schedule</span>
                    <strong>{{ $todayScheduleCount }}</strong>
                </div>

                <i class="fas fa-calendar-day stat-violet"></i>
            </div>

            <p>Scheduled site visits and field appointments today.</p>
            <div class="inspector-stat-line violet"><span></span></div>
        </article>

        <article class="inspector-stat-card availability-card">
            <div class="inspector-stat-head">
                <div>
                    <span>Availability Status</span>
                    <strong class="{{ $availabilityStatus === 'Available' ? 'text-success' : 'text-warning' }}">
                        <i class="fas fa-circle"></i>
                        {{ $availabilityStatus }}
                    </strong>
                </div>

                <i class="fas fa-user-check stat-green"></i>
            </div>

            <p>{{ $availabilityMessage }}</p>
        </article>
    </section>

    <section class="inspector-dashboard-grid">
        <article class="inspector-panel">
            <header class="inspector-panel-header">
                <div>
                    <i class="fas fa-calendar-day"></i>
                    <h5>Today's Assigned Visits</h5>
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
                            <th>Service Type</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($todayVisits as $visit)
                            <tr>
                                <td>
                                    <span class="visit-time-dot"></span>
                                    {{ $visit->appointment_time
                                        ? \Carbon\Carbon::parse($visit->appointment_time)->format('h:i A')
                                        : '—' }}
                                </td>

                                <td>
                                    <strong>{{ $visit->full_name }}</strong>
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

                                <td>
                                    <a
                                        href="{{ route('inspector.quotations.show', $visit) }}"
                                        class="inspector-row-action"
                                        aria-label="View request"
                                    >
                                        <i class="fas fa-ellipsis-vertical"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="inspector-empty-state">
                                        <i class="fas fa-calendar-check"></i>
                                        <strong>No assigned visits today</strong>
                                        <span>Your scheduled appointments will appear here.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <footer class="inspector-panel-note">
                <i class="fas fa-circle-info"></i>
                Times are displayed using the system timezone.
            </footer>
        </article>

        <article class="inspector-panel">
            <header class="inspector-panel-header">
                <div>
                    <i class="fas fa-clock-rotate-left"></i>
                    <h5>Recent Activity</h5>
                </div>

                <a href="{{ route('inspector.quotations.index') }}">
                    View all
                    <i class="fas fa-chevron-right"></i>
                </a>
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
                    >
                        <span class="activity-icon {{ $activityClass }}">
                            <i class="fas {{ $activityIcon }}"></i>
                        </span>

                        <span class="activity-copy">
                            <strong>
                                {{ ucwords(str_replace('_', ' ', $activity->status)) }}
                            </strong>
                            <small>
                                {{ $activity->service_type ?? 'Service request' }}
                                for {{ $activity->full_name }}
                            </small>
                        </span>

                        <time>
                            {{ optional($activity->updated_at)->diffForHumans() }}
                        </time>
                    </a>
                @empty
                    <div class="inspector-empty-state compact">
                        <i class="fas fa-clock-rotate-left"></i>
                        <strong>No recent activity</strong>
                        <span>Your latest request updates will appear here.</span>
                    </div>
                @endforelse
            </div>
        </article>
    </section>

    <section class="inspector-quick-actions">
        <header>
            <i class="fas fa-bolt"></i>
            <h5>Quick Actions</h5>
        </header>

        <div>
            <a href="{{ route('inspector.quotations.index') }}" class="btn btn-primary">
                <i class="fas fa-clipboard-list"></i>
                View Assigned Requests
                <i class="fas fa-arrow-right"></i>
            </a>

            <a href="{{ route('inspector.availability.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-calendar-check"></i>
                Manage Availability
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </section>
</div>
@endsection