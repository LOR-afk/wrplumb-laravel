@extends('client.layouts.app')

@section('title', 'My Contracts')

@section('content')
<div class="page-header-card mb-4">
    <h2 class="mb-1">My Contracts</h2>
    <p class="text-muted mb-0">View generated contracts for your approved service quotations.</p>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Contract No.</th>
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
                            <td>{{ $contract->quotation->request->service_type ?? '—' }}</td>
                            <td>
                                <span class="badge bg-secondary text-uppercase">
                                    {{ $contract->status }}
                                </span>
                            </td>
                            <td>{{ optional($contract->contract_date)->format('M d, Y') }}</td>
                            <td>PHP {{ number_format((float) $contract->total_contract_price, 2) }}</td>
                            <td class="text-end">
                                <a href="{{ route('client.contracts.show', $contract) }}" class="btn btn-sm btn-outline-primary">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No contracts available yet.</td>
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