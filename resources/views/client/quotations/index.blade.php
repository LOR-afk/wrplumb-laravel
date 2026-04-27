@extends('client.layouts.app')

@section('title', 'My Quotations')

@section('content')
<div class="page-header-card mb-4">
    <h2 class="mb-1">My Quotations</h2>
    <p class="text-muted mb-0">View the quotations prepared for your service requests.</p>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if (session('info'))
    <div class="alert alert-info">{{ session('info') }}</div>
@endif

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Quotation No.</th>
                        <th>Service</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Date Created</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($quotations as $quotation)
                        <tr>
                            <td class="fw-semibold">{{ $quotation->quotation_no }}</td>
                            <td>{{ $quotation->request->service_type ?? '—' }}</td>
                            <td>
                                <span class="badge bg-secondary text-uppercase">
                                    {{ $quotation->status }}
                                </span>
                            </td>
                            <td>PHP {{ number_format((float) $quotation->grand_total, 2) }}</td>
                            <td>{{ optional($quotation->created_at)->format('M d, Y h:i A') }}</td>
                            <td class="text-end">
                                <a href="{{ route('client.quotations.show', $quotation) }}" class="btn btn-sm btn-outline-primary">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No quotations available yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($quotations, 'links'))
            <div class="p-3">
                {{ $quotations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection