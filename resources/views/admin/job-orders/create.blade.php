@extends('admin.layouts.app')

@section('title', 'Create Job Order - WRPlumb')
@section('topbar_title', 'Create Job Order')
@section('topbar_subtitle', 'Create a service execution record from a request.')

@section('content')
<div class="panel">
    <div class="panel-header">
        <h5><i class="fas fa-plus-circle me-2 text-primary"></i>Create Job Order</h5>
    </div>

    <div class="panel-body">
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
                <h6 class="fw-bold mb-3">Request Summary</h6>

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="small text-muted">Client</div>
                        <div class="fw-semibold">{{ $quotation->full_name ?? trim(($quotation->first_name ?? '') . ' ' . ($quotation->last_name ?? '')) }}</div>
                    </div>

                    <div class="col-md-4">
                        <div class="small text-muted">Service Type</div>
                        <div class="fw-semibold">{{ $quotation->service_type }}</div>
                    </div>

                    <div class="col-md-4">
                        <div class="small text-muted">Flow</div>
                        <div class="fw-semibold text-uppercase">{{ str_replace('_', ' ', $quotation->service_flow ?? 'inspection_required') }}</div>
                    </div>

                    <div class="col-md-6">
                        <div class="small text-muted">Preferred / Current Schedule</div>
                        <div class="fw-semibold">
                            @if ($quotation->appointment_date || $quotation->preferred_date)
                                {{ optional($quotation->appointment_date ?? $quotation->preferred_date)->format('Y-m-d') }}
                                @if ($quotation->appointment_time || $quotation->preferred_time)
                                    • {{ $quotation->appointment_time ?? $quotation->preferred_time }}
                                @endif
                            @else
                                —
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="small text-muted">Assigned Worker</div>
                        <div class="fw-semibold">{{ $quotation->worker->name ?? $quotation->worker->first_name ?? 'Not assigned' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.job-orders.store') }}">
            @csrf

            <input type="hidden" name="quotation_request_id" value="{{ $quotation->id }}">

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">Job Order Details</h6>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Assigned Worker</label>
                                    <select name="worker_id" class="form-select">
                                        <option value="">Use current assigned worker</option>
                                        @if($quotation->worker)
                                            <option value="{{ $quotation->worker->id }}" selected>
                                                {{ $quotation->worker->name ?? $quotation->worker->first_name }}
                                            </option>
                                        @endif
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Scheduled Date</label>
                                    <input
                                        type="date"
                                        name="scheduled_date"
                                        class="form-control"
                                        value="{{ old('scheduled_date', optional($quotation->appointment_date ?? $quotation->preferred_date)->format('Y-m-d')) }}"
                                    >
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Scheduled Time</label>
                                    <input
                                        type="text"
                                        name="scheduled_time"
                                        class="form-control"
                                        value="{{ old('scheduled_time', $quotation->appointment_time ?? $quotation->preferred_time) }}"
                                        placeholder="e.g. 03:30 PM"
                                    >
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Scope of Work</label>
                                    <textarea
                                        name="scope_of_work"
                                        class="form-control"
                                        rows="4"
                                    >{{ old('scope_of_work', $quotation->details) }}</textarea>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Admin Notes</label>
                                    <textarea
                                        name="admin_notes"
                                        class="form-control"
                                        rows="3"
                                    >{{ old('admin_notes', $quotation->admin_notes) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">Create Action</h6>
                            <p class="text-muted small mb-3">
                                This will create a job order and mark the service execution as scheduled.
                            </p>

                            <div class="d-grid gap-2">
                                <button class="btn btn-primary">
                                    Create Job Order
                                </button>

                                <a href="{{ route('admin.quotations.index') }}" class="btn btn-outline-secondary">
                                    Back to Requests
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection