@extends('client.layouts.app')

@section('title', 'My Job Orders')
@section('topbar_title', 'My Job Orders')
@section('topbar_subtitle', 'Track scheduled, ongoing, completed, and cancelled service work.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/client/job-orders-index.css') }}?v=20260818a">
@endpush

@section('content')
@php
    $jobOrderCollection = collect($jobOrders->items() ?? $jobOrders);

    $totalJobs = method_exists($jobOrders, 'total')
        ? $jobOrders->total()
        : $jobOrderCollection->count();

    $scheduledCount = $jobOrderCollection
        ->filter(fn ($jobOrder) => strtolower((string) $jobOrder->status) === 'scheduled')
        ->count();

    $inProgressCount = $jobOrderCollection
        ->filter(fn ($jobOrder) => in_array(strtolower((string) $jobOrder->status), ['in_progress', 'in-progress', 'ongoing']))
        ->count();

    $completedCount = $jobOrderCollection
        ->filter(fn ($jobOrder) => strtolower((string) $jobOrder->status) === 'completed')
        ->count();
@endphp

<div class="client-job-orders-page">


    <section class="job-orders-stats">
        <article>
            <span class="job-stat-icon blue"><i class="fas fa-clipboard-list"></i></span>
            <div>
                <small>Total Job Orders</small>
                <strong>{{ $totalJobs }}</strong>
            </div>
        </article>

        <article>
            <span class="job-stat-icon orange"><i class="fas fa-calendar-check"></i></span>
            <div>
                <small>Scheduled</small>
                <strong>{{ $scheduledCount }}</strong>
            </div>
        </article>

        <article>
            <span class="job-stat-icon violet"><i class="fas fa-play"></i></span>
            <div>
                <small>In Progress</small>
                <strong>{{ $inProgressCount }}</strong>
            </div>
        </article>

        <article>
            <span class="job-stat-icon green"><i class="fas fa-circle-check"></i></span>
            <div>
                <small>Completed</small>
                <strong>{{ $completedCount }}</strong>
            </div>
        </article>
    </section>

    <section class="job-orders-list-card">
        <div class="job-orders-list-head">
            <div>
                <span>Records</span>
                <h3>Job Order History</h3>
            </div>

            <small>
                {{ $totalJobs }}
                {{ $totalJobs === 1 ? 'job order' : 'job orders' }}
            </small>
        </div>

        @if ($jobOrders->count())
            <div class="job-orders-table-wrap">
                <table class="job-orders-table">
                    <thead>
                        <tr>
                            <th>Job Order</th>
                            <th>Service</th>
                            <th>Assigned Worker</th>
                            <th>Schedule</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($jobOrders as $jobOrder)
                            @php
                                $statusKey = strtolower((string) ($jobOrder->status ?? 'scheduled'));

                                $statusClass = match ($statusKey) {
                                    'scheduled' => 'blue',
                                    'in_progress', 'in-progress', 'ongoing' => 'orange',
                                    'completed' => 'green',
                                    'cancelled', 'canceled' => 'grey',
                                    default => 'gray',
                                };

                                $statusLabel = match ($statusKey) {
                                    'in_progress', 'in-progress', 'ongoing' => 'In Progress',
                                    'cancelled', 'canceled' => 'Cancelled',
                                    default => ucfirst(str_replace('_', ' ', $statusKey)),
                                };

                                $workerName = $jobOrder->worker->name
                                    ?? $jobOrder->worker->first_name
                                    ?? 'Not assigned';

                                $scheduleDate = $jobOrder->scheduled_date
                                    ? optional($jobOrder->scheduled_date)->format('M d, Y')
                                    : 'Not scheduled';

                                $scheduleTime = $jobOrder->scheduled_time
                                    ? \Carbon\Carbon::parse($jobOrder->scheduled_time)->format('h:i A')
                                    : null;
                            @endphp

                            <tr>
                                <td>
                                    <a
                                        href="{{ route('client.job-orders.show', $jobOrder) }}"
                                        class="job-order-number"
                                    >
                                        {{ $jobOrder->job_order_no }}
                                    </a>
                                </td>

                                <td>
                                    <div class="job-order-service">
                                        <strong>{{ $jobOrder->service_type ?? 'Service' }}</strong>

                                        @if (!empty($jobOrder->project_type))
                                            <span>{{ $jobOrder->project_type }}</span>
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    <div class="job-order-worker">
                                        <span class="job-order-worker-icon">
                                            <i class="fas fa-user"></i>
                                        </span>

                                        <div>
                                            <strong>{{ $workerName }}</strong>
                                            <span>{{ $workerName === 'Not assigned' ? 'Awaiting assignment' : 'Assigned worker' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="job-order-schedule">
                                        <strong>{{ $scheduleDate }}</strong>
                                        @if ($scheduleTime)
                                            <span>{{ $scheduleTime }}</span>
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    <span class="job-order-status {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <td class="text-end">
                                    <a
                                        href="{{ route('client.job-orders.show', $jobOrder) }}"
                                        class="job-order-view-btn"
                                    >
                                        <i class="fas fa-eye"></i>
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="job-orders-pagination">
                {{ $jobOrders->links() }}
            </div>
        @else
            <div class="job-orders-empty">
                <div class="job-orders-empty-icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>

                <strong>No job orders available yet</strong>
                <p>Your job orders will appear here once a service request reaches the execution stage.</p>
            </div>
        @endif
    </section>
</div>
@endsection