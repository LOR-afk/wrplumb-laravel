@extends('hr.layouts.app')

@section('title', 'HR Dashboard - WRPlumb')
@section('topbar_title', 'HR Dashboard')
@section('topbar_subtitle', 'Monitor conversations routed to HR and support operations.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/hr/dashboard.css') }}">
@endpush

@section('content')
@php
    $hrName = auth()->user()->first_name ?? 'HR';
@endphp

<div class="hr-dashboard-page">
    <section class="hr-welcome-card">
        <div class="hr-welcome-icon">
            <i class="fas fa-headset"></i>
        </div>

        <div class="hr-welcome-main">
            <span class="hr-kicker">HR Support Operations</span>
            <h2>Welcome, {{ $hrName }}</h2>
            <p>Review routed client concerns, monitor HR workload, and keep support conversations moving.</p>
        </div>

        <div class="hr-welcome-metrics">
            <div class="hr-mini-metric">
                <span>Avg. Response</span>
                <strong>{{ $averageResponseTime ?? '—' }}</strong>
            </div>
            <div class="hr-mini-metric">
                <span>SLA Compliance</span>
                <strong class="success">{{ $slaCompliance ?? '—' }}</strong>
            </div>
        </div>
    </section>

    <section class="hr-kpi-grid">
        <article class="hr-kpi-card blue">
            <div class="hr-kpi-icon"><i class="fas fa-inbox"></i></div>
            <div class="hr-kpi-body">
                <span>Total in HR Queue</span>
                <strong>{{ $openCount }}</strong>
                <p>Support conversations currently handled by HR.</p>
                <div class="hr-kpi-progress"><i style="width: {{ $openPercent ?? 12 }}%"></i></div>
            </div>
        </article>

        <article class="hr-kpi-card orange">
            <div class="hr-kpi-icon"><i class="fas fa-share"></i></div>
            <div class="hr-kpi-body">
                <span>Routed to HR</span>
                <strong>{{ $routedCount }}</strong>
                <p>Concerns forwarded by the support workflow.</p>
                <div class="hr-kpi-progress"><i style="width: {{ $routedPercent ?? 25 }}%"></i></div>
            </div>
        </article>

        <article class="hr-kpi-card green">
            <div class="hr-kpi-icon"><i class="fas fa-circle-check"></i></div>
            <div class="hr-kpi-body">
                <span>Resolved Conversations</span>
                <strong>{{ $resolvedCount }}</strong>
                <p>Support concerns completed and closed.</p>
                <div class="hr-kpi-progress"><i style="width: {{ $resolvedPercent ?? 75 }}%"></i></div>
            </div>
        </article>

        <article class="hr-kpi-card violet">
            <div class="hr-kpi-icon"><i class="fas fa-calendar-check"></i></div>
            <div class="hr-kpi-body">
                <span>Pending Follow-ups</span>
                <strong>{{ $pendingFollowups ?? 0 }}</strong>
                <p>Conversations awaiting action or update.</p>
                <div class="hr-kpi-progress"><i style="width: {{ $pendingPercent ?? 40 }}%"></i></div>
            </div>
        </article>
    </section>

    <section class="hr-action-panel">
        <div class="hr-section-head">
            <div>
                <h5><i class="fas fa-bolt me-2 text-primary"></i>Quick Actions</h5>
                <p>Jump to common HR workflows.</p>
            </div>
        </div>

        <div class="hr-action-grid">
            <a href="{{ route('hr.support.index') }}" class="hr-action-card">
                <span><i class="fas fa-comments"></i></span>
                <strong>Open Support Queue</strong>
                <i class="fas fa-chevron-right"></i>
            </a>

            <a href="{{ route('hr.quotations.index') }}" class="hr-action-card">
                <span><i class="fas fa-file-invoice-dollar"></i></span>
                <strong>View Quotations</strong>
                <i class="fas fa-chevron-right"></i>
            </a>

            <a href="{{ route('hr.invoices.index') }}" class="hr-action-card">
                <span><i class="fas fa-file-invoice"></i></span>
                <strong>Manage Invoices</strong>
                <i class="fas fa-chevron-right"></i>
            </a>

            <a href="{{ route('hr.reports.index') }}" class="hr-action-card">
                <span><i class="fas fa-chart-column"></i></span>
                <strong>View Reports</strong>
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>
    </section>

    <section class="hr-bottom-grid">
        <div class="hr-panel">
            <div class="hr-section-head">
                <div>
                    <h5><i class="fas fa-clock-rotate-left me-2 text-primary"></i>Recent Support Activity</h5>
                    <p>Latest routed or updated conversations.</p>
                </div>

                <a href="{{ route('hr.support.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>

            <div class="hr-activity-list">
                @forelse ($recentConversations ?? [] as $conversation)
                    @php
                        $clientName = $conversation->client->name
                            ?? trim(($conversation->client->first_name ?? '') . ' ' . ($conversation->client->last_name ?? ''))
                            ?: 'Client';
                        $initials = collect(explode(' ', $clientName))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->implode('') ?: 'C';
                        $statusKey = $conversation->status ?? 'open';
                    @endphp

                    <a href="{{ route('hr.support.show', $conversation) }}" class="hr-activity-item">
                        <span class="hr-avatar">{{ $initials }}</span>
                        <span class="hr-activity-main">
                            <strong>{{ $clientName }}</strong>
                            <small>{{ \Illuminate\Support\Str::limit($conversation->latest_message ?? 'Support conversation update', 48) }}</small>
                        </span>
                        <span class="hr-status-chip {{ $statusKey }}">{{ ucwords(str_replace('_', ' ', $statusKey)) }}</span>
                        <span class="hr-time">{{ optional($conversation->updated_at)->diffForHumans() }}</span>
                    </a>
                @empty
                    <div class="hr-empty-state">
                        <i class="fas fa-comments"></i>
                        <strong>No recent support activity</strong>
                        <p>Routed HR conversations will appear here.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="hr-panel">
            <div class="hr-section-head">
                <div>
                    <h5><i class="fas fa-list-check me-2 text-primary"></i>HR Workflow Summary</h5>
                    <p>Support queue status for this period.</p>
                </div>

                <span class="hr-period-pill">This Month</span>
            </div>

            <div class="hr-workflow-list">
                <div class="hr-workflow-row blue">
                    <span><i class="fas fa-message"></i></span>
                    <div>
                        <strong>Conversations Received</strong>
                        <small>Total support conversations received</small>
                    </div>
                    <b>{{ $receivedCount ?? ($openCount + $resolvedCount) }}</b>
                </div>

                <div class="hr-workflow-row green">
                    <span><i class="fas fa-share"></i></span>
                    <div>
                        <strong>Routed to HR</strong>
                        <small>Forwarded from system or other departments</small>
                    </div>
                    <b>{{ $routedCount }}</b>
                </div>

                <div class="hr-workflow-row violet">
                    <span><i class="fas fa-circle-check"></i></span>
                    <div>
                        <strong>Resolved</strong>
                        <small>Conversations resolved by HR</small>
                    </div>
                    <b>{{ $resolvedCount }}</b>
                </div>

                <div class="hr-workflow-row orange">
                    <span><i class="fas fa-clock"></i></span>
                    <div>
                        <strong>Pending</strong>
                        <small>Awaiting action or follow-up</small>
                    </div>
                    <b>{{ $pendingFollowups ?? 0 }}</b>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
