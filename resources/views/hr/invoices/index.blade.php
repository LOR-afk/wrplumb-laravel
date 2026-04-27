@extends('hr.layouts.app')

@section('title', 'Invoices')

@section('content')
<div class="page-header-card mb-4">
    <h2 class="mb-1">Invoices</h2>
    <p class="text-muted mb-0">Manage invoices generated from quotations.</p>
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
                        <th>Invoice No.</th>
                        <th>Client</th>
                        <th>Service</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Invoice Date</th>
                        <th>Due Date</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td class="fw-semibold">{{ $invoice->invoice_no }}</td>
                            <td>{{ $invoice->quotation->request->full_name ?? $invoice->quotation->request->email ?? '—' }}</td>
                            <td>{{ $invoice->quotation->request->service_type ?? '—' }}</td>
                            <td>
                                <span class="badge bg-secondary text-uppercase">
                                    {{ $invoice->status }}
                                </span>
                            </td>
                            <td>PHP {{ number_format((float) $invoice->total_amount, 2) }}</td>
                            <td>{{ optional($invoice->invoice_date)->format('M d, Y') }}</td>
                            <td>{{ optional($invoice->due_date)->format('M d, Y') ?? '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('hr.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No invoices found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($invoices, 'links'))
            <div class="p-3">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
@endsection