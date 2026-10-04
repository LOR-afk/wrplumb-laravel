@extends('admin.layouts.app')



@section('title', 'Dashboard - WRPlumb')

@section('topbar_title', 'Dashboard')

@section('topbar_subtitle', 'Monitor the most important operational and financial indicators.')



@push('styles')

    <link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}?v=admin-dashboard-modern-01">

@endpush



@section('content')

<div class="admin-dashboard-page">

    <section class="admin-dashboard-greeting">

        <div class="admin-greeting-copy">

            <span class="admin-greeting-label">Good day,</span>

            <h2>{{ auth()->user()->first_name ?? 'Admin' }}!</h2>

            <p>Here is a quick operational overview for <strong>{{ $rangeLabel }}</strong>.</p>



            <div class="admin-greeting-meta">

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



        <div class="admin-range-area">

            <span class="admin-range-label">Dashboard Range</span>



            <div class="admin-range-pills">

                <a href="{{ route('admin.dashboard', ['range' => 'today']) }}"

                   class="admin-range-pill {{ $range === 'today' ? 'active' : '' }}">

                    Today

                </a>



                <a href="{{ route('admin.dashboard', ['range' => 'week']) }}"

                   class="admin-range-pill {{ $range === 'week' ? 'active' : '' }}">

                    This Week

                </a>



                <a href="{{ route('admin.dashboard', ['range' => 'month']) }}"

                   class="admin-range-pill {{ $range === 'month' ? 'active' : '' }}">

                    This Month

                </a>



                <a href="{{ route('admin.dashboard', ['range' => 'year']) }}"

                   class="admin-range-pill {{ $range === 'year' ? 'active' : '' }}">

                    This Year

                </a>

            </div>

        </div>

    </section>



    <section class="admin-primary-stats">

        <a href="{{ route('admin.clients.index') }}" class="admin-stat-card">

            <div>

                <span class="admin-stat-label">Total Clients</span>

                <strong>{{ $clientCount }}</strong>

                <small>Registered client accounts</small>

            </div>

            <span class="admin-stat-icon blue"><i class="fas fa-users"></i></span>

        </a>



        <a href="{{ route('admin.quotations.index') }}" class="admin-stat-card">

            <div>

                <span class="admin-stat-label">Pending Quotations</span>

                <strong>{{ $pendingQuotationsCount }}</strong>

                <small>Waiting for review or assignment</small>

            </div>

            <span class="admin-stat-icon orange"><i class="fas fa-hourglass-half"></i></span>

        </a>



        <a href="{{ route('admin.job-orders.index') }}" class="admin-stat-card">

            <div>

                <span class="admin-stat-label">Active Job Orders</span>

                <strong>{{ $activeJobOrdersCount }}</strong>

                <small>Scheduled or ongoing work</small>

            </div>

            <span class="admin-stat-icon green"><i class="fas fa-briefcase"></i></span>

        </a>



        <a href="{{ route('admin.reports.index') }}" class="admin-stat-card">

            <div>

                <span class="admin-stat-label">Revenue</span>

                <strong class="money">PHP {{ number_format((float) $monthlyRevenue, 2) }}</strong>

                <small>Confirmed payments in {{ strtolower($rangeLabel) }}</small>

            </div>

            <span class="admin-stat-icon cyan"><i class="fas fa-peso-sign"></i></span>

        </a>

    </section>



    



    <section class="admin-chart-grid">

        <article class="admin-panel admin-chart-panel admin-income-panel">

            <header class="admin-panel-header">

                <div>

                    <h3>Monthly Income</h3>

                    <p>Confirmed payment activity.</p>

                </div>

                <span class="admin-panel-icon"><i class="fas fa-chart-line"></i></span>

            </header>



            <div class="admin-chart-body">

                <canvas id="monthlyIncomeChart"></canvas>

            </div>

        </article>



        <article class="admin-panel admin-chart-panel">

            <header class="admin-panel-header">

                <div>

                    <h3>Quotation Status</h3>

                    <p>Current quotation distribution.</p>

                </div>

                <span class="admin-panel-icon"><i class="fas fa-file-signature"></i></span>

            </header>



            <div class="admin-chart-body compact">

                <canvas id="quotationStatusChart"></canvas>

            </div>

        </article>

    </section>



    



    <section class="admin-bottom-grid">

        <article class="admin-panel">

            <header class="admin-panel-header">

                <div>

                    <h3>Recent Activity</h3>

                    <p>Latest operational updates.</p>

                </div>

            </header>



            <div class="admin-activity-list">

                @if (!empty($recentActivities))

                    @foreach ($recentActivities as $activity)

                        <div

                            class="admin-activity-item"

                            data-admin-search="{{ strtolower(

                                ($activity['title'] ?? '') . ' ' .

                                ($activity['description'] ?? '') . ' ' .

                                ($activity['time'] ?? '')

                            ) }}"

                        >

                            <span class="admin-activity-icon">

                                <i class="fas {{ $activity['icon'] ?? 'fa-circle-info' }}"></i>

                            </span>



                            <div class="admin-activity-copy">

                                <strong>{{ $activity['title'] }}</strong>

                                <span>{{ $activity['description'] }}</span>

                            </div>



                            <time>{{ $activity['time'] }}</time>

                        </div>

                    @endforeach

                @else

                    <div class="admin-empty-state">

                        <span><i class="fas fa-clock-rotate-left"></i></span>

                        <strong>No recent activity</strong>

                        <small>New system activity will appear here.</small>

                    </div>

                @endif

            </div>

        </article>



        

    </section>

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

                    boxWidth: 10,

                    boxHeight: 10,

                    usePointStyle: true,

                    padding: 12,

                    font: {

                        size: 10

                    }

                }

            }

        }

    };



    const palette = [

        '#1d9bf0',

        '#16a34a',

        '#f59e0b',

        '#7c3aed',

        '#dc2626',

        '#0891b2',

        '#475569',

        '#db2777'

    ];



    new Chart(document.getElementById('monthlyIncomeChart'), {

        type: 'line',

        data: {

            labels: @json($incomeChart['labels']),

            datasets: [{

                label: 'Confirmed Income',

                data: @json($incomeChart['values']),

                borderColor: '#1d9bf0',

                backgroundColor: 'rgba(29, 155, 240, 0.08)',

                fill: true,

                tension: 0.32,

                pointRadius: 2.5,

                pointHoverRadius: 4

            }]

        },

        options: {

            ...chartOptions,

            scales: {

                x: {

                    grid: {

                        display: false

                    },

                    ticks: {

                        font: {

                            size: 9

                        }

                    }

                },

                y: {

                    beginAtZero: true,

                    suggestedMax: 1000,

                    ticks: {

                        font: {

                            size: 9

                        },

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

                    borderColor: '#ffffff',

                    borderWidth: 2

                }]

            },

            options: {

                ...chartOptions,

                cutout: '68%'

            }

        });

    }



    makeDoughnutChart(

        'quotationStatusChart',

        @json($quotationStatusChart['labels']),

        @json($quotationStatusChart['values']),

        'Quotation Status'

    );
</script>

@endsection
