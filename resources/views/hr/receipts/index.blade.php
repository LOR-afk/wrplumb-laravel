@extends('hr.layouts.app')

@section('title', 'Receipts')

@section('content')
<style>
    .receipt-stat-card,
    .receipt-filter-card,
    .receipt-table-card {
        border: 0;
        border-radius: 24px;
        box-shadow: 0 12px 34px rgba(15, 23, 42, 0.08);
    }

    .receipt-stat-card {
        overflow: hidden;
        background: #ffffff;
    }

    .receipt-stat-card::before {
        content: "";
        display: block;
        height: 5px;
        background: linear-gradient(90deg, #0f4c81, #1d9bf0);
    }

    .receipt-stat-label {
        color: #64748b;
        font-size: 0.82rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .receipt-stat-value {
        color: #0f172a;
        font-size: 1.55rem;
        font-weight: 950;
        margin: 0;
    }

    .receipt-filter-card .form-label {
        font-weight: 800;
        color: #334155;
    }

    .receipt-filter-card .form-control {
        border-radius: 14px;
        padding: 11px 14px;
    }

    .receipt-table-card {
        overflow: hidden;
    }

    .receipt-table-card table thead th {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        font-weight: 900;
        white-space: nowrap;
        background: #f8fafc;
    }

    .receipt-table-card table tbody td {
        vertical-align: middle;
    }

    .receipt-no {
        font-weight: 950;
        color: #0f4c81;
    }

    .amount-cell {
        font-weight: 950;
        color: #0f172a;
        white-space: nowrap;
    }

    .method-chip {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 6px 10px;
        background: #eaf6ff;
        color: #0f4c81;
        font-weight: 900;
        font-size: 0.78rem;
    }

    .btn-receipt-action {
        border-radius: 12px;
        font-weight: 900;
    }

    .empty-receipts {
        padding: 56px 18px;
        text-align: center;
        color: #64748b;
    }

    .empty-receipts-icon {
        width: 70px;
        height: 70px;
        border-radius: 24px;
        background: #eaf6ff;
        color: #0f4c81;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-bottom: 16px;
    }
</style>

<div class="page-header-card mb-4">
    <h2 class="mb-1">Receipts</h2>
    <p class="text-muted mb-0">View, search, print, and manage issued receipts.</p>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if (session('info'))
    <div class="alert alert-info">{{ session('info') }}</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card receipt-stat-card h-100">
            <div class="card-body">
                <div class="receipt-stat-label">Total Receipts</div>
                <p class="receipt-stat-value">{{ $summary['total_receipts'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card receipt-stat-card h-100">
            <div class="card-body">
                <div class="receipt-stat-label">Total Receipted Amount</div>
                <p class="receipt-stat-value">PHP {{ number_format((float) ($summary['total_amount'] ?? 0), 2) }}</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card receipt-stat-card h-100">
            <div class="card-body">
                <div class="receipt-stat-label">This Month</div>
                <p class="receipt-stat-value">PHP {{ number_format((float) ($summary['this_month_amount'] ?? 0), 2) }}</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card receipt-stat-card h-100">
            <div class="card-body">
                <div class="receipt-stat-label">Latest Receipt</div>
                <p class="receipt-stat-value" style="font-size:1.05rem;">
                    {{ !empty($summary['latest_receipt_date']) ? \Carbon\Carbon::parse($summary['latest_receipt_date'])->format('M d, Y') : '—' }}
                </p>
            </div>
        </div>
    </div>
</div>

<div class="card receipt-filter-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('hr.receipts.index') }}" class="row g-3 align-items-end">
            <div class="col-lg-5">
                <label class="form-label">Search</label>
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    value="{{ request('search') }}"
                    placeholder="Receipt no., payment no., invoice no., or client name"
                >
            </div>

            <div class="col-lg-2 col-md-4">
                <label class="form-label">From</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>

            <div class="col-lg-2 col-md-4">
                <label class="form-label">To</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>

            <div class="col-lg-3 col-md-4 d-flex gap-2">
                <button class="btn btn-primary flex-fill btn-receipt-action">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
                <a href="{{ route('hr.receipts.index') }}" class="btn btn-outline-secondary btn-receipt-action">
                    Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card receipt-table-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Receipt No.</th>
                        <th>Payment No.</th>
                        <th>Invoice No.</th>
                        <th>Client</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Receipt Date</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($receipts as $receipt)
                        <tr>
                            <td class="receipt-no">{{ $receipt->receipt_no }}</td>
                            <td>{{ $receipt->payment->payment_no ?? '—' }}</td>
                            <td>{{ $receipt->payment->invoice->invoice_no ?? '—' }}</td>
                            <td>
                                <div class="fw-bold text-dark">
                                    {{ $receipt->payment->invoice->quotation->request->full_name ?? '—' }}
                                </div>
                                <div class="small text-muted">
                                    {{ $receipt->payment->invoice->quotation->request->email ?? '' }}
                                </div>
                            </td>
                            <td class="amount-cell">PHP {{ number_format((float) $receipt->amount_received, 2) }}</td>
                            <td>
                                <span class="method-chip">
                                    <i class="fas fa-wallet me-1"></i>
                                    {{ $receipt->payment_method ?? $receipt->payment->payment_method ?? '—' }}
                                </span>
                            </td>
                            <td>{{ optional($receipt->receipt_date)->format('M d, Y') ?? '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('hr.receipts.show', $receipt) }}" class="btn btn-sm btn-outline-primary btn-receipt-action">
                                    <i class="fas fa-eye me-1"></i> View Receipt
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-receipts">
                                    <div class="empty-receipts-icon">
                                        <i class="fas fa-receipt"></i>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-1">No receipts found</h5>
                                    <p class="mb-0">Issued receipts will appear here after confirmed payments are processed.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($receipts, 'links'))
            <div class="p-3">
                {{ $receipts->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
