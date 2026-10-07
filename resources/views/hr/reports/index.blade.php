@extends('hr.layouts.app')

@section('title', 'HR Reports')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/hr/reports.css') }}?v=reports-01">
@endpush

@section('content')
<div class="reports-page">
    <section class="reports-hero">
        <div>
            <span class="reports-kicker">HR · Financial Analytics</span>
            <h2>Reports</h2>
            <p>Track collections, payment activity, and export income records.</p>
        </div>

        <form method="GET" action="{{ route('hr.reports.index') }}" class="reports-quick-filter">
            <div>
                <label>Year</label>
                <input
                    type="number"
                    name="year"
                    value="{{ $year }}"
                    min="2020"
                    max="2100"
                >
            </div>

            <div>
                <label>Month</label>
                <select name="month">
                    <option value="">All Months</option>
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}" @selected((string) $month === (string) $m)>
                            {{ DateTime::createFromFormat('!m', $m)->format('M') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit">
                <i class="fas fa-filter"></i>
                Apply
            </button>
        </form>
    </section>

    <section class="reports-kpi-grid">
        <article class="reports-kpi-card">
            <span class="reports-kpi-icon income">
                <i class="fas fa-wallet"></i>
            </span>
            <div>
                <span>Total Income</span>
                <strong>PHP {{ number_format((float) $summary['total_income'], 2) }}</strong>
                <small>Confirmed collections</small>
            </div>
        </article>

        <article class="reports-kpi-card">
            <span class="reports-kpi-icon confirmed">
                <i class="fas fa-circle-check"></i>
            </span>
            <div>
                <span>Confirmed Payments</span>
                <strong>{{ $summary['confirmed_count'] }}</strong>
                <small>Verified transactions</small>
            </div>
        </article>

        <article class="reports-kpi-card">
            <span class="reports-kpi-icon rejected">
                <i class="fas fa-circle-xmark"></i>
            </span>
            <div>
                <span>Rejected Payments</span>
                <strong>{{ $summary['rejected_count'] }}</strong>
                <small>Rejected transactions</small>
            </div>
        </article>

        <article class="reports-kpi-card">
            <span class="reports-kpi-icon receipts">
                <i class="fas fa-receipt"></i>
            </span>
            <div>
                <span>Receipts Issued</span>
                <strong>{{ $summary['receipt_count'] }}</strong>
                <small>Generated receipts</small>
            </div>
        </article>
    </section>

    <section class="reports-chart-grid">
        <article class="reports-chart-card reports-chart-wide">
            <div class="reports-card-head">
                <div>
                    <span class="reports-card-kicker">Revenue Trend</span>
                    <h5>Monthly Income</h5>
                    <p>Confirmed collections across {{ $year }}.</p>
                </div>

                <span class="reports-card-chip">{{ $year }}</span>
            </div>

            <div class="reports-chart-wrap reports-chart-line">
                <canvas id="monthlyIncomeChart"></canvas>
            </div>
        </article>

        <article class="reports-chart-card">
            <div class="reports-card-head">
                <div>
                    <span class="reports-card-kicker">Payment Activity</span>
                    <h5>Status Distribution</h5>
                    <p>Confirmed, pending, and rejected payments.</p>
                </div>
            </div>

            <div class="reports-chart-wrap reports-chart-doughnut">
                <canvas id="paymentStatusChart"></canvas>
            </div>

            <div class="reports-status-legend">
                <span><i class="status-dot confirmed"></i>Confirmed {{ $statusBreakdown['confirmed'] }}</span>
                <span><i class="status-dot pending"></i>Pending {{ $statusBreakdown['pending'] }}</span>
                <span><i class="status-dot rejected"></i>Rejected {{ $statusBreakdown['rejected'] }}</span>
            </div>
        </article>
    </section>

    <section class="reports-chart-card reports-method-card">
        <div class="reports-card-head">
            <div>
                <span class="reports-card-kicker">Collection Channels</span>
                <h5>Income by Payment Method</h5>
                <p>Confirmed income grouped by payment method.</p>
            </div>
        </div>

        <div class="reports-chart-wrap reports-chart-bar">
            <canvas id="paymentMethodChart"></canvas>
        </div>
    </section>

    <section class="reports-export-card">
        <div class="reports-export-head">
            <div>
                <span class="reports-card-kicker">Export</span>
                <h5>
                    <i class="fas fa-file-excel"></i>
                    Income Report
                </h5>
                <p>Download filtered payment records as an Excel workbook.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('hr.reports.income.export') }}" class="reports-export-grid">
            <div>
                <label class="form-label">Year</label>
                <input
                    type="number"
                    name="year"
                    class="form-control"
                    value="{{ $year }}"
                    min="2020"
                    max="2100"
                >
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
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="pending">Pending</option>
                    <option value="pending_verification">Pending Verification</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>

            <div>
                <label class="form-label">Payment Method</label>
                <select name="method" class="form-select">
                    <option value="">All Methods</option>
                    <option value="Cash">Cash</option>
                    <option value="GCash">GCash</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                </select>
            </div>

            <div class="reports-export-button-wrap">
                <button class="btn btn-success reports-export-button">
                    <i class="fas fa-download"></i>
                    Export Excel
                </button>
            </div>
        </form>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') {
        return;
    }

    const monthlyIncome = @json($monthlyIncome);
    const statusBreakdown = @json($statusBreakdown);
    const methodBreakdown = @json($methodBreakdown);

    const currencyFormatter = new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        maximumFractionDigits: 0
    });

    new Chart(document.getElementById('monthlyIncomeChart'), {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Income',
                data: monthlyIncome,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, .10)',
                pointBackgroundColor: '#2563eb',
                pointBorderWidth: 0,
                pointRadius: 3,
                pointHoverRadius: 5,
                borderWidth: 2.5,
                tension: .35,
                fill: true
            }]
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
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return currencyFormatter.format(context.parsed.y || 0);
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    border: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    border: {
                        display: false
                    },
                    grid: {
                        color: 'rgba(148, 163, 184, .16)'
                    },
                    ticks: {
                        callback: function (value) {
                            if (value >= 1000000) {
                                return 'PHP ' + (value / 1000000).toFixed(1) + 'M';
                            }

                            if (value >= 1000) {
                                return 'PHP ' + (value / 1000).toFixed(0) + 'K';
                            }

                            return 'PHP ' + value;
                        }
                    }
                }
            }
        }
    });

    new Chart(document.getElementById('paymentStatusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Confirmed', 'Pending', 'Rejected'],
            datasets: [{
                data: [
                    statusBreakdown.confirmed,
                    statusBreakdown.pending,
                    statusBreakdown.rejected
                ],
                backgroundColor: ['#22c55e', '#f59e0b', '#ef4444'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    const methodLabels = Object.keys(methodBreakdown);
    const methodValues = Object.values(methodBreakdown);

    new Chart(document.getElementById('paymentMethodChart'), {
        type: 'bar',
        data: {
            labels: methodLabels.length ? methodLabels : ['No confirmed payments'],
            datasets: [{
                label: 'Income',
                data: methodValues.length ? methodValues : [0],
                backgroundColor: '#0f4c81',
                borderRadius: 8,
                borderSkipped: false,
                maxBarThickness: 46
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return currencyFormatter.format(context.parsed.x || 0);
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    border: {
                        display: false
                    },
                    grid: {
                        color: 'rgba(148, 163, 184, .16)'
                    },
                    ticks: {
                        callback: function (value) {
                            if (value >= 1000000) {
                                return 'PHP ' + (value / 1000000).toFixed(1) + 'M';
                            }

                            if (value >= 1000) {
                                return 'PHP ' + (value / 1000).toFixed(0) + 'K';
                            }

                            return 'PHP ' + value;
                        }
                    }
                },
                y: {
                    grid: {
                        display: false
                    },
                    border: {
                        display: false
                    }
                }
            }
        }
    });
});
</script>
@endsection
