@extends('hr.layouts.app')

@section('title', 'Quotation Details')

@section('content')
<div class="page-header-card mb-4">
    <h2 class="mb-1">Quotation Details</h2>
    <p class="text-muted mb-0">Review the quotation breakdown and send it to the client when ready.</p>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="small text-muted">Quotation No.</div>
                <div class="fw-semibold">{{ $quotation->quotation_no }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Status</div>
                <div class="fw-semibold text-uppercase">{{ $quotation->status }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Client</div>
                <div class="fw-semibold">{{ $quotation->request->full_name }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Prepared By</div>
                <div class="fw-semibold">{{ $quotation->preparedBy->name ?? $quotation->preparedBy->first_name ?? '—' }}</div>
            </div>

            <div class="col-md-6">
                <div class="small text-muted">Service Type</div>
                <div class="fw-semibold">{{ $quotation->request->service_type }}</div>
            </div>
            <div class="col-md-6">
                <div class="small text-muted">Address</div>
                <div class="fw-semibold">{{ $quotation->request->address }}</div>
            </div>

            <div class="col-12">
                <div class="small text-muted">Problem Details</div>
                <div class="fw-semibold">{{ $quotation->request->details }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
        <h5 class="mb-3">Quotation Items</h5>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($quotation->items as $item)
                        <tr>
                            <td>{{ $item->description }}</td>
                            <td class="text-capitalize">{{ $item->item_category }}</td>
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
                <div class="d-flex justify-content-between mb-2">
                    <span>Materials</span>
                    <strong>PHP {{ number_format((float) $quotation->materials_cost, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Labor</span>
                    <strong>PHP {{ number_format((float) $quotation->labor_cost, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Miscellaneous</span>
                    <strong>PHP {{ number_format((float) $quotation->miscellaneous_cost, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal</span>
                    <strong>PHP {{ number_format((float) $quotation->subtotal_amount, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Tax</span>
                    <strong>PHP {{ number_format((float) $quotation->tax_amount, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Grand Total</span>
                    <strong>PHP {{ number_format((float) $quotation->grand_total, 2) }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
        <h5 class="mb-3">Payment Terms</h5>

        @if (!empty($quotation->payment_terms_json['phases']))
            @foreach ($quotation->payment_terms_json['phases'] as $phase)
                <div class="d-flex justify-content-between border rounded-3 p-3 mb-2">
                    <div>
                        <div class="fw-semibold">{{ $phase['label'] }}</div>
                        <div class="small text-muted">{{ $phase['percent'] }}%</div>
                    </div>
                    <div class="fw-semibold">
                        PHP {{ number_format((float) $phase['amount'], 2) }}
                    </div>
                </div>
            @endforeach
        @else
            <div class="text-muted">No payment terms available.</div>
        @endif
    </div>
</div>

    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('hr.quotations.index') }}" class="btn btn-outline-secondary">
            Back to Quotations
        </a>

        @if ($quotation->contract)
            <a href="{{ route('hr.contracts.show', $quotation->contract) }}" class="btn btn-outline-dark">
                View Contract
            </a>
        @else
            <a href="{{ route('hr.contracts.create', $quotation) }}" class="btn btn-outline-dark">
                Generate Contract
            </a>
        @endif

        @if ($quotation->invoice)
            <a href="{{ route('hr.invoices.show', $quotation->invoice) }}" class="btn btn-outline-info">
                View Invoice
            </a>
        @else
            <a href="{{ route('hr.invoices.create', $quotation) }}" class="btn btn-outline-primary">
                Create Invoice
            </a>
        @endif

        @if ($quotation->status === 'draft')
            <form method="POST" action="{{ route('hr.quotations.send', $quotation) }}">
                @csrf
                <button type="submit" class="btn btn-primary">
                    Send Quotation to Client
                </button>
            </form>
        @endif
    </div>

@endsection