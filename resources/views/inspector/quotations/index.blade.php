@extends('inspector.layouts.app')

@section('title', 'Assigned Requests - WRPlumb')
@section('topbar_title', 'Assigned Requests')
@section('topbar_subtitle', 'Review requests assigned to you and open task details.')

@section('content')
<div class="page-header">
    <h1>Assigned Requests</h1>
    <p>Track the service requests assigned to you and review their status before field work.</p>
</div>

<div class="panel">
    <div class="panel-header">
        <h5><i class="fas fa-file-signature me-2 text-primary"></i>My Assigned Requests</h5>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Service</th>
                        <th>Preferred Date</th>
                        <th>Status</th>
                        <th width="120">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($quotations as $quotation)
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">{{ $quotation->full_name }}</div>
                                <div class="text-muted small">{{ $quotation->email }}</div>
                            </td>
                            <td>{{ $quotation->service_type }}</td>
                            <td>{{ optional($quotation->preferred_date)->format('Y-m-d') ?? '—' }}</td>
                            <td>
                                @if ($quotation->status === 'assigned')
                                    <span class="badge-soft orange">Assigned</span>
                                @elseif ($quotation->status === 'in_progress')
                                    <span class="badge-soft blue">In Progress</span>
                                @elseif ($quotation->status === 'completed')
                                    <span class="badge-soft green">Completed</span>
                                @else
                                    <span class="badge-soft gray">{{ ucfirst($quotation->status) }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('inspector.quotations.show', $quotation) }}" class="btn btn-sm btn-primary">
                                    Open
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center justify-content-center text-muted">
                                    <div class="mb-3" style="width:64px;height:64px;border-radius:18px;background:#eef6ff;color:#1d9bf0;display:flex;align-items:center;justify-content:center;font-size:24px;">
                                        <i class="fas fa-file-circle-check"></i>
                                    </div>
                                    <div class="fw-bold text-dark mb-1">No assigned requests yet</div>
                                    <div class="text-muted">New assigned service requests will appear here.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $quotations->links() }}
        </div>
    </div>
</div>
@endsection