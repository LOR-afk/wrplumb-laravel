@extends('admin.layouts.app')

@section('title', 'Job Orders - WRPlumb')
@section('topbar_title', 'Job Orders')
@section('topbar_subtitle', 'Track scheduled and ongoing service execution.')

@section('content')
<div class="panel">
    <div class="panel-header">
        <h5><i class="fas fa-clipboard-list me-2 text-primary"></i>All Job Orders</h5>
    </div>

    <div class="panel-body">
        @if (session('success'))
            <div class="alert alert-success mb-3">{{ session('success') }}</div>
        @endif

        @if (session('info'))
            <div class="alert alert-info mb-3">{{ session('info') }}</div>
        @endif

        @if ($jobOrders->count())
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Job Order No.</th>
                            <th>Client</th>
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
                                <td>{{ $jobOrder->quotationRequest->full_name ?? trim(($jobOrder->quotationRequest->first_name ?? '') . ' ' . ($jobOrder->quotationRequest->last_name ?? '')) }}</td>
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
                                <td>
                                    <span class="badge-soft {{ $jobOrder->status === 'completed' ? 'green' : ($jobOrder->status === 'in_progress' ? 'blue' : ($jobOrder->status === 'cancelled' ? 'gray' : 'orange')) }}">
                                        {{ ucfirst(str_replace('_', ' ', $jobOrder->status)) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.job-orders.show', $jobOrder) }}" class="btn btn-sm btn-outline-primary">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $jobOrders->links() }}
            </div>
        @else
            <div class="text-center py-5 text-muted">
                No job orders found.
            </div>
        @endif
    </div>
</div>
@endsection