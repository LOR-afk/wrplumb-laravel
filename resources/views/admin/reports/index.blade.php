@extends('admin.layouts.app')

@section('title', 'Reports - WRPlumb')
@section('topbar_title', 'Reports')
@section('topbar_subtitle', 'Generate operational reports and export project records.')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/reports.css') }}">
@endpush
@section('content')


<div class="report-hero">
    <div class="report-hero-badge">
        <i class="fas fa-chart-column"></i>
        Operational Reporting Center
    </div>
    <h1>Admin Reports</h1>
    <p>Monitor project status, income performance, and export filtered project records.</p>
</div>

<div class="report-filter-card">
    <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-3 align-items-end">
        <div class="col-lg-2 col-md-4">
            <label class="form-label fw-bold">Year</label>
            <input type="number" name="year" class="form-control" value="{{ $year }}" min="2020" max="2100">
        </div>

        <div class="col-lg-3 col-md-4">
            <label class="form-label fw-bold">Month</label>
            <select name="month" class="form-select">
                <option value="">All Months</option>
                @foreach (range(1, 12) as $m)
                    <option value="{{ $m }}" @selected((string) $month === (string) $m)>
                        {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-lg-3 col-md-4">
            <label class="form-label fw-bold">Project Status</label>
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="scheduled" @selected($status === 'scheduled')>Scheduled</option>
                <option value="in_progress" @selected($status === 'in_progress')>Ongoing / In Progress</option>
                <option value="completed" @selected($status === 'completed')>Completed</option>
                <option value="cancelled" @selected($status === 'cancelled')>Cancelled</option>
            </select>
        </div>

        <div class="col-lg-2 col-md-6">
            <button class="btn btn-primary w-100">
                <i class="fas fa-filter me-2"></i>Apply Filter
            </button>
        </div>

        <div class="col-lg-2 col-md-6">
            <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary w-100">
                Reset
            </a>
        </div>
    </form>
</div>

<div class="report-grid">
    <div class="report-stat-card">
        <div class="report-stat-top">
            <div>
                <div class="report-stat-label">Completed Projects</div>
                <div class="report-stat-value">{{ $summary['completed'] }}</div>
            </div>
            <div class="report-stat-icon green">
                <i class="fas fa-circle-check"></i>
            </div>
        </div>
        <div class="report-stat-helper">Projects fully completed within the selected period.</div>
    </div>

    <div class="report-stat-card">
        <div class="report-stat-top">
            <div>
                <div class="report-stat-label">Ongoing Projects</div>
                <div class="report-stat-value">{{ $summary['ongoing'] }}</div>
            </div>
            <div class="report-stat-icon orange">
                <i class="fas fa-person-digging"></i>
            </div>
        </div>
        <div class="report-stat-helper">Projects currently marked as in progress.</div>
    </div>

    <div class="report-stat-card">
        <div class="report-stat-top">
            <div>
                <div class="report-stat-label">Scheduled Projects</div>
                <div class="report-stat-value">{{ $summary['scheduled'] }}</div>
            </div>
            <div class="report-stat-icon blue">
                <i class="fas fa-calendar-check"></i>
            </div>
        </div>
        <div class="report-stat-helper">Projects already scheduled for service.</div>
    </div>

    <div class="report-stat-card">
        <div class="report-stat-top">
            <div>
                <div class="report-stat-label">Cancelled Projects</div>
                <div class="report-stat-value">{{ $summary['cancelled'] }}</div>
            </div>
            <div class="report-stat-icon red">
                <i class="fas fa-ban"></i>
            </div>
        </div>
        <div class="report-stat-helper">Projects cancelled during the selected period.</div>
    </div>

    <div class="report-stat-card">
        <div class="report-stat-top">
            <div>
                <div class="report-stat-label">Confirmed Income</div>
                <div class="report-stat-value money">PHP {{ number_format((float) $incomeSummary['confirmed_income'], 2) }}</div>
            </div>
            <div class="report-stat-icon violet">
                <i class="fas fa-peso-sign"></i>
            </div>
        </div>
        <div class="report-stat-helper">Confirmed payments recorded for the selected period.</div>
    </div>
</div>

<div class="report-chart-grid">
    <div class="panel">
        <div class="panel-header">
            <h5><i class="fas fa-chart-line me-2 text-primary"></i>Monthly Projects and Income</h5>
        </div>
        <div class="panel-body chart-box">
            <canvas id="monthlyProjectsIncomeChart"></canvas>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h5><i class="fas fa-chart-pie me-2 text-primary"></i>Project Status Distribution</h5>
        </div>
        <div class="panel-body chart-box small">
            <canvas id="projectStatusChart"></canvas>
        </div>
    </div>
</div>

<div class="panel mb-4">
    <div class="panel-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5><i class="fas fa-file-excel me-2 text-success"></i>Export Project Report</h5>
        <span class="project-table-meta">
            Uses the selected filters above
        </span>
    </div>

    <div class="panel-body">
        <div class="export-callout">
            <div class="row g-3 align-items-center">
                <div class="col-lg-8">
                    <div class="export-title">Download Excel Report</div>
                    <p class="export-text mb-lg-0">
                        This exports the same filtered project records shown in the preview table.
                    </p>
                </div>

                <div class="col-lg-4">
                    <form method="GET" action="{{ route('admin.reports.projects.export') }}">
                        <input type="hidden" name="year" value="{{ $year }}">
                        <input type="hidden" name="month" value="{{ $month }}">
                        <input type="hidden" name="status" value="{{ $status }}">

                        <button class="btn btn-primary w-100">
                            <i class="fas fa-download me-2"></i>Export Excel Report
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5><i class="fas fa-list-check me-2 text-primary"></i>Project Records Preview</h5>
        <div class="project-table-meta">
            Showing {{ $jobOrders->count() }} of {{ $jobOrders->total() }} filtered records
        </div>
    </div>

    <div class="panel-body p-0">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Job Order</th>
                        <th>Client</th>
                        <th>Service</th>
                        <th>Personnel</th>
                        <th>Status</th>
                        <th>Schedule</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($jobOrders as $jobOrder)
                        @php
                            $statusKey = $jobOrder->status ?? 'default';
                            $statusClass = in_array($statusKey, ['scheduled', 'in_progress', 'completed', 'cancelled'])
                                ? $statusKey
                                : 'default';

                            $clientName = $jobOrder->quotationRequest?->full_name
                                ?? trim(($jobOrder->quotationRequest?->first_name ?? '') . ' ' . ($jobOrder->quotationRequest?->last_name ?? ''));
                        @endphp

                        <tr>
                            <td class="fw-bold">
                                {{ $jobOrder->job_order_no ?? '—' }}
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $clientName ?: '—' }}</div>
                                <div class="project-table-meta">{{ $jobOrder->quotationRequest?->email ?? '' }}</div>
                            </td>
                            <td>{{ $jobOrder->service_type ?? '—' }}</td>
                            <td>{{ $jobOrder->worker?->name ?? $jobOrder->worker?->first_name ?? 'Not assigned' }}</td>
                            <td>
                                <span class="report-status-badge {{ $statusClass }}">
                                    <i class="fas fa-circle"></i>
                                    {{ strtoupper(str_replace('_', ' ', $jobOrder->status ?? 'Unknown')) }}
                                </span>
                            </td>
                            <td>
                                <div>{{ optional($jobOrder->scheduled_date)->format('M d, Y') ?? '—' }}</div>
                                <div class="project-table-meta">{{ $jobOrder->scheduled_time ?? '' }}</div>
                            </td>
                            <td>{{ optional($jobOrder->created_at)->format('M d, Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No project records found for the selected filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($jobOrders, 'links'))
            <div class="p-3">
                {{ $jobOrders->links() }}
            </div>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartPalette = [
        '#1d9bf0',
        '#16a34a',
        '#f59e0b',
        '#ef4444',
        '#7c3aed',
        '#0891b2'
    ];

    new Chart(document.getElementById('monthlyProjectsIncomeChart'), {
        type: 'bar',
        data: {
            labels: @json($monthlyChart['labels']),
            datasets: [
                {
                    type: 'bar',
                    label: 'Projects',
                    data: @json($monthlyChart['projects']),
                    backgroundColor: 'rgba(29, 155, 240, 0.22)',
                    borderColor: '#1d9bf0',
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    type: 'line',
                    label: 'Income',
                    data: @json($monthlyChart['income']),
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22, 163, 74, 0.12)',
                    fill: true,
                    tension: 0.35,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    position: 'left',
                    ticks: {
                        precision: 0
                    }
                },
                y1: {
                    beginAtZero: true,
                    suggestedMax: 1000,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    },
                    ticks: {
                        callback: value => 'PHP ' + Number(value).toLocaleString()
                    }
                }
            }
        }
    });

    new Chart(document.getElementById('projectStatusChart'), {
        type: 'doughnut',
        data: {
            labels: @json($statusChart['labels']),
            datasets: [{
                data: @json($statusChart['values']),
                backgroundColor: chartPalette,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 12
                    }
                }
            }
        }
    });
</script>
@endsection
