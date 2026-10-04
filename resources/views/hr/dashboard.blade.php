@extends('hr.layouts.app')



@section('title', 'HR Dashboard - WRPlumb')

@section('topbar_title', 'Dashboard')

@section('topbar_subtitle', 'Monitor HR support operations, finance workflows, and routed client concerns.')



@push('styles')

    <link rel="stylesheet" href="{{ asset('css/hr/dashboard.css') }}?v=hr-dashboard-readable-03">

@endpush



@section('content')

@php

    $hrName = auth()->user()->first_name ?? 'HR';

@endphp



<div class="hr-dashboard-page">

    <section class="hr-greeting">

        <div class="hr-greeting-copy">

            <span class="hr-greeting-label">Good day,</span>

            <h2>{{ $hrName }}!</h2>



            <p>

                Here is a quick overview of routed concerns, HR workload, and current support activity.

            </p>



            <div class="hr-greeting-meta">

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



        <div class="hr-greeting-metrics">

            <div class="hr-mini-metric">

                <span>Avg. Response</span>

                <strong>{{ $averageResponseTime ?? '—' }}</strong>

            </div>



            <div class="hr-mini-metric">

                <span>SLA Compliance</span>

                <strong class="success">{{ $slaCompliance ?? '—' }}</strong>

            </div>



            <span class="hr-greeting-art" aria-hidden="true">

                <i class="fas fa-headset"></i>

            </span>

        </div>

    </section>



    <section class="hr-kpi-grid">

        <article class="hr-kpi-card">

            <div>

                <span class="hr-kpi-label">Total Quotations</span>

                <strong>{{ number_format($totalQuotations ?? 0) }}</strong>

                <small>Current quotation records</small>

            </div>



            <span class="hr-kpi-icon blue">

                <i class="fas fa-file-invoice-dollar"></i>

            </span>

        </article>



        <article class="hr-kpi-card">

            <div>

                <span class="hr-kpi-label">Sent Quotations</span>

                <strong>{{ number_format($sentQuotations ?? 0) }}</strong>

                <small>Waiting for client acceptance</small>

            </div>



            <span class="hr-kpi-icon orange">

                <i class="fas fa-paper-plane"></i>

            </span>

        </article>



        <article class="hr-kpi-card">

            <div>

                <span class="hr-kpi-label">Accepted Quotations</span>

                <strong>{{ number_format($acceptedQuotations ?? 0) }}</strong>

                <small>Accepted by clients</small>

            </div>



            <span class="hr-kpi-icon green">

                <i class="fas fa-circle-check"></i>

            </span>

        </article>



        <article class="hr-kpi-card">

            <div>

                <span class="hr-kpi-label">Invoice Ready</span>

                <strong>{{ number_format($invoiceReadyQuotations ?? 0) }}</strong>

                <small>Accepted quotations with invoice</small>

            </div>



            <span class="hr-kpi-icon violet">

                <i class="fas fa-file-invoice"></i>

            </span>

        </article>

    </section>



    <section class="hr-main-grid">

        <div class="hr-left-column">

            <article class="hr-panel">

                <header class="hr-panel-header">

                    <div>

                        <h5>Recent Support Activity</h5>

                        <p>Latest routed or updated conversations.</p>

                    </div>



                    <a href="{{ route('hr.support.index') }}" class="hr-panel-link">

                        View all

                        <i class="fas fa-chevron-right"></i>

                    </a>

                </header>



                <div class="hr-activity-list">

                    @forelse ($recentConversations ?? [] as $conversation)

                        @php

                            $clientName = $conversation->client->name

                                ?? trim(

                                    ($conversation->client->first_name ?? '') . ' ' .

                                    ($conversation->client->last_name ?? '')

                                )

                                ?: 'Client';



                            $initials = collect(explode(' ', $clientName))

                                ->filter()

                                ->take(2)

                                ->map(fn ($part) => strtoupper(substr($part, 0, 1)))

                                ->implode('') ?: 'C';



                            $statusKey = $conversation->status ?? 'open';

                        @endphp



                        <a

                            href="{{ route('hr.support.show', $conversation) }}"

                            class="hr-activity-item"

                            data-hr-search="{{ strtolower(

                                $clientName . ' ' .

                                ($conversation->latest_message ?? '') . ' ' .

                                $statusKey

                            ) }}"

                        >

                            <span class="hr-avatar">{{ $initials }}</span>



                            <span class="hr-activity-main">

                                <strong>{{ $clientName }}</strong>

                                <small>

                                    {{ \Illuminate\Support\Str::limit(

                                        $conversation->latest_message ?? 'Support conversation update',

                                        58

                                    ) }}

                                </small>

                            </span>



                            <span class="hr-status-chip {{ $statusKey }}">

                                {{ ucwords(str_replace('_', ' ', $statusKey)) }}

                            </span>



                            <time>{{ optional($conversation->updated_at)->diffForHumans() }}</time>

                        </a>

                    @empty

                        <div class="hr-empty-state">

                            <span><i class="fas fa-comments"></i></span>

                            <strong>No recent support activity</strong>

                            <small>Routed HR conversations will appear here.</small>

                        </div>

                    @endforelse

                </div>

            </article>



            <article class="hr-panel">

                <header class="hr-panel-header">

                    <div>

                        <h5>HR Workflow Summary</h5>

                        <p>Support queue status for this period.</p>

                    </div>



                    <span class="hr-period-pill">This Month</span>

                </header>



                <div class="hr-workflow-list">

                    <div class="hr-workflow-row">

                        <span class="blue"><i class="fas fa-message"></i></span>

                        <div>

                            <strong>Conversations Received</strong>

                            <small>Total support conversations received</small>

                        </div>

                        <b>{{ $receivedCount ?? ($openCount + $resolvedCount) }}</b>

                    </div>



                    <div class="hr-workflow-row">

                        <span class="orange"><i class="fas fa-share"></i></span>

                        <div>

                            <strong>Routed to HR</strong>

                            <small>Forwarded from the support workflow</small>

                        </div>

                        <b>{{ $routedCount }}</b>

                    </div>



                    <div class="hr-workflow-row">

                        <span class="green"><i class="fas fa-circle-check"></i></span>

                        <div>

                            <strong>Resolved</strong>

                            <small>Conversations resolved by HR</small>

                        </div>

                        <b>{{ $resolvedCount }}</b>

                    </div>



                    <div class="hr-workflow-row">

                        <span class="violet"><i class="fas fa-clock"></i></span>

                        <div>

                            <strong>Pending</strong>

                            <small>Awaiting action or follow-up</small>

                        </div>

                        <b>{{ $pendingFollowups ?? 0 }}</b>

                    </div>

                </div>

            </article>

        </div>



        <aside class="hr-right-column">

            <article class="hr-panel">

                <header class="hr-panel-header">

                    <div>

                        <h5>Quick Actions</h5>

                        <p>Common HR workflows.</p>

                    </div>

                </header>



                <div class="hr-action-list">

                    <a href="{{ route('hr.support.index') }}" class="hr-action-card">

                        <span><i class="fas fa-comments"></i></span>

                        <strong>Support Queue</strong>

                        <i class="fas fa-chevron-right"></i>

                    </a>



                    <a href="{{ route('hr.inspection-reports.index') }}" class="hr-action-card">

                        <span><i class="fas fa-clipboard-check"></i></span>

                        <strong>Inspection Reports</strong>

                        <i class="fas fa-chevron-right"></i>

                    </a>



                    <a href="{{ route('hr.quotations.index') }}" class="hr-action-card">

                        <span><i class="fas fa-file-invoice-dollar"></i></span>

                        <strong>Quotations</strong>

                        <i class="fas fa-chevron-right"></i>

                    </a>



                    <a href="{{ route('hr.invoices.index') }}" class="hr-action-card">

                        <span><i class="fas fa-file-invoice"></i></span>

                        <strong>Invoices</strong>

                        <i class="fas fa-chevron-right"></i>

                    </a>



                    <a href="{{ route('hr.reports.index') }}" class="hr-action-card">

                        <span><i class="fas fa-chart-column"></i></span>

                        <strong>Reports</strong>

                        <i class="fas fa-chevron-right"></i>

                    </a>

                </div>

            </article>



            <article class="hr-panel">

                <header class="hr-panel-header">

                    <div>

                        <h5>Finance Tools</h5>

                        <p>Billing and payment records.</p>

                    </div>

                </header>



                <div class="hr-finance-links">

                    <a href="{{ route('hr.contracts.index') }}">

                        <i class="fas fa-file-contract"></i>

                        <span>Contracts</span>

                    </a>



                    <a href="{{ route('hr.payments.index') }}">

                        <i class="fas fa-credit-card"></i>

                        <span>Payments</span>

                    </a>



                    <a href="{{ route('hr.receipts.index') }}">

                        <i class="fas fa-receipt"></i>

                        <span>Receipts</span>

                    </a>



                    <a href="{{ route('hr.quotations.archived') }}">

                        <i class="fas fa-box-archive"></i>

                        <span>Archived</span>

                    </a>

                </div>

            </article>

        </aside>

    </section>

</div>

@endsection
