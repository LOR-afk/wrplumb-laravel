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
    $statusTotal = array_sum($statusChart['values'] ?? []);
    $statusColors = [
        'Scheduled' => 'scheduled',
        'Ongoing' => 'in_progress',
        'Completed' => 'completed',
        'Cancelled' => 'cancelled',
    ];

    $statusBreakdown = collect($statusChart['labels'] ?? [])->map(function ($label, $index) use ($statusChart, $statusTotal, $statusColors) {
        $count = (int) (($statusChart['values'][$index] ?? 0));
        return [
            'label' => $label,
            'count' => $count,
            'percent' => $statusTotal > 0 ? round(($count / $statusTotal) * 100) : 0,
            'class' => $statusColors[$label] ?? 'default',
        ];
    });

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
@endphp

<div class="reports-page">
    <section class="reports-hero-card">
        <div class="reports-hero-copy">
            <div class="reports-hero-kicker"><i class="fas fa-chart-column me-2"></i>Monthly Operations Report</div>
            <h2>{{ $monthName }} {{ $year }} Operational Summary</h2>
            <p>Monitor job orders, quotations, warranty claims, backjobs, inspector availability, and confirmed income in one report center.</p>
        </div>
        <div class="reports-hero-meta">
            <span>Report Type</span>
            <strong>{{ $reportTypeLabels[$reportType] ?? 'All Reports' }}</strong>
            <small>Generated {{ now()->format('M d, Y h:i A') }}</small>
        </div>
    </section>

    <section class="reports-filter-card">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="reports-filter-grid reports-filter-grid-extended">
            <div>
                <label class="form-label">Year</label>
                <input type="number" name="year" class="form-control" value="{{ $year }}" min="2020" max="2100">
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

    <section class="reports-module-grid">
        <article class="reports-module-card job">
            <span><i class="fas fa-clipboard-check"></i></span>
            <div><small>Job Orders</small><strong>{{ $moduleStats['job_orders'] ?? 0 }}</strong></div>
        </article>
        <article class="reports-module-card quotation">
            <span><i class="fas fa-file-signature"></i></span>
            <div><small>Quotations</small><strong>{{ $moduleStats['quotations'] ?? 0 }}</strong></div>
        </article>
        <article class="reports-module-card warranty">
            <span><i class="fas fa-shield-halved"></i></span>
            <div><small>Warranty Claims</small><strong>{{ $moduleStats['warranty_claims'] ?? 0 }}</strong></div>
        </article>
        <article class="reports-module-card backjob">
            <span><i class="fas fa-rotate-left"></i></span>
            <div><small>Backjobs</small><strong>{{ $moduleStats['backjobs'] ?? 0 }}</strong></div>
        </article>
        <article class="reports-module-card availability">
            <span><i class="fas fa-calendar-check"></i></span>
            <div><small>Availability Records</small><strong>{{ $moduleStats['inspector_availability'] ?? 0 }}</strong></div>
        </article>
    </section>

    <section class="reports-kpi-grid">
        <article class="reports-kpi-card completed">
            <div class="reports-kpi-icon"><i class="fas fa-circle-check"></i></div>
            <div>
                <span>Completed Jobs</span>
                <strong>{{ $summary['completed'] }}</strong>
                <p>Fully completed within the selected period.</p>
            </div>
        </article>

        <article class="reports-kpi-card ongoing">
            <div class="reports-kpi-icon"><i class="fas fa-person-digging"></i></div>
            <div>
                <span>Ongoing Jobs</span>
                <strong>{{ $summary['ongoing'] }}</strong>
                <p>Currently marked as in progress.</p>
            </div>
        </article>

        <article class="reports-kpi-card scheduled">
            <div class="reports-kpi-icon"><i class="fas fa-calendar-check"></i></div>
            <div>
                <span>Scheduled Jobs</span>
                <strong>{{ $summary['scheduled'] }}</strong>
                <p>Already scheduled for service.</p>
            </div>
        </article>

        <article class="reports-kpi-card cancelled">
            <div class="reports-kpi-icon"><i class="fas fa-ban"></i></div>
            <div>
                <span>Cancelled Jobs</span>
                <strong>{{ $summary['cancelled'] }}</strong>
                <p>Cancelled during the selected period.</p>
            </div>
        </article>

        <article class="reports-kpi-card income">
            <div class="reports-kpi-icon"><i class="fas fa-peso-sign"></i></div>
            <div>
                <span>Confirmed Income</span>
                <strong class="money">PHP {{ number_format((float) $incomeSummary['confirmed_income'], 2) }}</strong>
                <p>Confirmed payments for this filter.</p>
            </div>
        </article>
    </section>

    <section class="reports-chart-grid reports-chart-grid-extended">
        <div class="reports-panel reports-performance-panel">
            <div class="reports-panel-head">
                <div>
                    <h5><i class="fas fa-chart-line me-2 text-primary"></i>Monthly Operations Trend</h5>
                    <p>Job orders, quotations, warranty claims, backjobs, and income trend by month.</p>
                </div>
            </div>
            <div class="reports-chart-box">
                <canvas id="monthlyOperationsChart"></canvas>
            </div>
        </div>

        <div class="reports-panel reports-status-panel">
            <div class="reports-panel-head">
                <div>
                    <h5><i class="fas fa-chart-pie me-2 text-primary"></i>Job Status Breakdown</h5>
                    <p>Distribution of job orders by current status.</p>
                </div>
            </div>

            <div class="reports-status-content compact">
                <div class="reports-donut-wrap">
                    <canvas id="projectStatusChart"></canvas>
                </div>

                <div class="reports-status-table">
                    <div class="reports-status-row reports-status-heading">
                        <span>Status</span><span>Count</span><span>%</span>
                    </div>
                    @foreach ($statusBreakdown as $item)
                        <div class="reports-status-row">
                            <span><i class="reports-status-dot {{ $item['class'] }}"></i>{{ $item['label'] }}</span>
                            <strong>{{ $item['count'] }}</strong>
                            <strong>{{ $item['percent'] }}%</strong>
                        </div>
                    @endforeach
                    <div class="reports-status-row reports-status-total">
                        <span>Total</span><strong>{{ $statusTotal }}</strong><strong>{{ $statusTotal > 0 ? '100%' : '0%' }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="reports-panel reports-inspector-panel">
        <div class="reports-panel-head reports-records-head">
            <div>
                <h5><i class="fas fa-user-clock me-2 text-primary"></i>Inspector Availability Summary</h5>
                <p>Monthly attendance-style overview based on availability records.</p>
            </div>
        </div>
        <div class="reports-inspector-grid">
            <div class="reports-inspector-pill available"><span>Available</span><strong>{{ $inspectorSummary['available'] ?? 0 }}</strong></div>
            <div class="reports-inspector-pill duty"><span>On Duty</span><strong>{{ $inspectorSummary['on_duty'] ?? 0 }}</strong></div>
            <div class="reports-inspector-pill off"><span>Off Duty</span><strong>{{ $inspectorSummary['off_duty'] ?? 0 }}</strong></div>
            <div class="reports-inspector-pill leave"><span>On Leave</span><strong>{{ $inspectorSummary['on_leave'] ?? 0 }}</strong></div>
        </div>
    </section>

    <section class="reports-panel reports-records-panel">
        <div class="reports-panel-head reports-records-head">
            <div>
                <h5><i class="fas fa-table-list me-2 text-primary"></i>{{ $reportTypeLabels[$reportType] ?? 'All Reports' }} Preview</h5>
                <p>Showing {{ $records->count() }} of {{ $records->total() }} filtered record(s).</p>
            </div>

            <div class="reports-record-actions">
                <div class="reports-search-control">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="search" id="reportRecordSearch" class="form-control" placeholder="Search records...">
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
                                <td><div class="reports-client-name">{{ $row['inspector'] }}</div><div class="reports-muted-text">Latest: {{ $row['latest_date'] ?? '—' }}</div></td>
                                <td><span class="reports-count-badge available">{{ $row['available'] }}</span></td>
                                <td><span class="reports-count-badge duty">{{ $row['on_duty'] }}</span></td>
                                <td><span class="reports-count-badge off">{{ $row['off_duty'] }}</span></td>
                                <td><span class="reports-count-badge leave">{{ $row['on_leave'] }}</span></td>
                                <td><span class="reports-count-badge neutral">{{ $row['no_record'] }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-5">No inspector availability records found.</td></tr>
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
                                <td><div class="reports-reference">{{ $row['reference'] }}</div></td>
                                <td><div class="reports-client-name">{{ $row['client'] }}</div></td>
                                <td>{{ \Illuminate\Support\Str::limit($row['subject'], 70) }}</td>
                                <td>
                                    <span class="report-status-badge {{ $statusBadgeClass($row['status']) }}">
                                        <i class="fas fa-circle"></i>{{ strtoupper(str_replace('_', ' ', $row['status'])) }}
                                    </span>
                                </td>
                                <td>{{ $row['date'] }}</td>
                                <td><div class="reports-muted-text">{{ \Illuminate\Support\Str::limit($row['remarks'], 80) }}</div></td>
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

        @if(method_exists($records, 'links'))
            <div class="reports-pagination">
                {{ $records->links() }}
            </div>
        @endif
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const monthlyChartElement = document.getElementById('monthlyOperationsChart');
    if (monthlyChartElement) {
        new Chart(monthlyChartElement, {
            type: 'bar',
            data: {
                labels: @json($monthlyChart['labels']),
                datasets: [
                    { label: 'Job Orders', data: @json($monthlyChart['jobOrders']), backgroundColor: 'rgba(29, 155, 240, 0.25)', borderColor: '#1d9bf0', borderWidth: 1, borderRadius: 6, yAxisID: 'y' },
                    { label: 'Quotations', data: @json($monthlyChart['quotations']), backgroundColor: 'rgba(15, 76, 129, 0.18)', borderColor: '#0f4c81', borderWidth: 1, borderRadius: 6, yAxisID: 'y' },
                    { label: 'Warranty Claims', data: @json($monthlyChart['warranties']), backgroundColor: 'rgba(124, 58, 237, 0.18)', borderColor: '#7c3aed', borderWidth: 1, borderRadius: 6, yAxisID: 'y' },
                    { label: 'Backjobs', data: @json($monthlyChart['backjobs']), backgroundColor: 'rgba(239, 68, 68, 0.16)', borderColor: '#ef4444', borderWidth: 1, borderRadius: 6, yAxisID: 'y' },
                    { type: 'line', label: 'Income (PHP)', data: @json($monthlyChart['income']), borderColor: '#16a34a', backgroundColor: 'rgba(22, 163, 74, 0.08)', fill: true, tension: 0.35, pointRadius: 3, yAxisID: 'y1' }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 10 } } },
                scales: {
                    y: { beginAtZero: true, position: 'left', ticks: { precision: 0 } },
                    y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { callback: value => 'PHP ' + Number(value).toLocaleString() } }
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
                datasets: [{ data: @json($statusChart['values']), backgroundColor: ['#1d9bf0', '#16a34a', '#f59e0b', '#ef4444'], borderWidth: 3, borderColor: '#ffffff', hoverOffset: 4 }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '62%', plugins: { legend: { display: false } } }
        });
    }

    const searchInput = document.getElementById('reportRecordSearch');
    const table = document.getElementById('reportRecordsTable');
    if (searchInput && table) {
        searchInput.addEventListener('input', function () {
            const term = searchInput.value.toLowerCase().trim();
            table.querySelectorAll('tbody tr').forEach(function (row) {
                row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
            });
        });
    }
});
</script>
@endsection
