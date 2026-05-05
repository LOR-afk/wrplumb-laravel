@extends('hr.layouts.app')

@section('title', 'Create Invoice')

@section('content')
<style>
    .invoice-create-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 360px;
        gap: 22px;
        align-items: start;
    }

    .invoice-card {
        background: #ffffff;
        border: 1px solid #e5edf5;
        border-radius: 22px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .invoice-card-header {
        padding: 18px 22px;
        border-bottom: 1px solid #e5edf5;
        background: #fbfdff;
    }

    .invoice-card-title {
        margin: 0;
        font-weight: 900;
        color: #0f172a;
    }

    .invoice-card-body {
        padding: 22px;
    }

    .invoice-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .invoice-summary-item {
        background: #f8fbff;
        border: 1px solid #eef2f7;
        border-radius: 16px;
        padding: 14px;
    }

    .invoice-label {
        font-size: 0.76rem;
        color: #64748b;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 5px;
    }

    .invoice-value {
        font-weight: 900;
        color: #0f172a;
    }

    .invoice-muted {
        color: #64748b;
        font-size: 0.88rem;
    }

    .invoice-total-card {
        position: sticky;
        top: 104px;
    }

    .invoice-total-amount {
        font-size: 1.9rem;
        font-weight: 950;
        color: #0f172a;
        line-height: 1.1;
    }

    .invoice-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        border-radius: 999px;
        background: #fff7ed;
        color: #c2410c;
        font-weight: 900;
        font-size: 0.78rem;
        text-transform: uppercase;
    }

    .schedule-preview {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .schedule-preview-item {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 12px;
        align-items: center;
        background: #f8fbff;
        border: 1px solid #eef2f7;
        border-radius: 16px;
        padding: 12px 14px;
    }

    .schedule-preview-label {
        font-weight: 900;
        color: #0f172a;
    }

    .schedule-preview-percent {
        color: #64748b;
        font-size: 0.84rem;
        font-weight: 800;
    }

    .schedule-preview-amount {
        font-weight: 900;
        color: #0f172a;
        white-space: nowrap;
    }

    .invoice-actions .btn {
        border-radius: 14px;
        font-weight: 900;
        padding: 12px 16px;
    }

    .invoice-table thead th {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        font-weight: 900;
        background: #f8fbff;
    }

    @media (max-width: 1199.98px) {
        .invoice-create-grid {
            grid-template-columns: 1fr;
        }

        .invoice-total-card {
            position: static;
        }

        .invoice-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .invoice-summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

@php
    $paymentTerms = $quotation->payment_terms_json ?? [];
    $phases = $paymentTerms['phases'] ?? [];
@endphp

<div class="page-header-card mb-4">
    <h2 class="mb-1">Create Invoice</h2>
    <p class="text-muted mb-0">Generate an invoice from an approved quotation and prepare its payment schedule.</p>
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

<div class="invoice-card mb-4">
    <div class="invoice-card-header">
        <h5 class="invoice-card-title"><i class="fas fa-file-signature me-2 text-primary"></i>Quotation Summary</h5>
    </div>
    <div class="invoice-card-body">
        <div class="invoice-summary-grid">
            <div class="invoice-summary-item">
                <div class="invoice-label">Quotation No.</div>
                <div class="invoice-value">{{ $quotation->quotation_no }}</div>
            </div>
            <div class="invoice-summary-item">
                <div class="invoice-label">Client</div>
                <div class="invoice-value">{{ $quotation->request->full_name }}</div>
            </div>
            <div class="invoice-summary-item">
                <div class="invoice-label">Service Type</div>
                <div class="invoice-value">{{ $quotation->request->service_type }}</div>
            </div>
            <div class="invoice-summary-item">
                <div class="invoice-label">Grand Total</div>
                <div class="invoice-value">PHP {{ number_format((float) $quotation->grand_total, 2) }}</div>
            </div>
            <div class="invoice-summary-item" style="grid-column: span 2;">
                <div class="invoice-label">Address</div>
                <div class="invoice-value">{{ $quotation->request->address }}</div>
            </div>
            <div class="invoice-summary-item">
                <div class="invoice-label">Quotation Status</div>
                <div class="invoice-value text-uppercase">{{ $quotation->status }}</div>
            </div>
            <div class="invoice-summary-item">
                <div class="invoice-label">Items</div>
                <div class="invoice-value">{{ $quotation->items->count() }} item(s)</div>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('hr.invoices.store') }}">
    @csrf
    <input type="hidden" name="quotation_id" value="{{ $quotation->id }}">

    <div class="invoice-create-grid">
        <div>
            <div class="invoice-card">
                <div class="invoice-card-header">
                    <h5 class="invoice-card-title"><i class="fas fa-pen-to-square me-2 text-primary"></i>Invoice Details</h5>
                </div>
                <div class="invoice-card-body">
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

            <div class="invoice-card mt-4">
                <div class="invoice-card-header">
                    <h5 class="invoice-card-title"><i class="fas fa-list-check me-2 text-primary"></i>Items to be Copied from Quotation</h5>
                </div>
                <div class="invoice-card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 invoice-table">
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
                                        <td class="fw-semibold">{{ $item->description }}</td>
                                        <td class="text-capitalize">{{ $item->item_category }}</td>
                                        <td>{{ number_format((float) $item->quantity, 2) }}</td>
                                        <td>PHP {{ number_format((float) $item->unit_price, 2) }}</td>
                                        <td class="fw-bold">PHP {{ number_format((float) $item->total_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3 border-top invoice-muted">
                        These quotation items will be copied to the invoice once you create it.
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="invoice-card invoice-total-card">
                <div class="invoice-card-header">
                    <h5 class="invoice-card-title"><i class="fas fa-calculator me-2 text-primary"></i>Invoice Summary</h5>
                </div>
                <div class="invoice-card-body">
                    <div class="invoice-label">Total Amount</div>
                    <div class="invoice-total-amount mb-3">PHP {{ number_format((float) $quotation->grand_total, 2) }}</div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="invoice-muted">Initial Status</span>
                        <span class="invoice-status-pill"><i class="fas fa-clock"></i> Unpaid</span>
                    </div>

                    <hr>

                    <h6 class="fw-bold mb-3">Payment Schedule Preview</h6>

                    @if (!empty($phases) && is_array($phases))
                        <div class="schedule-preview">
                            @foreach ($phases as $index => $phase)
                                <div class="schedule-preview-item">
                                    <div>
                                        <div class="schedule-preview-label">{{ $phase['label'] ?? ('Phase ' . ($index + 1)) }}</div>
                                        <div class="schedule-preview-percent">{{ number_format((float) ($phase['percent'] ?? 0), 2) }}%</div>
                                    </div>
                                    <div class="schedule-preview-amount">
                                        PHP {{ number_format((float) ($phase['amount'] ?? 0), 2) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            No payment terms were found. The invoice will be created, but no payment schedule will be generated.
                        </div>
                    @endif

                    <div class="invoice-actions d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-file-circle-plus me-1"></i> Create Invoice
                        </button>
                        <a href="{{ route('hr.quotations.show', $quotation) }}" class="btn btn-outline-secondary">
                            Back to Quotation
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
