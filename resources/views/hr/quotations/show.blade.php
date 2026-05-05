@extends('hr.layouts.app')

@section('title', 'Quotation Details')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/hr/quotations.css') }}">
@endpush

@section('content')
<div class="page-header-card mb-4">
    <h2 class="mb-1">Quotation Details</h2>
    <p class="text-muted mb-0">Review the quotation breakdown and continue to contract or invoice processing.</p>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if (session('info'))
    <div class="alert alert-info">{{ session('info') }}</div>
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

@php
    $status = strtolower((string) $quotation->status);
    $isAccepted = $quotation->status === 'accepted' || $quotation->client_response === 'accepted';

    $statusClass = match ($status) {
        'sent' => 'status-sent',
        'draft' => 'status-draft',
        'accepted', 'approved' => 'status-accepted',
        'declined', 'rejected', 'cancelled' => 'status-rejected',
        default => 'status-default',
    };
@endphp

<div class="quotation-hero-card mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <div class="small text-muted fw-bold text-uppercase">Quotation No.</div>
            <h3 class="quotation-title mb-2">{{ $quotation->quotation_no }}</h3>
            <div class="d-flex flex-wrap gap-2">
                <span class="quotation-status {{ $statusClass }}">{{ strtoupper(str_replace('_', ' ', $quotation->status)) }}</span>
                <span class="quotation-chip"><i class="fas fa-user me-1"></i>{{ $quotation->request->full_name }}</span>
                <span class="quotation-chip"><i class="fas fa-tools me-1"></i>{{ $quotation->request->service_type }}</span>
            </div>
        </div>

        <div class="quotation-total-panel">
            <div class="small text-muted fw-bold text-uppercase">Grand Total</div>
            <div class="quotation-total-big">PHP {{ number_format((float) $quotation->grand_total, 2) }}</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="quotation-detail-card h-100">
            <h5 class="fw-bold mb-3">Client and Request Details</h5>
            <div class="detail-grid">
                <div class="detail-box"><div class="detail-label">Client</div><div class="detail-value">{{ $quotation->request->full_name }}</div></div>
                <div class="detail-box"><div class="detail-label">Prepared By</div><div class="detail-value">{{ $quotation->preparedBy->name ?? $quotation->preparedBy->first_name ?? '—' }}</div></div>
                <div class="detail-box"><div class="detail-label">Service Type</div><div class="detail-value">{{ $quotation->request->service_type }}</div></div>
                <div class="detail-box"><div class="detail-label">Address</div><div class="detail-value">{{ $quotation->request->address }}</div></div>
                <div class="detail-box full"><div class="detail-label">Problem Details</div><div class="detail-value">{{ $quotation->request->details }}</div></div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="quotation-detail-card h-100">
            <h5 class="fw-bold mb-3">Processing Status</h5>

            <div class="process-mini-list">
                <div class="process-mini-item done">
                    <span><i class="fas fa-check"></i></span>
                    <div>
                        <strong>Quotation Prepared</strong>
                        <small>{{ optional($quotation->created_at)->format('M d, Y h:i A') }}</small>
                    </div>
                </div>

                <div class="process-mini-item {{ $isAccepted ? 'done' : '' }}">
                    <span><i class="fas {{ $isAccepted ? 'fa-check' : 'fa-clock' }}"></i></span>
                    <div>
                        <strong>Client Acceptance</strong>
                        <small>{{ $isAccepted ? 'Accepted' : 'Awaiting client response' }}</small>
                    </div>
                </div>

                <div class="process-mini-item {{ $quotation->contract ? 'done' : '' }}">
                    <span><i class="fas {{ $quotation->contract ? 'fa-check' : 'fa-file-contract' }}"></i></span>
                    <div>
                        <strong>Contract</strong>
                        <small>{{ $quotation->contract ? 'Generated' : 'Not generated yet' }}</small>
                    </div>
                </div>

                <div class="process-mini-item {{ $quotation->invoice ? 'done' : '' }}">
                    <span><i class="fas {{ $quotation->invoice ? 'fa-check' : 'fa-file-invoice' }}"></i></span>
                    <div>
                        <strong>Invoice</strong>
                        <small>{{ $quotation->invoice ? 'Created' : 'Not created yet' }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="quotation-detail-card mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h5 class="fw-bold mb-0">Quotation Items</h5>
        <span class="quotation-chip">{{ $quotation->items->count() }} item(s)</span>
    </div>

    <div class="table-responsive">
        <table class="table align-middle quotation-items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Category</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Unit Price</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quotation->items as $item)
                    <tr>
                        <td class="fw-semibold">{{ $item->description }}</td>
                        <td><span class="item-category-chip">{{ ucfirst($item->item_category) }}</span></td>
                        <td class="text-end">{{ number_format((float) $item->quantity, 2) }}</td>
                        <td class="text-end">PHP {{ number_format((float) $item->unit_price, 2) }}</td>
                        <td class="text-end fw-bold">PHP {{ number_format((float) $item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="row justify-content-end">
        <div class="col-md-5 col-lg-4">
            <div class="quotation-totals-box">
                <div class="total-line"><span>Materials</span><strong>PHP {{ number_format((float) $quotation->materials_cost, 2) }}</strong></div>
                <div class="total-line"><span>Labor</span><strong>PHP {{ number_format((float) $quotation->labor_cost, 2) }}</strong></div>
                <div class="total-line"><span>Miscellaneous</span><strong>PHP {{ number_format((float) $quotation->miscellaneous_cost, 2) }}</strong></div>
                <div class="total-line"><span>Subtotal</span><strong>PHP {{ number_format((float) $quotation->subtotal_amount, 2) }}</strong></div>
                <div class="total-line"><span>Tax</span><strong>PHP {{ number_format((float) $quotation->tax_amount, 2) }}</strong></div>
                <div class="total-line grand"><span>Grand Total</span><strong>PHP {{ number_format((float) $quotation->grand_total, 2) }}</strong></div>
            </div>
        </div>
    </div>
</div>

<div class="quotation-detail-card mb-4">
    <h5 class="fw-bold mb-3">Payment Terms</h5>

    @if (!empty($quotation->payment_terms_json['phases']))
        <div class="payment-term-grid">
            @foreach ($quotation->payment_terms_json['phases'] as $phase)
                <div class="payment-term-card">
                    <div>
                        <div class="fw-bold">{{ $phase['label'] }}</div>
                        <div class="small text-muted">{{ $phase['percent'] }}% of total quotation</div>
                    </div>
                    <strong>PHP {{ number_format((float) $phase['amount'], 2) }}</strong>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-muted">No payment terms available.</div>
    @endif
</div>

<div class="quotation-actions-card">
    <a href="{{ route('hr.quotations.index') }}" class="btn btn-outline-secondary quotation-action-btn">
        <i class="fas fa-arrow-left me-1"></i>Back to Quotations
    </a>

    @if ($quotation->contract)
        <a href="{{ route('hr.contracts.show', $quotation->contract) }}" class="btn btn-outline-dark quotation-action-btn">
            <i class="fas fa-file-contract me-1"></i>View Contract
        </a>
    @elseif ($isAccepted)
        <a href="{{ route('hr.contracts.create', $quotation) }}" class="btn btn-outline-dark quotation-action-btn">
            <i class="fas fa-file-contract me-1"></i>Generate Contract
        </a>
    @else
        <span class="workflow-note">
            <i class="fas fa-clock me-2"></i>Generate Contract appears after client accepts the quotation.
        </span>
    @endif

    @if ($quotation->invoice)
        <a href="{{ route('hr.invoices.show', $quotation->invoice) }}" class="btn btn-outline-info quotation-action-btn">
            <i class="fas fa-file-invoice me-1"></i>View Invoice
        </a>
    @elseif ($quotation->contract)
        <a href="{{ route('hr.invoices.create', $quotation) }}" class="btn btn-outline-primary quotation-action-btn">
            <i class="fas fa-file-invoice me-1"></i>Create Invoice
        </a>
    @endif

    @if (in_array($quotation->status, ['draft', 'sent']))
        <form method="POST" action="{{ route('hr.quotations.send', $quotation) }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-primary quotation-action-btn">
                <i class="fas fa-envelope me-1"></i>Send Quotation Email
            </button>
        </form>
    @endif
</div>
@endsection
