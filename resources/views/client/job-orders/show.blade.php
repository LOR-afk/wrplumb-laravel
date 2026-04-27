@extends('client.layouts.app')

@section('title', 'Job Order Details')

@section('content')
<div class="page-header-card mb-4">
    <h2 class="mb-1">Job Order Details</h2>
    <p class="text-muted mb-0">View your service execution details.</p>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="small text-muted">Job Order No.</div>
                <div class="fw-bold">{{ $jobOrder->job_order_no }}</div>
            </div>

            <div class="col-md-4">
                <div class="small text-muted">Status</div>
                <div class="fw-semibold text-uppercase">{{ str_replace('_', ' ', $jobOrder->status) }}</div>
            </div>

            <div class="col-md-4">
                <div class="small text-muted">Service Flow</div>
                <div class="fw-semibold text-uppercase">{{ str_replace('_', ' ', $jobOrder->service_flow ?? '—') }}</div>
            </div>

            <div class="col-md-6">
                <div class="small text-muted">Service Type</div>
                <div class="fw-semibold">{{ $jobOrder->service_type ?? '—' }}</div>
            </div>

            <div class="col-md-6">
                <div class="small text-muted">Assigned Worker</div>
                <div class="fw-semibold">{{ $jobOrder->worker->name ?? $jobOrder->worker->first_name ?? 'Not assigned' }}</div>
            </div>

            <div class="col-md-6">
                <div class="small text-muted">Schedule</div>
                <div class="fw-semibold">
                    @if ($jobOrder->scheduled_date)
                        {{ optional($jobOrder->scheduled_date)->format('Y-m-d') }}
                    @if ($jobOrder->scheduled_time)
                        • {{ \Carbon\Carbon::parse($jobOrder->scheduled_time)->format('h:i A') }}
                    @endif
                    @else
                        —
                    @endif
                </div>
            </div>

            <div class="col-md-6">
                <div class="small text-muted">Project Type</div>
                <div class="fw-semibold">{{ $jobOrder->project_type ?? '—' }}</div>
            </div>

            <div class="col-12">
                <div class="small text-muted">Scope of Work</div>
                <div class="fw-semibold">{{ $jobOrder->scope_of_work ?? '—' }}</div>
            </div>

            @if ($jobOrder->work_remarks)
                <div class="col-12">
                    <div class="small text-muted">Work Remarks</div>
                    <div class="fw-semibold">{{ $jobOrder->work_remarks }}</div>
                </div>
            @endif

            @if ($jobOrder->completion_notes)
                <div class="col-12">
                    <div class="small text-muted">Completion Notes</div>
                    <div class="fw-semibold">{{ $jobOrder->completion_notes }}</div>
                </div>
            @endif
        </div>

        <div class="mt-4">
            <a href="{{ route('client.job-orders.index') }}" class="btn btn-outline-secondary">
                Back to My Job Orders
            </a>
        </div>
    </div>
</div>
@endsection