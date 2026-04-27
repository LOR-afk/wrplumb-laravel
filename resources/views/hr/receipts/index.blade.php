@extends('hr.layouts.app')

@section('title', 'Receipts')

@section('content')
<div class="page-header-card mb-4">
    <h2 class="mb-1">Receipts</h2>
    <p class="text-muted mb-0">View and manage issued receipts.</p>
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
                        <th>Receipt No.</th>
                        <th>Payment No.</th>
                        <th>Invoice No.</th>
                        <th>Client</th>
                        <th>Amount</th>
                        <th>Receipt Date</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($receipts as $receipt)
                        <tr>
                            <td class="fw-semibold">{{ $receipt->receipt_no }}</td>
                            <td>{{ $receipt->payment->payment_no ?? '—' }}</td>
                            <td>{{ $receipt->payment->invoice->invoice_no ?? '—' }}</td>
                            <td>{{ $receipt->payment->invoice->quotation->request->full_name ?? '—' }}</td>
                            <td>PHP {{ number_format((float) $receipt->amount_received, 2) }}</td>
                            <td>{{ optional($receipt->receipt_date)->format('M d, Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('hr.receipts.show', $receipt) }}" class="btn btn-sm btn-outline-primary">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No receipts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($receipts, 'links'))
            <div class="p-3">
                {{ $receipts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection