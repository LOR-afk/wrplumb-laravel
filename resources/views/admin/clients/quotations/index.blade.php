@extends('admin.layouts.app')

@section('title', 'Quotation Requests - WRPlumb')
@section('topbar_title', 'Quotation Requests')
@section('topbar_subtitle', 'Review submitted requests and assign the appropriate worker or inspector.')

@section('content')
<div class="page-header">
    <h1>Free Quotation Requests</h1>
    <p>Monitor requests from the landing page and assign responsible personnel.</p>
</div>

<div class="panel">
    <div class="panel-header">
        <h5><i class="fas fa-file-signature me-2 text-primary"></i>Submitted Requests</h5>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Category</th>
                        <th>Service Type</th>
                        <th>Preferred Date</th>
                        <th>Details</th>
                        <th>Status</th>
                        <th>Assigned</th>
                        <th width="260">Assign Personnel</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($quotations as $quotation)
                        <tr>
                            <td>
                                <strong>{{ $quotation->full_name }}</strong><br>
                                <small class="text-muted">{{ $quotation->email }} | {{ $quotation->phone }}</small>
                            </td>
                            <td>{{ ucfirst($quotation->service_category) }}</td>
                            <td>{{ $quotation->service_type }}</td>
                            <td>{{ optional($quotation->preferred_date)->format('Y-m-d') ?? '—' }}</td>
                            <td style="max-width:220px;">{{ $quotation->details }}</td>
                            <td>
                                @if ($quotation->status === 'pending')
                                    <span class="badge-soft badge-pending">Pending</span>
                                @elseif ($quotation->status === 'assigned')
                                    <span class="badge-soft badge-assigned">Assigned</span>
                                @else
                                    <span class="badge-soft badge-default">{{ ucfirst($quotation->status) }}</span>
                                @endif
                            </td>
                            <td>{{ $quotation->worker?->name ?? 'Not assigned' }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.quotations.assign-worker', $quotation) }}">
                                    @csrf
                                    <select name="worker_id" class="form-select form-select-sm mb-2" required>
                                        <option value="">Select personnel</option>
                                        @foreach ($workers as $worker)
                                            <option value="{{ $worker->id }}" @selected($quotation->worker_id == $worker->id)>
                                                {{ $worker->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="admin_notes" class="form-control form-control-sm mb-2" placeholder="Admin notes">
                                    <button class="btn btn-sm btn-primary btn-clean w-100">
                                        Assign
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No quotation requests found.</td>
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