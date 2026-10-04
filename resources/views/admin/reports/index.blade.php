@extends('admin.layouts.app')

@section('title', 'Reports - WRPlumb')

@section('topbar_title', 'Reports')

@section('topbar_subtitle', 'Generate monthly operations reports across service workflows.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/reports.css') }}">
@endpush

@section('content')

    @php
        $monthName = $month ? DateTime::createFromFormat('!m', (int) $month)->format('F') : 'All Months';
        $reportTypeLabels = [
            'all' => 'All Reports',
            'job_orders' => 'Job Orders',
            'quotations' => 'Quotations',
            'warranty_claims' => 'Warranty Claims',
            'backjobs' => 'Backjobs',
            'inspector_availability' => 'Inspector Availability',
        ];
        $statusBadgeClass = function ($value) {
            $value = strtolower((string) $value);
            return match ($value) {
                'scheduled', 'pending', 'routed' => 'scheduled',
                'in_progress', 'on_duty', 'approved' => 'in_progress',
                'completed', 'resolved', 'available' => 'completed',
                'cancelled', 'rejected', 'off_duty', 'on_leave' => 'cancelled',
                default => 'default',
            };
        };
        $monthlyChartHasData =
            array_sum($monthlyChart['jobOrders'] ?? []) +
                array_sum($monthlyChart['quotations'] ?? []) +
                array_sum($monthlyChart['warranties'] ?? []) +
                array_sum($monthlyChart['backjobs'] ?? []) +
                array_sum($monthlyChart['income'] ?? []) >
            0;
        $statusChartHasData = array_sum($statusChart['values'] ?? []) > 0;
    @endphp

    <div class="reports-page">

        <section class="reports-hero-card">

            <div class="reports-hero-copy">

                <div class="reports-hero-kicker"><i class="fas fa-chart-column me-2"></i>Monthly Operations Report</div>

                <h2>{{ $monthName }} {{ $year }} Operational Summary</h2>

                <p>Review the most important operational totals, trends, and filtered records in one report center.</p>

            </div>

            <div class="reports-hero-meta">

                <span>Report Type</span>

                <strong>{{ $reportTypeLabels[$reportType] ?? 'All Reports' }}</strong>

                <small>Generated {{ now()->format('M d, Y h:i A') }}</small>

            </div>

        </section>

        <section class="reports-filter-card">

            <form method="GET" action="{{ route('admin.reports.index') }}"
                class="reports-filter-grid reports-filter-grid-extended">

                <div>

                    <label class="form-label">Year</label>

                    <input type="number" name="year" class="form-control" value="{{ $year }}" min="2020"
                        max="2100">

                </div>

                <div>

                    <label class="form-label">Month</label>

                    <select name="month" class="form-select">

                        <option value="">All Months</option>

                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}" @selected((string) $month === (string) $m)>

                                {{ DateTime::createFromFormat('!m', $m)->format('F') }}

                            </option>
                        @endforeach

                    </select>

                </div>

                <div>

                    <label class="form-label">Report Type</label>

                    <select name="report_type" class="form-select">

                        @foreach ($reportTypeLabels as $value => $label)
                            <option value="{{ $value }}" @selected($reportType === $value)>{{ $label }}</option>
                        @endforeach

                    </select>

                </div>

                <div>

                    <label class="form-label">Status / Focus</label>

                    <select name="status" class="form-select">

                        <option value="">All Status</option>

                        <option value="scheduled" @selected($status === 'scheduled')>Scheduled</option>

                        <option value="in_progress" @selected($status === 'in_progress')>In Progress</option>

                        <option value="completed" @selected($status === 'completed')>Completed</option>

                        <option value="cancelled" @selected($status === 'cancelled')>Cancelled</option>

                        <option value="pending" @selected($status === 'pending')>Pending</option>

                        <option value="approved" @selected($status === 'approved')>Approved</option>

                        <option value="resolved" @selected($status === 'resolved')>Resolved</option>

                        <option value="available" @selected($status === 'available')>Inspector Available</option>

                        <option value="on_duty" @selected($status === 'on_duty')>Inspector On Duty</option>

                        <option value="off_duty" @selected($status === 'off_duty')>Inspector Off Duty</option>

                        <option value="on_leave" @selected($status === 'on_leave')>Inspector On Leave</option>

                    </select>

                </div>

                <div class="reports-filter-actions">

                    <button class="btn btn-primary">

                        <i class="fas fa-filter me-1"></i> Apply Filter

                    </button>

                    <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">

                        <i class="fas fa-rotate-left me-1"></i> Reset

                    </a>

                </div>

            </form>

        </section>

        <section class="reports-kpi-grid">

            <article class="reports-kpi-card records">

                <div class="reports-kpi-icon"><i class="fas fa-table-list"></i></div>

                <div>

                    <span>Filtered Records</span>

                    <strong>{{ method_exists($records, 'total') ? $records->total() : $records->count() }}</strong>

                    <p>Records matching the selected filters.</p>

                </div>

            </article>

            <article class="reports-kpi-card completed">

                <div class="reports-kpi-icon"><i class="fas fa-circle-check"></i></div>

                <div>

                    <span>Completed Jobs</span>

                    <strong>{{ $summary['completed'] ?? 0 }}</strong>

                    <p>Completed service work for the selected period.</p>

                </div>

            </article>

            <article class="reports-kpi-card active">

                <div class="reports-kpi-icon"><i class="fas fa-person-digging"></i></div>

                <div>

                    <span>Active Jobs</span>

                    <strong>{{ ($summary['ongoing'] ?? 0) + ($summary['scheduled'] ?? 0) }}</strong>

                    <p>Scheduled and ongoing jobs requiring attention.</p>

                </div>

            </article>

            <article class="reports-kpi-card income">

                <div class="reports-kpi-icon"><i class="fas fa-peso-sign"></i></div>

                <div>

                    <span>Confirmed Income</span>

                    <strong class="money">PHP

                        {{ number_format((float) ($incomeSummary['confirmed_income'] ?? 0), 2) }}</strong>

                    <p>Confirmed payments for this filter.</p>

                </div>

            </article>

        </section>

        <section class="reports-chart-grid reports-chart-grid-extended">
            <div class="reports-panel reports-performance-panel">
                <div class="reports-panel-head">
                    <div>
                        <h5>
                            <i class="fas fa-chart-line me-2 text-primary"></i>
                            {{ $month ? $monthName . ' Operations Overview' : 'Monthly Operations Trend' }}
                        </h5>
                        <p>
                            {{ $month
                                ? 'Operational activity for ' . $monthName . ' ' . $year . '.'
                                : 'Job orders, quotations, warranty claims, backjobs, and income trend by month.' }}
                        </p>
                    </div>
                </div>
                @if ($monthlyChartHasData)
                    <div class="reports-chart-box">
                        <canvas id="monthlyOperationsChart"></canvas>
                    </div>
                @else
                    <div class="reports-chart-empty">
                        <i class="fas fa-chart-line"></i>
                        <strong>No operational data</strong>
                        <span>
                            {{ $month
                                ? 'No records found for ' . $monthName . ' ' . $year . '.'
                                : 'No operational records found for ' . $year . '.' }}
                        </span>
                    </div>
                @endif
            </div>

            <div class="reports-panel reports-status-panel">
                <div class="reports-panel-head">
                    <div>
                        <h5><i class="fas fa-chart-pie me-2 text-primary"></i>Job Status Breakdown</h5>
                        <p>
                            {{ $month
                                ? 'Job order status distribution for ' . $monthName . ' ' . $year . '.'
                                : 'Distribution of job orders by current status.' }}
                        </p>
                    </div>
                </div>
                @if ($statusChartHasData)
                    <div class="reports-donut-wrap">
                        <canvas id="projectStatusChart"></canvas>
                    </div>
                @else
                    <div class="reports-chart-empty">
                        <i class="fas fa-chart-pie"></i>
                        <strong>No job status data</strong>
                        <span>
                            {{ $month
                                ? 'No job orders found for ' . $monthName . ' ' . $year . '.'
                                : 'No job orders found for ' . $year . '.' }}
                        </span>
                    </div>
                @endif
            </div>
        </section>

        <section class="reports-panel reports-records-panel">

            <div class="reports-panel-head reports-records-head">

                <div>

                    <h5><i
                            class="fas fa-table-list me-2 text-primary"></i>{{ $reportTypeLabels[$reportType] ?? 'All Reports' }}

                        Records</h5>

                    <p>Showing {{ $records->count() }} of {{ $records->total() }} filtered record(s).</p>

                </div>

                <div class="reports-record-actions">

                    <div class="reports-search-control">

                        <i class="fas fa-magnifying-glass"></i>

                        <input type="search" id="reportRecordSearch" class="form-control"
                            placeholder="Search current page...">

                    </div>

                    <form method="GET" action="{{ route('admin.reports.projects.export') }}">

                        <input type="hidden" name="year" value="{{ $year }}">

                        <input type="hidden" name="month" value="{{ $month }}">

                        <input type="hidden" name="status" value="{{ $status }}">

                        <input type="hidden" name="report_type" value="{{ $reportType }}">

                        <button class="btn btn-outline-primary reports-export-btn">

                            <i class="fas fa-file-excel me-1"></i> Export Selected Report

                        </button>

                    </form>

                </div>

            </div>

            <div class="reports-table-wrap">

                @if ($reportType === 'inspector_availability')

                    <table class="table align-middle reports-table reports-table-availability" id="reportRecordsTable">

                        <thead>

                            <tr>

                                @foreach ($recordColumns as $column)
                                    <th>{{ $column }}</th>
                                @endforeach

                            </tr>

                        </thead>

                        <tbody>

                            @forelse ($records as $row)
                                <tr>

                                    <td>

                                        <div class="reports-client-name">{{ $row['inspector'] }}</div>

                                        <div class="reports-muted-text">Latest: {{ $row['latest_date'] ?? '—' }}</div>

                                    </td>

                                    <td><span class="reports-count-badge available">{{ $row['available'] }}</span></td>

                                    <td><span class="reports-count-badge duty">{{ $row['on_duty'] }}</span></td>

                                    <td><span class="reports-count-badge off">{{ $row['off_duty'] }}</span></td>

                                    <td><span class="reports-count-badge leave">{{ $row['on_leave'] }}</span></td>

                                    <td><span class="reports-count-badge neutral">{{ $row['no_record'] }}</span></td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="6" class="text-center text-muted py-5">No inspector availability

                                        records found.</td>

                                </tr>
                            @endforelse

                        </tbody>

                    </table>
                @else
                    <table class="table align-middle reports-table" id="reportRecordsTable">

                        <thead>

                            <tr>

                                @foreach ($recordColumns as $column)
                                    <th>{{ $column }}</th>
                                @endforeach

                            </tr>

                        </thead>

                        <tbody>

                            @forelse ($records as $row)
                                <tr>

                                    <td><span class="reports-type-badge">{{ $row['type'] }}</span></td>

                                    <td>

                                        <div class="reports-reference">{{ $row['reference'] }}</div>

                                    </td>

                                    <td>

                                        <div class="reports-client-name">{{ $row['client'] }}</div>

                                    </td>

                                    <td>{{ \Illuminate\Support\Str::limit($row['subject'], 70) }}</td>

                                    <td>

                                        <span class="report-status-badge {{ $statusBadgeClass($row['status']) }}">

                                            <i
                                                class="fas fa-circle"></i>{{ strtoupper(str_replace('_', ' ', $row['status'])) }}

                                        </span>

                                    </td>

                                    <td>{{ $row['date'] }}</td>

                                    <td>

                                        <div class="reports-muted-text">

                                            {{ \Illuminate\Support\Str::limit($row['remarks'], 80) }}</div>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="7" class="text-center text-muted py-5">

                                        <i class="fas fa-file-circle-xmark d-block mb-2 fs-3"></i>

                                        No records found for the selected filter.

                                    </td>

                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                @endif

            </div>

            @if (method_exists($records, 'links'))
                <div class="reports-pagination">

                    {{ $records->links() }}

                </div>
            @endif

        </section>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const monthlyChartElement = document.getElementById('monthlyOperationsChart');

            if (monthlyChartElement) {

                new Chart(monthlyChartElement, {

                    type: 'bar',

                    data: {

                        labels: @json($monthlyChart['labels']),

                        datasets: [

                            {

                                label: 'Job Orders',

                                data: @json($monthlyChart['jobOrders']),

                                backgroundColor: 'rgba(29, 155, 240, 0.25)',

                                borderColor: '#1d9bf0',

                                borderWidth: 1,

                                borderRadius: 6,

                                yAxisID: 'y'

                            },

                            {

                                label: 'Quotations',

                                data: @json($monthlyChart['quotations']),

                                backgroundColor: 'rgba(15, 76, 129, 0.18)',

                                borderColor: '#0f4c81',

                                borderWidth: 1,

                                borderRadius: 6,

                                yAxisID: 'y'

                            },

                            {

                                label: 'Warranty Claims',

                                data: @json($monthlyChart['warranties']),

                                backgroundColor: 'rgba(124, 58, 237, 0.18)',

                                borderColor: '#7c3aed',

                                borderWidth: 1,

                                borderRadius: 6,

                                yAxisID: 'y'

                            },

                            {

                                label: 'Backjobs',

                                data: @json($monthlyChart['backjobs']),

                                backgroundColor: 'rgba(239, 68, 68, 0.16)',

                                borderColor: '#ef4444',

                                borderWidth: 1,

                                borderRadius: 6,

                                yAxisID: 'y'

                            },

                            {

                                type: 'line',

                                label: 'Income (PHP)',

                                data: @json($monthlyChart['income']),

                                borderColor: '#16a34a',

                                backgroundColor: 'rgba(22, 163, 74, 0.08)',

                                fill: true,

                                tension: 0.35,

                                pointRadius: 3,

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
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 10,
                                    generateLabels(chart) {
                                        return chart.data.datasets.map((dataset, index) => ({
                                            text: `${chart.isDatasetVisible(index) ? '✓' : '□'} ${dataset.label}`,
                                            fillStyle: dataset.borderColor || dataset
                                                .backgroundColor,
                                            strokeStyle: dataset.borderColor || dataset
                                                .backgroundColor,
                                            pointStyle: 'circle',
                                            hidden: false,
                                            datasetIndex: index
                                        }));
                                    }
                                },
                                onClick(event, item, legend) {
                                    const chart = legend.chart;
                                    const index = item.datasetIndex;
                                    chart.setDatasetVisibility(index, !chart.isDatasetVisible(index));
                                    chart.update();
                                }
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

            }

            const statusChartElement = document.getElementById('projectStatusChart');

            if (statusChartElement) {

                new Chart(statusChartElement, {

                    type: 'doughnut',

                    data: {

                        labels: @json($statusChart['labels']),

                        datasets: [{

                            data: @json($statusChart['values']),

                            backgroundColor: ['#1d9bf0', '#16a34a', '#f59e0b', '#ef4444'],

                            borderWidth: 3,

                            borderColor: '#ffffff',

                            hoverOffset: 4

                        }]

                    },

                    options: {

                        responsive: true,

                        maintainAspectRatio: false,

                        cutout: '62%',

                        plugins: {

                            legend: {
                                display: true,
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 10,
                                    padding: 14,
                                    generateLabels(chart) {
                                        const dataset = chart.data.datasets[0];
                                        return chart.data.labels.map((label, index) => ({
                                            text: `${chart.getDataVisibility(index) ? '✓' : '□'} ${label}`,
                                            fillStyle: dataset.backgroundColor[index],
                                            strokeStyle: dataset.backgroundColor[index],
                                            pointStyle: 'circle',
                                            hidden: false,
                                            index
                                        }));
                                    }
                                },
                                onClick(event, item, legend) {
                                    const chart = legend.chart;
                                    chart.toggleDataVisibility(item.index);
                                    chart.update();
                                }
                            }

                        }

                    }

                });

            }

            const searchInput = document.getElementById('reportRecordSearch');

            const table = document.getElementById('reportRecordsTable');

            if (searchInput && table) {

                searchInput.addEventListener('input', function() {

                    const term = searchInput.value.toLowerCase().trim();

                    table.querySelectorAll('tbody tr').forEach(function(row) {

                        row.style.display = row.textContent.toLowerCase().includes(term) ? '' :

                            'none';

                    });

                });

            }

        });
    </script>

@endsection
