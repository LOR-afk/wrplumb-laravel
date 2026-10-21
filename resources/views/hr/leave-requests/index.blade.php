@extends('hr.layouts.app')

@section('title', 'Inspector Leave Requests - WRPlumb')
@section('topbar_title', 'Inspector Leave Requests')
@section('topbar_subtitle', 'Review and approve or decline inspector leave requests.')

@section('content')
<div class="container-fluid py-3">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <small class="text-muted">Pending</small>
                    <h3 class="mb-0">{{ $summary['pending'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <small class="text-muted">Approved</small>
                    <h3 class="mb-0">{{ $summary['approved'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <small class="text-muted">Declined</small>
                    <h3 class="mb-0">{{ $summary['declined'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-1">Leave Requests</h5>
                <small class="text-muted">Approved dates are automatically marked On Leave in inspector availability.</small>
            </div>

            <form method="GET" class="d-flex gap-2">
                <select name="status" class="form-select">
                    <option value="pending" @selected($status === 'pending')>Pending</option>
                    <option value="approved" @selected($status === 'approved')>Approved</option>
                    <option value="declined" @selected($status === 'declined')>Declined</option>
                    <option value="all" @selected($status === 'all')>All</option>
                </select>
                <button class="btn btn-outline-primary" type="submit">Filter</button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Inspector</th>
                        <th>Leave Dates</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Reviewed By</th>
                        <th style="min-width: 300px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leaveRequests as $leaveRequest)
                        @php
                            $inspectorName = $leaveRequest->inspector?->name
                                ?: trim(
                                    ($leaveRequest->inspector?->first_name ?? '') . ' ' .
                                    ($leaveRequest->inspector?->last_name ?? '')
                                )
                                ?: 'Inspector';
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $inspectorName }}</strong>
                                <div class="small text-muted">{{ $leaveRequest->inspector?->email }}</div>
                            </td>
                            <td>
                                {{ $leaveRequest->start_date->format('M d, Y') }}
                                @if (!$leaveRequest->start_date->isSameDay($leaveRequest->end_date))
                                    <br>
                                    <span class="text-muted">to {{ $leaveRequest->end_date->format('M d, Y') }}</span>
                                @endif
                            </td>
                            <td style="max-width: 320px;">{{ $leaveRequest->reason }}</td>
                            <td>
                                @if ($leaveRequest->status === 'approved')
                                    <span class="badge text-bg-success">Approved</span>
                                @elseif ($leaveRequest->status === 'declined')
                                    <span class="badge text-bg-danger">Declined</span>
                                @else
                                    <span class="badge text-bg-warning">Pending</span>
                                @endif
                            </td>
                            <td>
                                {{ $leaveRequest->reviewedBy?->name ?? '—' }}
                                @if ($leaveRequest->reviewed_at)
                                    <div class="small text-muted">
                                        {{ $leaveRequest->reviewed_at->format('M d, Y h:i A') }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if ($leaveRequest->status === 'pending')
                                    <form
                                        method="POST"
                                        action="{{ route('hr.leave-requests.review', $leaveRequest) }}"
                                        class="d-flex flex-column gap-2"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <textarea
                                            name="review_notes"
                                            class="form-control form-control-sm"
                                            rows="2"
                                            maxlength="1000"
                                            placeholder="Review notes (optional)"
                                        ></textarea>

                                        <div class="d-flex gap-2">
                                            <button
                                                type="submit"
                                                name="decision"
                                                value="approved"
                                                class="btn btn-success btn-sm"
                                            >
                                                Approve
                                            </button>

                                            <button
                                                type="submit"
                                                name="decision"
                                                value="declined"
                                                class="btn btn-outline-danger btn-sm"
                                            >
                                                Decline
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    <span class="small text-muted">
                                        {{ $leaveRequest->review_notes ?: 'No review notes.' }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                No leave requests found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($leaveRequests->hasPages())
            <div class="card-footer bg-white">
                {{ $leaveRequests->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
