@extends('admin.layouts.app')

@section('title', 'Job Order Details - WRPlumb')
@section('topbar_title', 'Job Order Details')
@section('topbar_subtitle', 'Review the service execution record.')

@section('content')
<div class="panel">
    <div class="panel-header">
        <h5><i class="fas fa-clipboard-check me-2 text-primary"></i>Job Order Details</h5>
    </div>

    <div class="panel-body">
        @if (session('success'))
            <div class="alert alert-success mb-3">{{ session('success') }}</div>
        @endif

        @if (session('info'))
            <div class="alert alert-info mb-3">{{ session('info') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger mb-3">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4 mb-4">
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
                        <div class="small text-muted">Client</div>
                        <div class="fw-semibold">{{ $jobOrder->quotationRequest->full_name ?? trim(($jobOrder->quotationRequest->first_name ?? '') . ' ' . ($jobOrder->quotationRequest->last_name ?? '')) }}</div>
                    </div>

                    <div class="col-md-6">
                        <div class="small text-muted">Assigned Worker</div>
                        <div class="fw-semibold">{{ $jobOrder->worker->name ?? $jobOrder->worker->first_name ?? 'Not assigned' }}</div>
                    </div>

                    <div class="col-md-4">
                        <div class="small text-muted">Service Type</div>
                        <div class="fw-semibold">{{ $jobOrder->service_type ?? '—' }}</div>
                    </div>

                    <div class="col-md-4">
                        <div class="small text-muted">Project Type</div>
                        <div class="fw-semibold">{{ $jobOrder->project_type ?? '—' }}</div>
                    </div>

                    <div class="col-md-4">
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

                    <div class="col-md-4">
                        <div class="small text-muted">Started At</div>
                        <div class="fw-semibold">{{ optional($jobOrder->started_at)->format('Y-m-d h:i A') ?? '—' }}</div>
                    </div>

                    <div class="col-md-4">
                        <div class="small text-muted">Completed At</div>
                        <div class="fw-semibold">{{ optional($jobOrder->completed_at)->format('Y-m-d h:i A') ?? '—' }}</div>
                    </div>

                    <div class="col-md-4">
                        <div class="small text-muted">Cancelled At</div>
                        <div class="fw-semibold">{{ optional($jobOrder->cancelled_at)->format('Y-m-d h:i A') ?? '—' }}</div>
                    </div>

                    <div class="col-12">
                        <div class="small text-muted">Scope of Work</div>
                        <div class="fw-semibold">{{ $jobOrder->scope_of_work ?? '—' }}</div>
                    </div>

                    @if ($jobOrder->admin_notes)
                        <div class="col-12">
                            <div class="small text-muted">Admin Notes</div>
                            <div class="fw-semibold">{{ $jobOrder->admin_notes }}</div>
                        </div>
                    @endif

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

                <div class="mt-4 d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.job-orders.index') }}" class="btn btn-outline-secondary">
                        Back to Job Orders
                    </a>

                    <a href="{{ route('admin.quotations.index') }}" class="btn btn-outline-primary">
                        Back to Requests
                    </a>

                    @if ($jobOrder->status === 'scheduled')
                        <form method="POST" action="{{ route('admin.job-orders.start', $jobOrder) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-primary">Mark In Progress</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Update Remarks</h6>

                        <form method="POST" action="{{ route('admin.job-orders.update-remarks', $jobOrder) }}">
                            @csrf
                            @method('PATCH')

                            <div class="mb-3">
                                <label class="form-label">Work Remarks</label>
                                <textarea name="work_remarks" class="form-control" rows="4">{{ old('work_remarks', $jobOrder->work_remarks) }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Admin Notes</label>
                                <textarea name="admin_notes" class="form-control" rows="4">{{ old('admin_notes', $jobOrder->admin_notes) }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-outline-primary">Save Remarks</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Complete Job Order</h6>

                        <form method="POST" action="{{ route('admin.job-orders.complete', $jobOrder) }}">
                            @csrf
                            @method('PATCH')

                            <div class="mb-3">
                                <label class="form-label">Completion Notes</label>
                                <textarea name="completion_notes" class="form-control" rows="4">{{ old('completion_notes', $jobOrder->completion_notes) }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-success">Mark Completed</button>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3 text-danger">Cancel Job Order</h6>

                        <form method="POST" action="{{ route('admin.job-orders.cancel', $jobOrder) }}">
                            @csrf
                            @method('PATCH')

                            <div class="mb-3">
                                <label class="form-label">Cancellation Notes</label>
                                <textarea name="completion_notes" class="form-control" rows="4">{{ old('completion_notes') }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-danger">Cancel Job Order</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection