@extends('admin.layouts.app')

@section('title', 'Dashboard Overview - WRPlumb')
@section('topbar_title', 'Dashboard Overview')
@section('topbar_subtitle', 'Monitor clients, revenue, job orders, support activity, and inspector availability.')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
@endpush
@section('content')
<div class="hero-card">
    <div class="hero-kicker">
        <i class="fas fa-shield-halved"></i>
        Admin Monitoring Center
    </div>
    <div class="hero-title">Welcome, Admin</div>
    <p class="hero-text">
        Here is a quick view of your clients, quotation requests, job orders, revenue, and support operations.
    </p>
</div>

<div class="dashboard-filter-card">
    <div>
        <div class="filter-label">Dashboard Range</div>
        <div class="filter-sub">Showing operational activity for: <strong>{{ $rangeLabel }}</strong></div>
    </div>

    <div class="range-pills">
        <a href="{{ route('admin.dashboard', ['range' => 'today']) }}" class="range-pill {{ $range === 'today' ? 'active' : '' }}">Today</a>
        <a href="{{ route('admin.dashboard', ['range' => 'week']) }}" class="range-pill {{ $range === 'week' ? 'active' : '' }}">This Week</a>
        <a href="{{ route('admin.dashboard', ['range' => 'month']) }}" class="range-pill {{ $range === 'month' ? 'active' : '' }}">This Month</a>
        <a href="{{ route('admin.dashboard', ['range' => 'year']) }}" class="range-pill {{ $range === 'year' ? 'active' : '' }}">This Year</a>
    </div>
</div>

<div class="dashboard-grid">
    <div class="dashboard-stat-card">
        <div class="dashboard-stat-top">
            <div>
                <div class="dashboard-stat-label">Total Clients</div>
                <div class="dashboard-stat-value">{{ $clientCount }}</div>
            </div>
            <div class="dashboard-stat-icon blue"><i class="fas fa-users"></i></div>
        </div>
        <div class="dashboard-stat-helper">Registered client accounts in the system.</div>
        <div class="accent-line"><span style="width: 78%; background:#1d9bf0;"></span></div>
    </div>

    <div class="dashboard-stat-card">
        <div class="dashboard-stat-top">
            <div>
                <div class="dashboard-stat-label">Pending Quotations</div>
                <div class="dashboard-stat-value">{{ $pendingQuotationsCount }}</div>
            </div>
            <div class="dashboard-stat-icon orange"><i class="fas fa-hourglass-half"></i></div>
        </div>
        <div class="dashboard-stat-helper">Requests waiting for review or assignment.</div>
        <div class="accent-line"><span style="width: 48%; background:#f59e0b;"></span></div>
    </div>

    <div class="dashboard-stat-card">
        <div class="dashboard-stat-top">
            <div>
                <div class="dashboard-stat-label">Active Job Orders</div>
                <div class="dashboard-stat-value">{{ $activeJobOrdersCount }}</div>
            </div>
            <div class="dashboard-stat-icon green"><i class="fas fa-briefcase"></i></div>
        </div>
        <div class="dashboard-stat-helper">Assigned, scheduled, or ongoing job orders.</div>
        <div class="accent-line"><span style="width: 62%; background:#16a34a;"></span></div>
    </div>

    <div class="dashboard-stat-card">
        <div class="dashboard-stat-top">
            <div>
                <div class="dashboard-stat-label">Completed Jobs</div>
                <div class="dashboard-stat-value">{{ $completedJobOrdersCount }}</div>
            </div>
            <div class="dashboard-stat-icon cyan"><i class="fas fa-circle-check"></i></div>
        </div>
        <div class="dashboard-stat-helper">Job orders marked as completed.</div>
        <div class="accent-line"><span style="width: 76%; background:#0891b2;"></span></div>
    </div>

    <div class="dashboard-stat-card">
        <div class="dashboard-stat-top">
            <div>
                <div class="dashboard-stat-label">Pending Support</div>
                <div class="dashboard-stat-value">{{ $pendingSupportCount }}</div>
            </div>
            <div class="dashboard-stat-icon violet"><i class="fas fa-comments"></i></div>
        </div>
        <div class="dashboard-stat-helper">Client concerns currently routed to HR/Admin.</div>
        <div class="accent-line"><span style="width: 55%; background:#7c3aed;"></span></div>
    </div>

    <div class="dashboard-stat-card">
        <div class="dashboard-stat-top">
            <div>
                <div class="dashboard-stat-label">Available Inspectors</div>
                <div class="dashboard-stat-value">{{ $availableInspectorsCount }}</div>
            </div>
            <div class="dashboard-stat-icon pink"><i class="fas fa-user-check"></i></div>
        </div>
        <div class="dashboard-stat-helper">Inspectors marked available for today.</div>
        <div class="accent-line"><span style="width: 60%; background:#db2777;"></span></div>
    </div>

    <div class="dashboard-stat-card">
        <div class="dashboard-stat-top">
            <div>
                <div class="dashboard-stat-label">Revenue</div>
                <div class="dashboard-stat-value money">PHP {{ number_format((float) $monthlyRevenue, 2) }}</div>
            </div>
            <div class="dashboard-stat-icon slate"><i class="fas fa-peso-sign"></i></div>
        </div>
        <div class="dashboard-stat-helper">Confirmed payments within {{ strtolower($rangeLabel) }}.</div>
        <div class="accent-line"><span style="width: 84%; background:#475569;"></span></div>
    </div>

    <div class="dashboard-stat-card">
        <div class="dashboard-stat-top">
            <div>
                <div class="dashboard-stat-label">Cancelled Requests</div>
                <div class="dashboard-stat-value">{{ $cancelledRequestsCount }}</div>
            </div>
            <div class="dashboard-stat-icon red"><i class="fas fa-ban"></i></div>
        </div>
        <div class="dashboard-stat-helper">Cancelled/rejected requests and job orders.</div>
        <div class="accent-line"><span style="width: 40%; background:#dc2626;"></span></div>
    </div>
