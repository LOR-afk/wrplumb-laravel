@extends('client.layouts.app')

@section('title', 'Invoice Details')

@section('content')
<div class="page-header-card mb-4">
    <h2 class="mb-1">Invoice Details</h2>
    <p class="text-muted mb-0">Review your invoice and billing details.</p>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="small text-muted">Invoice No.</div>
                <div class="fw-semibold">{{ $invoice->invoice_no }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Status</div>
                <div class="fw-semibold text-uppercase">{{ $invoice->status }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Invoice Date</div>
                <div class="fw-semibold">{{ optional($invoice->invoice_date)->format('M d, Y') }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Due Date</div>
                <div class="fw-semibold">{{ optional($invoice->due_date)->format('M d, Y') ?? '—' }}</div>
            </div>

            <div class="col-md-6">
                <div class="small text-muted">Service Type</div>
                <div class="fw-semibold">{{ $invoice->quotation->request->service_type ?? '—' }}</div>
            </div>
            <div class="col-md-6">
                <div class="small text-muted">Address</div>
                <div class="fw-semibold">{{ $invoice->quotation->request->address ?? '—' }}</div>
            </div>

            @if (!empty($invoice->description))
                <div class="col-12">
                    <div class="small text-muted">Description</div>
                    <div class="fw-semibold">{{ $invoice->description }}</div>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
        <h5 class="mb-3">Invoice Items</h5>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->items as $item)
                        <tr>
                            <td>{{ $item->description }}</td>
                            <td>{{ number_format((float) $item->quantity, 2) }}</td>
                            <td>PHP {{ number_format((float) $item->unit_price, 2) }}</td>
                            <td>PHP {{ number_format((float) $item->total_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row justify-content-end">
            <div class="col-md-4">
                <div class="d-flex justify-content-between">
                    <span>Total Amount</span>
                    <strong>PHP {{ number_format((float) $invoice->total_amount, 2) }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

    <div class="d-flex gap-2">
        <a href="{{ route('client.invoices.index') }}" class="btn btn-outline-secondary">
            Back to My Invoices
        </a>

        @if ($invoice->status !== 'paid')
            <a href="{{ route('client.payments.create', $invoice) }}" class="btn btn-outline-primary">
                Submit Payment
            </a>
        @endif
    </div>
@endsection