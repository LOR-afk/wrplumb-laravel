@extends('hr.layouts.app')

@section('title', 'Quotations')

@section('content')
<div class="page-header-card mb-4">
    <h2 class="mb-1">Quotations</h2>
    <p class="text-muted mb-0">Manage prepared quotations for client service requests.</p>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if (session('info'))
    <div class="alert alert-info">{{ session('info') }}</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="quotation-stat-card">
            <div class="quotation-stat-icon bg-blue"><i class="fas fa-file-invoice"></i></div>
            <div>
                <div class="quotation-stat-label">Total Quotations</div>
                <div class="quotation-stat-value">{{ $summary['total_quotations'] ?? 0 }}</div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="quotation-stat-card">
            <div class="quotation-stat-icon bg-green"><i class="fas fa-paper-plane"></i></div>
            <div>
                <div class="quotation-stat-label">Sent Quotations</div>
                <div class="quotation-stat-value">{{ $summary['sent_quotations'] ?? 0 }}</div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="quotation-stat-card">
            <div class="quotation-stat-icon bg-gold"><i class="fas fa-peso-sign"></i></div>
            <div>
                <div class="quotation-stat-label">Total Quoted</div>
                <div class="quotation-stat-value small-money">PHP {{ number_format((float) ($summary['total_amount'] ?? 0), 2) }}</div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="quotation-stat-card">
            <div class="quotation-stat-icon bg-slate"><i class="fas fa-clock"></i></div>
            <div>
                <div class="quotation-stat-label">Latest</div>
                <div class="quotation-stat-value latest-date">
                    {{ !empty($summary['latest_created']) ? \Carbon\Carbon::parse($summary['latest_created'])->format('M d, Y') : '—' }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="quotation-filter-card mb-4">
    <form method="GET" action="{{ route('hr.quotations.index') }}" class="row g-3 align-items-end">
        <div class="col-lg-5">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Quotation no., client, email, phone, or service...">
        </div>

        <div class="col-lg-3 col-md-6">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                <option value="sent" @selected(request('status') === 'sent')>Sent</option>
                <option value="accepted" @selected(request('status') === 'accepted')>Accepted</option>
                <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
            </select>
        </div>

        <div class="col-lg-4 col-md-6 d-flex gap-2">
            <button class="btn btn-primary flex-fill quotation-action-btn">
                <i class="fas fa-filter me-1"></i> Filter
            </button>
            <a href="{{ route('hr.quotations.index') }}" class="btn btn-outline-secondary quotation-action-btn">Reset</a>
        </div>
    </form>
</div>

<div class="quotation-table-card">
    <div class="quotation-table-header">
        <div>
            <h5 class="mb-1"><i class="fas fa-file-contract text-primary me-2"></i>Quotation Records</h5>
            <p class="text-muted mb-0">Track quotation status, totals, client details, and billing readiness.</p>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 quotation-table">
            <thead>
                <tr>
                    <th>Quotation</th>
                    <th>Client</th>
                    <th>Service</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Prepared By</th>
                    <th>Created</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($quotations as $quotation)
                    @php
                        $status = strtolower((string) $quotation->status);
                        $statusClass = match ($status) {
                            'sent' => 'status-sent',
                            'draft' => 'status-draft',
                            'accepted', 'approved' => 'status-accepted',
                            'rejected', 'cancelled' => 'status-rejected',
                            default => 'status-default',
                        };
                    @endphp
                    <tr>
                        <td>
                            <div class="quotation-no">{{ $quotation->quotation_no }}</div>
                            <div class="small text-muted">{{ $quotation->invoice ? 'Invoice ready' : 'No invoice yet' }}</div>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $quotation->request->full_name ?? $quotation->request->email ?? '—' }}</div>
                            <div class="small text-muted">
                                {{ $quotation->request->email ?? '' }}
                                @if(!empty($quotation->request->phone))
                                    • {{ $quotation->request->phone }}
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $quotation->request->service_type ?? '—' }}</div>
                            <div class="small text-muted text-capitalize">{{ $quotation->request->service_category ?? '—' }}</div>
                        </td>
                        <td>
                            <span class="quotation-status {{ $statusClass }}">{{ strtoupper(str_replace('_', ' ', $quotation->status)) }}</span>
                        </td>
                        <td>
                            <div class="quotation-total">PHP {{ number_format((float) $quotation->grand_total, 2) }}</div>
                            <div class="small text-muted">{{ $quotation->items_count ?? $quotation->items->count() }} item(s)</div>
                        </td>
                        <td>{{ $quotation->preparedBy->name ?? $quotation->preparedBy->first_name ?? '—' }}</td>
                        <td>{{ optional($quotation->created_at)->format('M d, Y h:i A') }}</td>
                        <td class="text-end">
                            <a href="{{ route('hr.quotations.show', $quotation) }}" class="btn btn-sm btn-outline-primary quotation-view-btn">
                                <i class="fas fa-eye me-1"></i> View Details
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="quotation-empty">
                                <div class="quotation-empty-icon"><i class="fas fa-file-invoice"></i></div>
                                <h5 class="fw-bold text-dark mb-1">No quotations found</h5>
                                <p class="mb-0">Prepared quotations will appear here once service requests are reviewed.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($quotations, 'links'))
        <div class="p-3">
            {{ $quotations->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

<style>
    .quotation-stat-card,
    .quotation-filter-card,
    .quotation-table-card {
        background: #fff;
        border: 1px solid rgba(15, 76, 129, 0.12);
        border-radius: 24px;
        box-shadow: 0 12px 34px rgba(15, 23, 42, 0.07);
    }

    .quotation-stat-card {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 18px;
        min-height: 104px;
    }

    .quotation-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        font-size: 1.1rem;
    }

    .quotation-stat-icon.bg-blue { background: #eaf6ff; color: #0f4c81; }
    .quotation-stat-icon.bg-green { background: #dcfce7; color: #166534; }
    .quotation-stat-icon.bg-gold { background: #fef3c7; color: #92400e; }
    .quotation-stat-icon.bg-slate { background: #f1f5f9; color: #475569; }

    .quotation-stat-label {
        color: #64748b;
        font-size: 0.76rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .quotation-stat-value {
        color: #0f172a;
        font-size: 1.45rem;
        font-weight: 950;
        line-height: 1.2;
    }

    .quotation-stat-value.small-money,
    .quotation-stat-value.latest-date { font-size: 1.02rem; }

    .quotation-filter-card { padding: 18px; }

    .quotation-filter-card .form-label {
        font-weight: 850;
        color: #334155;
    }

    .quotation-filter-card .form-control,
    .quotation-filter-card .form-select {
        border-radius: 14px;
        padding: 11px 14px;
    }

    .quotation-action-btn,
    .quotation-view-btn {
        border-radius: 12px;
        font-weight: 900;
    }

    .quotation-table-card { overflow: hidden; }

    .quotation-table-header {
        padding: 18px 20px;
        border-bottom: 1px solid rgba(15, 76, 129, 0.10);
        background: linear-gradient(135deg, #ffffff, #f8fbff);
    }

    .quotation-table thead th {
        background: #f8fafc;
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 950;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .quotation-table tbody td {
        vertical-align: middle;
        padding-top: 16px;
        padding-bottom: 16px;
    }

    .quotation-no {
        color: #0f4c81;
        font-weight: 950;
    }

    .quotation-total {
        color: #0f172a;
        font-weight: 950;
        white-space: nowrap;
    }

    .quotation-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 7px 11px;
        font-size: 0.72rem;
        font-weight: 950;
        letter-spacing: 0.04em;
    }

    .quotation-status::before {
        content: "";
        width: 7px;
        height: 7px;
        border-radius: 999px;
        display: inline-block;
    }

    .status-sent { background: #eaf6ff; color: #0f4c81; }
    .status-sent::before { background: #1d9bf0; }
    .status-draft { background: #f1f5f9; color: #475569; }
    .status-draft::before { background: #64748b; }
    .status-accepted { background: #dcfce7; color: #166534; }
    .status-accepted::before { background: #16a34a; }
    .status-rejected { background: #fee2e2; color: #991b1b; }
    .status-rejected::before { background: #dc2626; }
    .status-default { background: #f1f5f9; color: #334155; }
    .status-default::before { background: #94a3b8; }

    .quotation-empty {
        padding: 56px 18px;
        text-align: center;
        color: #64748b;
    }

    .quotation-empty-icon {
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
@endsection
