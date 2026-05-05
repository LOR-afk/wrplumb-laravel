@extends('hr.layouts.app')

@section('title', 'Invoices')

@section('content')
<style>
    .invoice-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .invoice-stat-card,
    .invoice-filter-card,
    .invoice-list-card {
        background: #ffffff;
        border: 1px solid #e5edf5;
        border-radius: 22px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
    }

    .invoice-stat-card {
        padding: 18px;
    }

    .invoice-stat-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .invoice-stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }

    .invoice-stat-icon.blue { background: #e0f2fe; color: #0369a1; }
    .invoice-stat-icon.green { background: #dcfce7; color: #166534; }
    .invoice-stat-icon.orange { background: #fff7ed; color: #c2410c; }
    .invoice-stat-icon.dark { background: #eef2f7; color: #334155; }

    .invoice-stat-label {
        color: #64748b;
        font-size: 0.84rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .invoice-stat-value {
        font-size: 1.45rem;
        font-weight: 900;
        color: #0f172a;
        line-height: 1.15;
    }

    .invoice-filter-card {
        padding: 18px;
        margin-bottom: 18px;
    }

    .invoice-list-card {
        overflow: hidden;
    }

    .invoice-list-header {
        padding: 20px 22px;
        border-bottom: 1px solid #e5edf5;
        background: #fbfdff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .invoice-list-title {
        font-weight: 900;
        color: #0f172a;
        margin: 0;
    }

    .invoice-table thead th {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        font-weight: 900;
        background: #f8fbff;
        white-space: nowrap;
    }

    .invoice-no {
        font-weight: 900;
        color: #0f172a;
    }

    .invoice-client {
        font-weight: 800;
        color: #0f172a;
    }

    .invoice-service {
        color: #64748b;
        font-size: 0.88rem;
    }

    .invoice-money {
        font-weight: 900;
        color: #0f172a;
        white-space: nowrap;
    }

    .invoice-money-muted {
        color: #64748b;
        font-size: 0.84rem;
        white-space: nowrap;
    }

    .invoice-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 11px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 900;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .invoice-status.paid { background: #dcfce7; color: #166534; }
    .invoice-status.partial, .invoice-status.partially_paid { background: #e0f2fe; color: #0369a1; }
    .invoice-status.unpaid { background: #fff7ed; color: #c2410c; }
    .invoice-status.overdue { background: #fee2e2; color: #991b1b; }
    .invoice-status.cancelled { background: #f1f5f9; color: #475569; }
    .invoice-status.default { background: #eef2f7; color: #475569; }

    .invoice-progress-wrap {
        min-width: 190px;
    }

    .invoice-progress-meta {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        font-size: 0.78rem;
        color: #64748b;
        margin-bottom: 6px;
        font-weight: 800;
    }

    .invoice-progress {
        height: 10px;
        border-radius: 999px;
        background: #e5edf5;
        overflow: hidden;
    }

    .invoice-progress-bar {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #1d9bf0, #22c55e);
    }

    .invoice-action-btn {
        border-radius: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .invoice-empty-state {
        min-height: 240px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #64748b;
    }

    .invoice-empty-icon {
        width: 68px;
        height: 68px;
        border-radius: 22px;
        background: #e0f2fe;
        color: #0284c7;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.7rem;
        margin-bottom: 14px;
    }

    @media (max-width: 1199.98px) {
        .invoice-stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .invoice-stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

@php
    $summary = $summary ?? [
        'total_invoices' => $invoices->total() ?? $invoices->count(),
        'total_amount' => 0,
        'total_paid' => 0,
        'total_remaining' => 0,
        'paid_invoices' => 0,
    ];
@endphp

<div class="page-header-card mb-4">
    <h2 class="mb-1">Invoices</h2>
    <p class="text-muted mb-0">Manage invoices generated from quotations and monitor payment progress.</p>
</div>

<div class="invoice-stats-grid">
    <div class="invoice-stat-card">
        <div class="invoice-stat-top">
            <div>
                <div class="invoice-stat-label">Total Invoices</div>
                <div class="invoice-stat-value">{{ number_format($summary['total_invoices'] ?? 0) }}</div>
            </div>
            <div class="invoice-stat-icon blue"><i class="fas fa-file-invoice"></i></div>
        </div>
        <div class="text-muted small">Filtered invoice records</div>
    </div>

    <div class="invoice-stat-card">
        <div class="invoice-stat-top">
            <div>
                <div class="invoice-stat-label">Total Amount</div>
                <div class="invoice-stat-value">PHP {{ number_format((float) ($summary['total_amount'] ?? 0), 2) }}</div>
            </div>
            <div class="invoice-stat-icon dark"><i class="fas fa-receipt"></i></div>
        </div>
        <div class="text-muted small">Total billed amount</div>
    </div>

    <div class="invoice-stat-card">
        <div class="invoice-stat-top">
            <div>
                <div class="invoice-stat-label">Total Paid</div>
                <div class="invoice-stat-value">PHP {{ number_format((float) ($summary['total_paid'] ?? 0), 2) }}</div>
            </div>
            <div class="invoice-stat-icon green"><i class="fas fa-circle-check"></i></div>
        </div>
        <div class="text-muted small">Confirmed collected amount</div>
    </div>

    <div class="invoice-stat-card">
        <div class="invoice-stat-top">
            <div>
                <div class="invoice-stat-label">Remaining</div>
                <div class="invoice-stat-value">PHP {{ number_format((float) ($summary['total_remaining'] ?? 0), 2) }}</div>
            </div>
            <div class="invoice-stat-icon orange"><i class="fas fa-wallet"></i></div>
        </div>
        <div class="text-muted small">Unpaid billing balance</div>
    </div>
</div>

<div class="invoice-filter-card">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-lg-4 col-md-6">
            <label class="form-label">Search</label>
            <input
                type="text"
                name="search"
                class="form-control"
                placeholder="Invoice, client, email, service..."
                value="{{ request('search') }}"
            >
        </div>

        <div class="col-lg-2 col-md-6">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="unpaid" @selected(request('status') === 'unpaid')>Unpaid</option>
                <option value="partial" @selected(request('status') === 'partial')>Partial</option>
                <option value="partially_paid" @selected(request('status') === 'partially_paid')>Partially Paid</option>
                <option value="paid" @selected(request('status') === 'paid')>Paid</option>
                <option value="overdue" @selected(request('status') === 'overdue')>Overdue</option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
            </select>
        </div>

        <div class="col-lg-2 col-md-6">
            <label class="form-label">From</label>
            <input type="date" name="invoice_date_from" class="form-control" value="{{ request('invoice_date_from') }}">
        </div>

        <div class="col-lg-2 col-md-6">
            <label class="form-label">To</label>
            <input type="date" name="invoice_date_to" class="form-control" value="{{ request('invoice_date_to') }}">
        </div>

        <div class="col-lg-2 col-md-12 d-flex gap-2">
            <button class="btn btn-primary flex-fill">
                <i class="fas fa-filter me-1"></i> Filter
            </button>
            <a href="{{ route('hr.invoices.index') }}" class="btn btn-outline-secondary">
                Reset
            </a>
        </div>
    </form>
</div>

<div class="invoice-list-card">
    <div class="invoice-list-header">
        <div>
            <h5 class="invoice-list-title"><i class="fas fa-file-invoice-dollar me-2 text-primary"></i>Invoice Records</h5>
            <div class="text-muted small mt-1">Track billing, payment progress, paid amount, and remaining balances.</div>
        </div>
    </div>

    @if ($invoices->count())
        <div class="table-responsive">
            <table class="table align-middle mb-0 invoice-table">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Client / Service</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Payment Progress</th>
                        <th>Invoice Date</th>
                        <th>Due Date</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoices as $invoice)
                        @php
                            $totalAmount = (float) $invoice->total_amount;
                            $paidAmount = (float) $invoice->paymentSchedules->sum('amount_paid');
                            $remainingAmount = max($totalAmount - $paidAmount, 0);
                            $progress = $totalAmount > 0 ? min(100, round(($paidAmount / $totalAmount) * 100, 2)) : 0;
                            $rawStatus = strtolower((string) $invoice->status);
                            $statusClass = in_array($rawStatus, ['paid', 'unpaid', 'partial', 'partially_paid', 'overdue', 'cancelled']) ? $rawStatus : 'default';
                            $statusLabel = str_replace('_', ' ', $invoice->status);
                        @endphp
                        <tr>
                            <td>
                                <div class="invoice-no">{{ $invoice->invoice_no }}</div>
                                <div class="text-muted small">{{ $invoice->quotation->quotation_no ?? 'No quotation no.' }}</div>
                            </td>
                            <td>
                                <div class="invoice-client">{{ $invoice->quotation->request->full_name ?? $invoice->quotation->request->email ?? '—' }}</div>
                                <div class="invoice-service">{{ $invoice->quotation->request->service_type ?? '—' }}</div>
                            </td>
                            <td>
                                <span class="invoice-status {{ $statusClass }}">
                                    <i class="fas fa-circle"></i> {{ ucfirst($statusLabel) }}
                                </span>
                            </td>
                            <td>
                                <div class="invoice-money">PHP {{ number_format($totalAmount, 2) }}</div>
                                <div class="invoice-money-muted">Balance: PHP {{ number_format($remainingAmount, 2) }}</div>
                            </td>
                            <td>
                                <div class="invoice-progress-wrap">
                                    <div class="invoice-progress-meta">
                                        <span>Paid PHP {{ number_format($paidAmount, 2) }}</span>
                                        <span>{{ number_format($progress, 2) }}%</span>
                                    </div>
                                    <div class="invoice-progress">
                                        <div class="invoice-progress-bar" style="width: {{ $progress }}%;"></div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ optional($invoice->invoice_date)->format('M d, Y') ?? '—' }}</td>
                            <td>{{ optional($invoice->due_date)->format('M d, Y') ?? '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('hr.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary invoice-action-btn">
                                    <i class="fas fa-eye me-1"></i> View Details
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(method_exists($invoices, 'links'))
            <div class="p-3 border-top">
                {{ $invoices->links('pagination::bootstrap-5') }}
            </div>
        @endif
    @else
        <div class="invoice-empty-state">
            <div class="invoice-empty-icon"><i class="fas fa-file-circle-xmark"></i></div>
            <div class="fw-bold text-dark mb-1">No invoices found</div>
            <div>No invoice records matched your filters.</div>
        </div>
    @endif
</div>
@endsection
