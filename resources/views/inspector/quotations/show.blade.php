@extends('inspector.layouts.app')

@section('title', 'Request Details - WRPlumb')
@section('topbar_title', 'Request Details')
@section('topbar_subtitle', 'Review the request and update inspection progress.')

@section('content')
<div class="page-header">
    <h1>{{ $quotation->full_name }}</h1>
    <p>{{ $quotation->service_type }} | {{ ucfirst($quotation->service_category) }}</p>
</div>
<div class="panel mb-4">
    <div class="panel-header">
        <h5><i class="fas fa-timeline me-2 text-primary"></i>Request Timeline</h5>
    </div>
    <div class="panel-body">
        @include('partials.request-timeline', ['quotation' => $quotation])
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="panel mb-4">
            <div class="panel-header">
                <h5><i class="fas fa-circle-info me-2 text-primary"></i>Request Information</h5>
            </div>
            <div class="panel-body">
                <p><strong>Email:</strong> {{ $quotation->email }}</p>
                <p><strong>Phone:</strong> {{ $quotation->phone }}</p>
                <p><strong>Address:</strong> {{ $quotation->address }}</p>
                <p><strong>Preferred Date:</strong> {{ optional($quotation->preferred_date)->format('Y-m-d') ?? '—' }}</p>
                <p><strong>Problem Details:</strong> {{ $quotation->details }}</p>
                <p><strong>Admin Notes:</strong> {{ $quotation->admin_notes ?? '—' }}</p>
                <hr>

                <p><strong>Appointment Status:</strong>
                    @if ($quotation->appointment_status === 'pending')
                        <span class="badge-soft orange">Pending</span>
                    @elseif ($quotation->appointment_status === 'approved')
                        <span class="badge-soft green">Approved</span>
                    @elseif ($quotation->appointment_status === 'rescheduled')
                        <span class="badge-soft blue">Rescheduled</span>
                    @elseif ($quotation->appointment_status === 'cancelled')
                        <span class="badge-soft gray">Cancelled</span>
                    @else
                        <span class="badge-soft gray">{{ ucfirst(str_replace('_', ' ', $quotation->appointment_status ?? 'pending')) }}</span>
                    @endif
                </p>

                <p><strong>Appointment Date:</strong>
                    {{ optional($quotation->appointment_date)->format('Y-m-d') ?? 'Not yet scheduled' }}
                </p>

                <p><strong>Appointment Time:</strong>
                    {{ $quotation->appointment_time ? date('h:i A', strtotime($quotation->appointment_time)) : 'Not yet scheduled' }}
                </p>

                @if ($quotation->appointment_status === 'cancelled' && $quotation->cancel_reason)
                    <p><strong>Cancellation Reason:</strong> {{ $quotation->cancel_reason }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="panel">
            <div class="panel-header">
                <h5><i class="fas fa-pen-to-square me-2 text-primary"></i>Update Status</h5>
            </div>
            <div class="panel-body">
                <form method="POST" action="{{ route('inspector.quotations.update', $quotation) }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="assigned" @selected($quotation->status === 'assigned')>Assigned</option>
                            <option value="in_progress" @selected($quotation->status === 'in_progress')>In Progress</option>
                            <option value="completed" @selected($quotation->status === 'completed')>Completed</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Inspector Notes</label>
                        <textarea name="inspector_notes" class="form-control" rows="5" placeholder="Add your field notes here...">{{ old('inspector_notes', $quotation->inspector_notes) }}</textarea>
                    </div>

                    <button class="btn btn-primary w-100">
                        <i class="fas fa-floppy-disk me-2"></i>Save Update
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection