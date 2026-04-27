@extends('hr.layouts.app')

@section('title', 'Contracts')

@section('content')
<div class="page-header-card mb-4">
    <h2 class="mb-1">Contracts</h2>
    <p class="text-muted mb-0">Manage generated service contracts.</p>
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
                        <th>Contract No.</th>
                        <th>Client</th>
                        <th>Service</th>
                        <th>Status</th>
                        <th>Contract Date</th>
                        <th>Total Price</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($contracts as $contract)
                        <tr>
                            <td class="fw-semibold">{{ $contract->contract_no }}</td>
                            <td>{{ $contract->client_name ?: ($contract->quotation->request->full_name ?? '—') }}</td>
                            <td>{{ $contract->quotation->request->service_type ?? '—' }}</td>
                            <td>
                                <span class="badge bg-secondary text-uppercase">
                                    {{ $contract->status }}
                                </span>
                            </td>
                            <td>{{ optional($contract->contract_date)->format('M d, Y') }}</td>
                            <td>PHP {{ number_format((float) $contract->total_contract_price, 2) }}</td>
                            <td class="text-end">
                                <a href="{{ route('hr.contracts.show', $contract) }}" class="btn btn-sm btn-outline-primary">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No contracts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($contracts, 'links'))
            <div class="p-3">
                {{ $contracts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection