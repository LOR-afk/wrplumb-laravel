@extends('client.layouts.app')

@section('title', 'Client Dashboard - WRPlumb')
@section('topbar_title', 'Dashboard')
@section('topbar_subtitle', 'Track your requests, schedules, invoices, and account activity.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/client/dashboard.css') }}?v=client-dashboard-modern-01">
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
    <section class="dashboard-greeting">
        <div class="dashboard-greeting-copy">
            <span class="greeting-label">Good day,</span>

            <h2>{{ $user->first_name ?? $user->name ?? 'Client' }}!</h2>

            <p>
                Here is a quick overview of your service requests, schedules, and billing activity.
            </p>

            <div class="greeting-meta">
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

        <div class="dashboard-greeting-actions">
            <a href="{{ route('client.requests.create') }}" class="greeting-primary-btn">
                <i class="fas fa-plus"></i>
                Book a Service
            </a>

            <a href="{{ route('client.requests.index') }}" class="greeting-secondary-btn">
                My Requests
                <i class="fas fa-arrow-right"></i>
            </a>

            <span class="greeting-art" aria-hidden="true">
                <i class="fas fa-faucet-drip"></i>
            </span>
        </div>
    </section>

    <section class="dashboard-stats">
        <a href="{{ route('client.requests.index') }}" class="stat-card">
            <div>
                <span class="stat-label">Active Requests</span>
                <strong>{{ $activeRequests }}</strong>
                <small>Requests currently being processed</small>
            </div>

            <span class="stat-icon stat-icon-blue">
                <i class="fas fa-clipboard-list"></i>
            </span>
        </a>

        <a href="{{ route('client.job-orders.index') }}" class="stat-card">
            <div>
                <span class="stat-label">Appointments</span>
                <strong>{{ $upcomingAppointments }}</strong>
                <small>Upcoming scheduled visits</small>
            </div>

            <span class="stat-icon stat-icon-purple">
                <i class="fas fa-calendar-check"></i>
            </span>
        </a>

        <a href="{{ route('client.invoices.index') }}" class="stat-card">
            <div>
                <span class="stat-label">Open Invoices</span>
                <strong>{{ $openInvoices }}</strong>
                <small>Invoices requiring review</small>
            </div>

            <span class="stat-icon stat-icon-orange">
                <i class="fas fa-file-invoice-dollar"></i>
            </span>
        </a>

        <a href="{{ route('client.job-orders.index') }}" class="stat-card">
            <div>
                <span class="stat-label">Completed Jobs</span>
                <strong>{{ $completedJobs }}</strong>
                <small>Finished service requests</small>
            </div>

            <span class="stat-icon stat-icon-green">
                <i class="fas fa-circle-check"></i>
            </span>
        </a>
    </section>

    <section class="dashboard-main-grid">
        <div class="dashboard-left-column">
            <article class="dashboard-panel requests-panel">
                <header class="panel-header">
                    <div>
                        <h2>Ongoing Service Requests</h2>
                        <p>Current requests and their progress.</p>
                    </div>

                    <a href="{{ route('client.requests.index') }}" class="panel-link">
                        View all
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </header>

                <div class="request-progress-list">
                    @forelse($ongoingRequests->take(3) as $request)
                        @php
                            $status = strtolower(str_replace('-', '_', $request->status ?? 'submitted'));

                            $currentStep = match ($status) {
                                'assigned', 'scheduled' => 2,
                                'in progress', 'in_progress', 'ongoing' => 3,
                                'completed', 'done' => 4,
                                default => 1,
                            };

                            $isFinished = in_array($status, ['completed', 'done'], true);
                        @endphp

                        <div
                            class="request-progress-item"
                            data-client-search="{{ strtolower(
                                ($request->service_type ?? $request->service_name ?? '') . ' ' .
                                ($request->request_no ?? '') . ' ' .
                                ($request->status ?? '')
                            ) }}"
                        >
                            <span class="request-service-icon">
                                <i class="fas fa-screwdriver-wrench"></i>
                            </span>

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
                                @foreach ([
                                    1 => 'Submitted',
                                    2 => 'Assigned',
                                    3 => 'In Progress',
                                    4 => 'Completed',
                                ] as $stepNumber => $stepLabel)
                                    @php
                                        $stepClass = $isFinished || $stepNumber < $currentStep
                                            ? 'done'
                                            : ($stepNumber === $currentStep ? 'current' : 'future');
                                    @endphp

                                    <div class="request-step {{ $stepClass }}">
                                        <span>
                                            @if($stepClass === 'done')
                                                <i class="fas fa-check"></i>
                                            @endif
                                        </span>
                                        <small>{{ $stepLabel }}</small>
                                    </div>

                                    @if($stepNumber < 4)
                                        <div class="step-line {{ $isFinished || $stepNumber < $currentStep ? 'done' : 'future' }}"></div>
                                    @endif
                                @endforeach
                            </div>

                            <span class="status-badge status-{{ str_replace([' ', '_'], '-', $status) }}">
                                {{ ucfirst(str_replace('_', ' ', $request->status ?? 'Submitted')) }}
                            </span>
                        </div>
                    @empty
                        <div class="dashboard-empty-state">
                            <span class="empty-state-icon">
                                <i class="fas fa-clipboard-check"></i>
                            </span>

                            <h3>No active service requests</h3>
                            <p>You currently have no service requests in progress.</p>

                            <a href="{{ route('client.requests.create') }}">
                                Book a Service
                            </a>
                        </div>
                    @endforelse
                </div>
            </article>

            <article class="dashboard-panel recent-invoices-panel">
                <header class="panel-header">
                    <div>
                        <h2>Recent Invoices</h2>
                        <p>Your latest billing records.</p>
                    </div>

                    <a href="{{ route('client.invoices.index') }}" class="panel-link">
                        View all
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </header>

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

                                <tr
                                    data-client-search="{{ strtolower(
                                        ($invoice->invoice_no ?? '') . ' ' .
                                        ($invoice->description ?? '') . ' ' .
                                        ($invoice->status ?? '')
                                    ) }}"
                                >
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
                                            <span class="table-status due-soon">Due Soon</span>
                                        @else
                                            <span class="table-status {{ str_replace(' ', '-', $invoiceStatus) }}">
                                                {{ ucfirst($invoiceStatus) }}
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        PHP {{ number_format((float) ($invoice->total_amount ?? $invoice->amount ?? 0), 2) }}
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
            </article>
        </div>

        <aside class="dashboard-right-column">
            <article class="billing-summary-card {{ $dueInvoice ? 'has-due-invoice' : '' }}">
                <header>
                    <span class="billing-icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </span>

                    <div>
                        <h2>Billing</h2>
                        <p>{{ $dueInvoice ? 'Upcoming payment' : 'Account status' }}</p>
                    </div>
                </header>

                @if($dueInvoice)
                    <span class="billing-label">Invoice Due Soon</span>

                    <strong class="billing-reference">
                        {{ $dueInvoice->invoice_no ?? 'Invoice' }}
                    </strong>

                    <span class="billing-date">
                        Due {{ \Carbon\Carbon::parse($dueInvoice->due_date)->format('M d, Y') }}
                    </span>

                    <strong class="billing-amount">
                        PHP {{ number_format((float) ($dueInvoice->total_amount ?? $dueInvoice->amount ?? 0), 2) }}
                    </strong>
                @else
                    <span class="billing-label">Payment Status</span>
                    <strong class="billing-reference">No payment due soon</strong>
                    <span class="billing-date">Your account has no upcoming payment deadline.</span>
                @endif

                <a href="{{ route('client.invoices.index') }}" class="billing-action">
                    View Invoices
                    <i class="fas fa-arrow-right"></i>
                </a>
            </article>

            <article class="dashboard-panel recent-activity-panel">
                <header class="panel-header">
                    <div>
                        <h2>Recent Activity</h2>
                        <p>Latest account updates.</p>
                    </div>
                </header>

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

                        <a
                            href="#"
                            class="activity-item"
                            data-client-search="{{ strtolower(
                                ($activity->title ?? '') . ' ' .
                                ($activity->message ?? '') . ' ' .
                                $activityType
                            ) }}"
                        >
                            <span class="activity-icon activity-icon-{{ $activityType }}">
                                <i class="fas {{ $activityIcon }}"></i>
                            </span>

                            <span class="activity-content">
                                <strong>{{ $activity->title ?? 'System update' }}</strong>
                                <span>{{ $activity->message ?? 'Your account has a new update.' }}</span>
                            </span>

                            <time>
                                {{ optional($activity->created_at)->diffForHumans() ?? 'Recently' }}
                            </time>
                        </a>
                    @empty
                        <div class="activity-empty-state">
                            <span class="empty-state-icon">
                                <i class="fas fa-bell"></i>
                            </span>

                            <h3>No recent activity</h3>
                            <p>Your latest account updates will appear here.</p>
                        </div>
                    @endforelse
                </div>
            </article>

            <article class="dashboard-panel quick-actions-panel">
                <header class="panel-header">
                    <div>
                        <h2>Quick Actions</h2>
                        <p>Common client tasks.</p>
                    </div>
                </header>

                <div class="quick-action-list">
                    <a href="{{ route('client.requests.create') }}" class="quick-action">
                        <span><i class="fas fa-screwdriver-wrench"></i></span>
                        <strong>Book a Service</strong>
                        <i class="fas fa-chevron-right"></i>
                    </a>

                    <a href="{{ route('client.payments.index') }}" class="quick-action">
                        <span><i class="fas fa-credit-card"></i></span>
                        <strong>Payments</strong>
                        <i class="fas fa-chevron-right"></i>
                    </a>

                    <button
                        type="button"
                        class="quick-action"
                        id="clientDashboardSupportButton"
                    >
                        <span><i class="fas fa-headset"></i></span>
                        <strong>Customer Support</strong>
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </article>
        </aside>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const button = document.getElementById('clientDashboardSupportButton');
    const supportToggle = document.getElementById('clientSupportToggle');

    button?.addEventListener('click', function () {
        supportToggle?.click();
    });
});
</script>
@endpush
