@extends('admin.layouts.app')

@section('title', 'Warranty Claims - WRPlumb')
@section('topbar_title', 'Warranty Claims')
@section('topbar_subtitle', 'Review client warranty requests and create backjobs when approved.')

@push('styles')
<style>
    .wc-page {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .wc-card {
        background: #ffffff;
        border: 1px solid #dbe7f3;
        border-radius: 20px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.055);
        overflow: hidden;
    }

    .wc-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 15px 18px;
        border-bottom: 1px solid #dbe7f3;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    }

    .wc-card-title {
        margin: 0;
        color: #0f172a;
        font-weight: 950;
        font-size: 1.02rem;
    }

    .wc-card-subtitle {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 0.8rem;
    }

    .wc-card-body {
        padding: 16px 18px;
    }

    .wc-filter-form {
        display: grid;
        grid-template-columns: minmax(240px, 1.5fr) minmax(150px, 0.8fr) minmax(160px, 0.8fr) auto auto;
        gap: 10px;
        align-items: end;
    }

    .wc-filter-form .form-label {
        font-size: 0.72rem;
        font-weight: 850;
        color: #334155;
        margin-bottom: 5px;
    }

    .wc-table {
        margin-bottom: 0;
    }

    .wc-table th {
        color: #64748b;
        font-size: 0.68rem;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        white-space: nowrap;
        padding: 10px 12px;
    }

    .wc-table td {
        padding: 12px;
        vertical-align: middle;
    }

    .wc-main {
        color: #0f172a;
        font-weight: 900;
        line-height: 1.2;
    }

    .wc-sub {
        color: #64748b;
        font-size: 0.74rem;
        line-height: 1.3;
        margin-top: 2px;
    }

    .wc-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 0.66rem;
        font-weight: 950;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .wc-badge.pending {
        background: #fff7ed;
        color: #c2410c;
    }

    .wc-badge.approved {
        background: #ecfdf3;
        color: #15803d;
    }

    .wc-badge.rejected {
        background: #fee2e2;
        color: #b91c1c;
    }

    .wc-badge.eligible {
        background: #e8f5ff;
        color: #0f4c81;
    }

    .wc-badge.expired,
    .wc-badge.ineligible {
        background: #f1f5f9;
        color: #475569;
    }

    .wc-badge-row {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        align-items: center;
    }

    .wc-empty {
        text-align: center;
        color: #64748b;
        padding: 42px 16px;
    }

    @media (max-width: 992px) {
        .wc-filter-form {
            grid-template-columns: 1fr 1fr;
        }

        .wc-table {
            min-width: 880px;
        }
    }

    @media (max-width: 576px) {
        .wc-filter-form {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="wc-page">
    <div class="wc-card">
        <div class="wc-card-header">
            <div>
                <h5 class="wc-card-title"><i class="fas fa-filter text-primary me-2"></i>Filter Warranty Claims</h5>
                <p class="wc-card-subtitle">Search by claim number, client, job order, or issue description.</p>
            </div>
        </div>

        <div class="wc-card-body">
            <form method="GET" class="wc-filter-form">
                <div>
                    <label class="form-label">Search</label>
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Claim no., client, job order, issue..."
                        value="{{ request('search') }}"
                    >
                </div>

                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                        <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">Eligibility</label>
                    <select name="eligibility_status" class="form-select">
                        <option value="">All eligibility</option>
                        <option value="eligible" @selected(request('eligibility_status') === 'eligible')>Eligible</option>
                        <option value="expired" @selected(request('eligibility_status') === 'expired')>Expired</option>
                        <option value="ineligible" @selected(request('eligibility_status') === 'ineligible')>Ineligible</option>
                    </select>
                </div>

                <button class="btn btn-primary" title="Apply filters">
                    <i class="fas fa-magnifying-glass"></i>
                </button>

                <a href="{{ route('admin.warranty-claims.index') }}" class="btn btn-outline-secondary">
                    Reset
                </a>
            </form>
        </div>
    </div>

    <div class="wc-card">
        <div class="wc-card-header">
            <div>
                <h5 class="wc-card-title"><i class="fas fa-shield-halved text-primary me-2"></i>Warranty Claim Requests</h5>
                <p class="wc-card-subtitle">Showing {{ $claims->count() }} of {{ $claims->total() }} claim(s).</p>
            </div>
        </div>

        <div class="wc-card-body p-0">
            <div class="table-responsive">
                <table class="table wc-table align-middle">
                    <thead>
                        <tr>
                            <th>Claim</th>
                            <th>Client / Job Order</th>
                            <th>Issue</th>
                            <th>Status</th>
                            <th>Warranty Until</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($claims as $claim)
                            @php
                                $clientName = $claim->client->name ?? 'Unknown client';
                                $jobNo = $claim->jobOrder->job_order_no ?? 'No job order';
                            @endphp
                            <tr>
                                <td>
                                    <div class="wc-main">{{ $claim->claim_no }}</div>
                                    <div class="wc-sub">Submitted {{ optional($claim->created_at)->format('Y-m-d h:i A') }}</div>
                                </td>
                                <td>
                                    <div class="wc-main">{{ $clientName }}</div>
                                    <div class="wc-sub">{{ $jobNo }}</div>
                                </td>
                                <td>
                                    <div class="wc-sub" style="max-width: 360px;">
                                        {{ \Illuminate\Support\Str::limit($claim->issue_description, 110) }}
                                    </div>
                                </td>
                                <td>
                                    <div class="wc-badge-row">
                                        <span class="wc-badge {{ $claim->status }}">{{ ucfirst($claim->status) }}</span>
                                        <span class="wc-badge {{ $claim->eligibility_status }}">{{ ucfirst(str_replace('_', ' ', $claim->eligibility_status)) }}</span>
                                    </div>
                                    @if ($claim->backJob)
                                        <div class="wc-sub mt-1">Backjob: {{ $claim->backJob->backjob_no }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if ($claim->warranty_expires_at)
                                        <div class="wc-main">{{ optional($claim->warranty_expires_at)->format('Y-m-d') }}</div>
                                        <div class="wc-sub">{{ optional($claim->warranty_expires_at)->diffForHumans() }}</div>
                                    @else
                                        <span class="text-muted">Not set</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.warranty-claims.show', $claim) }}" class="btn btn-sm btn-outline-primary">
                                        Review
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="wc-empty">
                                        <i class="fas fa-shield-halved fa-2x text-primary mb-2"></i>
                                        <div class="fw-bold text-dark">No warranty claims found</div>
                                        <div>Client warranty requests will appear here once submitted.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3">
                {{ $claims->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
