@extends('client.layouts.app')

@section('title', 'Client Dashboard - WRPlumb')
@section('topbar_title', 'Dashboard')
@section('topbar_subtitle', 'Home / Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/client/dashboard.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@endpush

@section('content')
@php
    $user = auth()->user();

    $activeRequests = $activeRequests ?? 0;
    $upcomingAppointments = $upcomingAppointments ?? 0;
    $openInvoices = $openInvoices ?? 0;
    $completedJobs = $completedJobs ?? 0;

    $ongoingRequests = $ongoingRequests ?? collect();
    $recentInvoices = $recentInvoices ?? collect();
    $recentActivities = $recentActivities ?? collect();
    $dueInvoice = $dueInvoice ?? null;
@endphp

<div class="client-dashboard">
    <section class="dashboard-hero">
        <div class="dashboard-hero-content">
            <span class="hero-label">Client Portal</span>

            <h1>
                Welcome back,
                {{ $user->first_name ?? $user->name ?? 'Client' }}
                <span class="wave">👋</span>
            </h1>

            <p>
                Here’s what’s happening with your projects and account today.
            </p>

            <div class="hero-actions">
                <a href="{{ route('client.requests.create') }}" class="hero-primary-btn">
                    <i class="fas fa-calendar-plus"></i>
                    Book a Service
                </a>

                <a href="{{ route('client.requests.index') }}" class="hero-secondary-btn">
                    View Requests
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <div class="dashboard-hero-visual">
            <div class="hero-circle hero-circle-one"></div>
            <div class="hero-circle hero-circle-two"></div>

            <div class="hero-house">
                <i class="fas fa-house-chimney"></i>
            </div>

            <div class="hero-city">
                <span></span>
                <span></span>
                <span></span>
                <span></span>
            </div>

            <div class="hero-van">
                <div class="van-body">
                    <div class="van-window"></div>

                    <div class="van-brand">
                        <strong>WR</strong>
                        <span>Plumb</span>
                    </div>
                </div>

                <div class="van-wheel wheel-left"></div>
                <div class="van-wheel wheel-right"></div>
            </div>
        </div>
    </section>

    <section class="dashboard-stats">
        <a href="{{ route('client.requests.index') }}" class="stat-card">
            <div class="stat-icon stat-icon-blue">
                <i class="fas fa-clipboard-list"></i>
            </div>

            <div class="stat-content">
                <span class="stat-label">Active Requests</span>
                <strong>{{ $activeRequests }}</strong>
                <small>View service progress</small>
            </div>
        </a>

        <a href="{{ route('client.job-orders.index') }}" class="stat-card">
            <div class="stat-icon stat-icon-purple">
                <i class="fas fa-calendar-check"></i>
            </div>

            <div class="stat-content">
                <span class="stat-label">Upcoming Appointments</span>
                <strong>{{ $upcomingAppointments }}</strong>
                <small>Scheduled service visits</small>
            </div>
        </a>

        <a href="{{ route('client.invoices.index') }}" class="stat-card">
            <div class="stat-icon stat-icon-orange">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>

            <div class="stat-content">
                <span class="stat-label">Open Invoices</span>
                <strong>{{ $openInvoices }}</strong>
                <small>Review billing details</small>
            </div>
        </a>

        <a href="{{ route('client.job-orders.index') }}" class="stat-card">
            <div class="stat-icon stat-icon-green">
                <i class="fas fa-circle-check"></i>
            </div>

            <div class="stat-content">
                <span class="stat-label">Completed Jobs</span>
                <strong>{{ $completedJobs }}</strong>
                <small>Successfully completed</small>
            </div>
        </a>
    </section>

    <section class="dashboard-main-grid">
        <div class="dashboard-left-column">
            <div class="dashboard-panel requests-panel">
                <div class="panel-header">
                    <div>
                        <span class="panel-eyebrow">Service monitoring</span>
                        <h2>Service Requests in Progress</h2>
                    </div>

                    <a href="{{ route('client.requests.index') }}" class="panel-link">
                        View all requests
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div class="request-progress-list">
                    @forelse($ongoingRequests->take(3) as $request)
                        @php
                            $status = strtolower($request->status ?? 'submitted');

                            $submittedComplete = true;
                            $assignedComplete = in_array($status, [
                                'assigned',
                                'scheduled',
                                'in progress',
                                'in_progress',
                                'completed',
                            ]);

                            $progressComplete = in_array($status, [
                                'in progress',
                                'in_progress',
                                'completed',
                            ]);

                            $completedComplete = $status === 'completed';
                        @endphp

                        <div class="request-progress-item">
                            <div class="request-service-icon">
                                <i class="fas fa-screwdriver-wrench"></i>
                            </div>

                            <div class="request-information">
                                <strong>
                                    {{ $request->service_type ?? $request->service_name ?? 'Service Request' }}
                                </strong>

                                <span>
                                    {{ $request->request_no ?? 'REQ-' . str_pad($request->id ?? 0, 5, '0', STR_PAD_LEFT) }}
                                    <span class="detail-dot"></span>
                                    {{ optional($request->created_at)->format('M d, Y') ?? 'Pending date' }}
                                </span>
                            </div>

                            <div class="request-stepper">
                                <div class="request-step completed">
                                    <span>
                                        <i class="fas fa-check"></i>
                                    </span>
                                    <small>Submitted</small>
                                </div>

                                <div class="step-line {{ $assignedComplete ? 'completed' : '' }}"></div>

                                <div class="request-step {{ $assignedComplete ? 'completed' : '' }}">
                                    <span>
                                        @if($assignedComplete)
                                            <i class="fas fa-check"></i>
                                        @endif
                                    </span>
                                    <small>Assigned</small>
                                </div>

                                <div class="step-line {{ $progressComplete ? 'completed' : '' }}"></div>

                                <div class="request-step {{ $progressComplete ? 'completed' : '' }}">
                                    <span>
                                        @if($progressComplete)
                                            <i class="fas fa-check"></i>
                                        @endif
                                    </span>
                                    <small>In Progress</small>
                                </div>

                                <div class="step-line {{ $completedComplete ? 'completed success' : '' }}"></div>

                                <div class="request-step {{ $completedComplete ? 'completed success' : '' }}">
                                    <span>
                                        @if($completedComplete)
                                            <i class="fas fa-check"></i>
                                        @endif
                                    </span>
                                    <small>Completed</small>
                                </div>
                            </div>

                            <div class="request-status">
                                <span class="status-badge status-{{ str_replace([' ', '_'], '-', $status) }}">
                                    {{ ucfirst(str_replace('_', ' ', $request->status ?? 'Submitted')) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="dashboard-empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-clipboard-check"></i>
                            </div>

                            <h3>No active service requests</h3>
                            <p>You currently have no service requests in progress.</p>

                            <a href="{{ route('client.requests.create') }}">
                                Book a Service
                            </a>
                        </div>
                    @endforelse
                </div>

                @if($ongoingRequests->count() > 0)
                    <div class="panel-footer">
                        <a href="{{ route('client.requests.index') }}">
                            View all requests
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                @endif
            </div>

            <div class="dashboard-panel recent-invoices-panel">
                <div class="panel-header">
                    <div>
                        <span class="panel-eyebrow">Billing overview</span>
                        <h2>Recent Invoices</h2>
                    </div>

                    <a href="{{ route('client.invoices.index') }}" class="panel-link">
                        View all invoices
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div class="dashboard-table-wrapper">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Amount</th>
                                <th>Due Date</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($recentInvoices->take(4) as $invoice)
                                @php
                                    $invoiceStatus = strtolower($invoice->status ?? 'unpaid');
                                    $dueSoon = $invoice->due_date
                                        && \Carbon\Carbon::parse($invoice->due_date)->isBetween(
                                            today(),
                                            today()->addDays(3)
                                        );
                                @endphp

                                <tr>
                                    <td>
                                        <strong>
                                            {{ $invoice->invoice_no ?? 'INV-' . str_pad($invoice->id ?? 0, 5, '0', STR_PAD_LEFT) }}
                                        </strong>
                                    </td>

                                    <td>
                                        {{ optional($invoice->created_at)->format('M d, Y') ?? '—' }}
                                    </td>

                                    <td>
                                        {{ $invoice->description ?? $invoice->quotation?->request?->service_type ?? 'Service Invoice' }}
                                    </td>

                                    <td>
                                        @if($dueSoon && $invoiceStatus !== 'paid')
                                            <span class="table-status due-soon">
                                                Due Soon
                                            </span>
                                        @else
                                            <span class="table-status {{ str_replace(' ', '-', $invoiceStatus) }}">
                                                {{ ucfirst($invoiceStatus) }}
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        ₱{{ number_format($invoice->total_amount ?? $invoice->amount ?? 0, 2) }}
                                    </td>

                                    <td>
                                        {{ $invoice->due_date
                                            ? \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y')
                                            : '—'
                                        }}
                                    </td>

                                    <td>
                                        <a href="{{ route('client.invoices.index') }}" class="table-action">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="table-empty-state">
                                            <i class="fas fa-file-invoice"></i>
                                            <span>No invoices available.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <aside class="dashboard-right-column">
            <div class="invoice-reminder-card {{ $dueInvoice ? '' : 'no-due-invoice' }}">
                <div class="invoice-reminder-header">
                    <div class="invoice-reminder-icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>

                    @if($dueInvoice)
                        <span class="due-badge">
                            Due Soon
                        </span>
                    @endif
                </div>

                @if($dueInvoice)
                    <div class="invoice-reminder-content">
                        <span class="reminder-label">Invoice Due Soon</span>

                        <h3>
                            {{ $dueInvoice->invoice_no ?? 'Invoice' }}
                        </h3>

                        <p>
                            Due on
                            {{ \Carbon\Carbon::parse($dueInvoice->due_date)->format('F d, Y') }}
                        </p>

                        <strong class="invoice-amount">
                            ₱{{ number_format($dueInvoice->total_amount ?? $dueInvoice->amount ?? 0, 2) }}
                        </strong>

                        <a href="{{ route('client.invoices.index') }}" class="invoice-button">
                            View Invoice
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                @else
                    <div class="invoice-reminder-content empty-reminder">
                        <span class="reminder-label">Invoice Status</span>
                        <h3>No payment due soon</h3>
                        <p>Your account currently has no upcoming payment deadline.</p>

                        <a href="{{ route('client.invoices.index') }}" class="invoice-button">
                            View Invoices
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                @endif
            </div>

            <div class="dashboard-panel recent-activity-panel">
                <div class="panel-header">
                    <div>
                        <span class="panel-eyebrow">Account updates</span>
                        <h2>Recent Activity</h2>
                    </div>
                </div>

                <div class="activity-list">
                    @forelse($recentActivities->take(5) as $activity)
                        @php
                            $activityType = $activity->type ?? 'system';

                            $activityIcon = match($activityType) {
                                'payment_due' => 'fa-file-invoice-dollar',
                                'payment' => 'fa-circle-check',
                                'request' => 'fa-screwdriver-wrench',
                                'appointment' => 'fa-calendar-check',
                                'quotation' => 'fa-file-lines',
                                default => 'fa-bell',
                            };
                        @endphp

                        <a href="#" class="activity-item">
                            <div class="activity-icon activity-icon-{{ $activityType }}">
                                <i class="fas {{ $activityIcon }}"></i>
                            </div>

                            <div class="activity-content">
                                <strong>{{ $activity->title ?? 'System update' }}</strong>
                                <span>{{ $activity->message ?? 'Your account has a new update.' }}</span>
                            </div>

                            <time>
                                {{ optional($activity->created_at)->diffForHumans() ?? 'Recently' }}
                            </time>
                        </a>
                    @empty
                        <div class="activity-empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-bell"></i>
                            </div>

                            <h3>No recent activity</h3>
                            <p>Your latest account updates will appear here.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="quick-actions-panel">
                <h2>Quick Actions</h2>

                <div class="quick-action-list">
                    <a href="{{ route('client.requests.create') }}" class="quick-action">
                        <span>
                            <i class="fas fa-screwdriver-wrench"></i>
                        </span>

                        <div>
                            <strong>Book a Service</strong>
                            <small>Submit a new request</small>
                        </div>

                        <i class="fas fa-chevron-right"></i>
                    </a>

                    <a href="{{ route('client.payments.index') }}" class="quick-action">
                        <span>
                            <i class="fas fa-credit-card"></i>
                        </span>

                        <div>
                            <strong>View Payments</strong>
                            <small>Check payment records</small>
                        </div>

                        <i class="fas fa-chevron-right"></i>
                    </a>

                    <a href="{{ route('client.support.index') }}" class="quick-action">
                        <span>
                            <i class="fas fa-headset"></i>
                        </span>

                        <div>
                            <strong>Contact Support</strong>
                            <small>Get customer assistance</small>
                        </div>

                        <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </div>
        </aside>
    </section>
</div>
@endsection