</div>

<div class="dashboard-chart-grid">
    <div class="panel">
        <div class="panel-header">
            <h5><i class="fas fa-chart-line me-2 text-primary"></i>Monthly Income</h5>
        </div>
        <div class="panel-body chart-box">
            <canvas id="monthlyIncomeChart"></canvas>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h5><i class="fas fa-file-signature me-2 text-primary"></i>Quotation Status</h5>
        </div>
        <div class="panel-body chart-box">
            <canvas id="quotationStatusChart"></canvas>
        </div>
    </div>
</div>

<div class="dashboard-chart-grid">
    <div class="panel">
        <div class="panel-header">
            <h5><i class="fas fa-clipboard-check me-2 text-primary"></i>Job Order Status</h5>
        </div>
        <div class="panel-body chart-box small">
            <canvas id="jobOrderStatusChart"></canvas>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h5><i class="fas fa-comments me-2 text-primary"></i>Support Queue Status</h5>
        </div>
        <div class="panel-body chart-box small">
            <canvas id="supportStatusChart"></canvas>
        </div>
    </div>
</div>

<div class="dashboard-grid-2">
    <div class="panel">
        <div class="panel-header">
            <h5><i class="fas fa-clock-rotate-left me-2 text-primary"></i>Recent Activity</h5>
        </div>
        <div class="panel-body">
            @if (!empty($recentActivities))
                <div class="activity-list">
                    @foreach ($recentActivities as $activity)
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas {{ $activity['icon'] ?? 'fa-circle-info' }}"></i>
                            </div>
                            <div>
                                <div class="activity-title">{{ $activity['title'] }}</div>
                                <div class="activity-description">{{ $activity['description'] }}</div>
                                <div class="activity-time">{{ $activity['time'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-activity">
                    <i class="fas fa-clock-rotate-left mb-2 d-block"></i>
                    No recent activity found.
                </div>
            @endif
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h5><i class="fas fa-chart-pie me-2 text-primary"></i>System Summary</h5>
        </div>
        <div class="panel-body">
            <div class="summary-list">
                <div class="summary-row">
                    <div class="summary-icon blue">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <div class="summary-label">Total Clients</div>
                        <div class="summary-sub">Registered client accounts</div>
                    </div>
                    <div class="summary-value blue">{{ $clientCount }}</div>
                </div>

                <div class="summary-row">
                    <div class="summary-icon green">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div>
                        <div class="summary-label">Total Inspectors</div>
                        <div class="summary-sub">Field personnel accounts</div>
                    </div>
                    <div class="summary-value green">{{ $workerCount }}</div>
                </div>

                <div class="summary-row">
                    <div class="summary-icon orange">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div>
                        <div class="summary-label">Pending Quotations</div>
                        <div class="summary-sub">Waiting for review</div>
                    </div>
                    <div class="summary-value orange">{{ $pendingQuotationsCount }}</div>
                </div>

                <div class="summary-row">
                    <div class="summary-icon cyan">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div>
                        <div class="summary-label">Active Job Orders</div>
                        <div class="summary-sub">Assigned or ongoing work</div>
                    </div>
                    <div class="summary-value cyan">{{ $activeJobOrdersCount }}</div>
                </div>

                <div class="summary-row">
                    <div class="summary-icon violet">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div>
                        <div class="summary-label">Open Support Concerns</div>
                        <div class="summary-sub">Routed to HR/Admin</div>
                    </div>
                    <div class="summary-value violet">{{ $pendingSupportCount }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 12,
                    usePointStyle: true
                }
            }
        }
    };

    const palette = [
        '#1d9bf0',
        '#16a34a',
        '#f59e0b',
        '#db2777',
        '#7c3aed',
        '#dc2626',
        '#0891b2',
        '#475569'
    ];

    new Chart(document.getElementById('monthlyIncomeChart'), {
        type: 'line',
        data: {
            labels: @json($incomeChart['labels']),
            datasets: [{
                label: 'Confirmed Income',
                data: @json($incomeChart['values']),
                borderColor: '#1d9bf0',
                backgroundColor: 'rgba(29, 155, 240, 0.12)',
                fill: true,
                tension: 0.38,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            ...chartOptions,
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: 1000,
                    ticks: {
                        stepSize: 100,
                        callback: value => 'PHP ' + Number(value).toLocaleString()
                    }
                }
            }
        }
    });

    function makeDoughnutChart(elementId, labels, values, title) {
        new Chart(document.getElementById(elementId), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    label: title,
                    data: values,
                    backgroundColor: palette,
                    borderWidth: 2
                }]
            },
            options: chartOptions
        });
    }

    makeDoughnutChart(
        'quotationStatusChart',
        @json($quotationStatusChart['labels']),
        @json($quotationStatusChart['values']),
        'Quotation Status'
    );

    makeDoughnutChart(
        'jobOrderStatusChart',
        @json($jobOrderStatusChart['labels']),
        @json($jobOrderStatusChart['values']),
        'Job Order Status'
    );

    makeDoughnutChart(
        'supportStatusChart',
        @json($supportStatusChart['labels']),
        @json($supportStatusChart['values']),
        'Support Queue Status'
    );
</script>
@endsection
