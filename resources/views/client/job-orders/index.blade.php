@extends('client.layouts.app')

@section('title', 'My Job Orders')

@section('content')
<div class="page-header-card mb-4">
    <h2 class="mb-1">My Job Orders</h2>
    <p class="text-muted mb-0">Track the status of your scheduled service work.</p>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        @if ($jobOrders->count())
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Job Order No.</th>
                            <th>Service Type</th>
                            <th>Assigned Worker</th>
                            <th>Schedule</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($jobOrders as $jobOrder)
                            <tr>
                                <td class="fw-bold">{{ $jobOrder->job_order_no }}</td>
                                <td>{{ $jobOrder->service_type ?? '—' }}</td>
                                <td>{{ $jobOrder->worker->name ?? $jobOrder->worker->first_name ?? 'Not assigned' }}</td>
                                <td>
                                    @if ($jobOrder->scheduled_date)
                                        {{ optional($jobOrder->scheduled_date)->format('Y-m-d') }}
                                        @if ($jobOrder->scheduled_time)
                                            • {{ $jobOrder->scheduled_time }}
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-uppercase">{{ str_replace('_', ' ', $jobOrder->status) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('client.job-orders.show', $jobOrder) }}" class="btn btn-sm btn-outline-primary">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-3">
                {{ $jobOrders->links() }}
            </div>
        @else
            <div class="text-center py-5 text-muted">
                No job orders available yet.
            </div>
        @endif
    </div>
</div>
@endsection