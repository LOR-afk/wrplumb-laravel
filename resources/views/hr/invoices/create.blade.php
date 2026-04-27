@extends('hr.layouts.app')

@section('title', 'Create Invoice')

@section('content')
<div class="page-header-card mb-4">
    <h2 class="mb-1">Create Invoice</h2>
    <p class="text-muted mb-0">Generate an invoice from an existing quotation.</p>
</div>

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
        <h5 class="mb-3">Quotation Summary</h5>

        <div class="row g-3">
            <div class="col-md-3">
                <div class="small text-muted">Quotation No.</div>
                <div class="fw-semibold">{{ $quotation->quotation_no }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Client</div>
                <div class="fw-semibold">{{ $quotation->request->full_name }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Service Type</div>
                <div class="fw-semibold">{{ $quotation->request->service_type }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Grand Total</div>
                <div class="fw-semibold">PHP {{ number_format((float) $quotation->grand_total, 2) }}</div>
            </div>

            <div class="col-md-6">
                <div class="small text-muted">Address</div>
                <div class="fw-semibold">{{ $quotation->request->address }}</div>
            </div>

            <div class="col-md-6">
                <div class="small text-muted">Quotation Status</div>
                <div class="fw-semibold text-uppercase">{{ $quotation->status }}</div>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('hr.invoices.store') }}">
    @csrf
    <input type="hidden" name="quotation_id" value="{{ $quotation->id }}">

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="mb-3">Invoice Details</h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Invoice Date</label>
                            <input
                                type="date"
                                name="invoice_date"
                                class="form-control"
                                value="{{ old('invoice_date', now()->format('Y-m-d')) }}"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Due Date</label>
                            <input
                                type="date"
                                name="due_date"
                                class="form-control"
                                value="{{ old('due_date') }}"
                            >
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description / Notes</label>
                            <textarea
                                name="description"
                                class="form-control"
                                rows="4"
                                placeholder="Enter invoice notes or billing description..."
                            >{{ old('description', $quotation->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mt-4">
                <div class="card-body">
                    <h5 class="mb-3">Items to be Copied from Quotation</h5>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
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

                    <div class="small text-muted mt-3">
                        These quotation items will be copied to the invoice once you create it.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="mb-3">Invoice Summary</h5>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Quotation Total</span>
                        <strong>PHP {{ number_format((float) $quotation->grand_total, 2) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Invoice Status</span>
                        <strong>UNPAID</strong>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <span>Total Amount</span>
                        <strong>PHP {{ number_format((float) $quotation->grand_total, 2) }}</strong>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Create Invoice</button>
                <a href="{{ route('hr.quotations.show', $quotation) }}" class="btn btn-outline-secondary">Back to Quotation</a>
            </div>
        </div>
    </div>
</form>
@endsection