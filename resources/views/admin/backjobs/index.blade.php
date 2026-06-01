@extends('admin.layouts.app')

@section('title', 'Backjobs - WRPlumb')
@section('topbar_title', 'Backjobs')
@section('topbar_subtitle', 'Monitor warranty-related return jobs and their resolution status.')

@push('styles')
<style>
    .bj-page {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .bj-card {
        background: #ffffff;
        border: 1px solid #dbe7f3;
        border-radius: 20px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.055);
        overflow: hidden;
    }

    .bj-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 15px 18px;
        border-bottom: 1px solid #dbe7f3;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    }

    .bj-card-title {
        margin: 0;
        color: #0f172a;
        font-weight: 950;
        font-size: 1.02rem;
    }

    .bj-card-subtitle {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 0.8rem;
    }

    .bj-card-body {
        padding: 16px 18px;
    }

    .bj-filter-form {
        display: grid;
        grid-template-columns: minmax(260px, 1.5fr) minmax(150px, 0.7fr) auto auto;
        gap: 10px;
        align-items: end;
    }

    .bj-filter-form .form-label {
        font-size: 0.72rem;
        font-weight: 850;
        color: #334155;
        margin-bottom: 5px;
    }

    .bj-table {
        margin-bottom: 0;
    }

    .bj-table th {
        color: #64748b;
        font-size: 0.68rem;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        white-space: nowrap;
        padding: 10px 12px;
    }

    .bj-table td {
        padding: 12px;
        vertical-align: middle;
    }

    .bj-main {
        color: #0f172a;
        font-weight: 900;
        line-height: 1.2;
    }

    .bj-sub {
        color: #64748b;
        font-size: 0.74rem;
        line-height: 1.3;
        margin-top: 2px;
    }

    .bj-badge {
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

    .bj-badge.scheduled {
        background: #e8f5ff;
        color: #0f4c81;
    }

    .bj-badge.in_progress {
        background: #fff7ed;
        color: #c2410c;
    }

    .bj-badge.resolved {
        background: #ecfdf3;
        color: #15803d;
    }

    .bj-badge.cancelled {
        background: #fee2e2;
        color: #b91c1c;
    }

    .bj-empty {
        text-align: center;
        color: #64748b;
        padding: 42px 16px;
    }

    @media (max-width: 992px) {
        .bj-filter-form {
            grid-template-columns: 1fr 1fr;
        }

        .bj-table {
            min-width: 880px;
        }
    }

    @media (max-width: 576px) {
        .bj-filter-form {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="bj-page">
    <div class="bj-card">
        <div class="bj-card-header">
            <div>
                <h5 class="bj-card-title"><i class="fas fa-filter text-primary me-2"></i>Filter Backjobs</h5>
                <p class="bj-card-subtitle">Search by backjob number, original job order, client, or reason.</p>
            </div>
        </div>

        <div class="bj-card-body">
            <form method="GET" class="bj-filter-form">
                <div>
                    <label class="form-label">Search</label>
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Backjob no., client, job order, reason..."
                        value="{{ request('search') }}"
                    >
                </div>

                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="scheduled" @selected(request('status') === 'scheduled')>Scheduled</option>
                        <option value="in_progress" @selected(request('status') === 'in_progress')>In Progress</option>
                        <option value="resolved" @selected(request('status') === 'resolved')>Resolved</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                    </select>
                </div>

                <button class="btn btn-primary" title="Apply filters">
                    <i class="fas fa-magnifying-glass"></i>
                </button>

                <a href="{{ route('admin.backjobs.index') }}" class="btn btn-outline-secondary">
                    Reset
                </a>
            </form>
        </div>
    </div>

    <div class="bj-card">
        <div class="bj-card-header">
            <div>
                <h5 class="bj-card-title"><i class="fas fa-rotate-left text-primary me-2"></i>Backjob Records</h5>
                <p class="bj-card-subtitle">Showing {{ $backJobs->count() }} of {{ $backJobs->total() }} backjob(s).</p>
            </div>
        </div>

        <div class="bj-card-body p-0">
            <div class="table-responsive">
                <table class="table bj-table align-middle">
                    <thead>
                        <tr>
                            <th>Backjob</th>
                            <th>Client / Claim</th>
                            <th>Original Job</th>
                            <th>Personnel</th>
                            <th>Schedule</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($backJobs as $backJob)
                            @php
                                $client = $backJob->warrantyClaim?->client;
                                $clientName = $client?->name ?? 'Unknown client';
                                $claimNo = $backJob->warrantyClaim?->claim_no ?? 'No claim';
                                $originalJobNo = $backJob->originalJobOrder?->job_order_no ?? 'No original job';
                                $workerName = $backJob->worker?->name ?? 'Not assigned';
                            @endphp

                            <tr>
                                <td>
                                    <div class="bj-main">{{ $backJob->backjob_no }}</div>
                                    <div class="bj-sub">Created {{ optional($backJob->created_at)->format('Y-m-d h:i A') }}</div>
                                </td>

                                <td>
                                    <div class="bj-main">{{ $clientName }}</div>
                                    <div class="bj-sub">{{ $claimNo }}</div>
                                </td>

                                <td>
                                    <div class="bj-main">{{ $originalJobNo }}</div>
                                    <div class="bj-sub">{{ \Illuminate\Support\Str::limit($backJob->reason, 70) }}</div>
                                </td>

                                <td>
                                    <div class="bj-main">{{ $workerName }}</div>
                                    <div class="bj-sub">
                                        {{ $backJob->worker?->role ? ucfirst($backJob->worker->role) : 'No role' }}
                                    </div>
                                </td>

                                <td>
                                    @if ($backJob->scheduled_date)
                                        <div class="bj-main">{{ optional($backJob->scheduled_date)->format('Y-m-d') }}</div>
                                        <div class="bj-sub">
                                            {{ $backJob->scheduled_time ? \Carbon\Carbon::parse($backJob->scheduled_time)->format('h:i A') : 'No time set' }}
                                        </div>
                                    @else
                                        <span class="text-muted">Not scheduled</span>
                                    @endif
                                </td>

                                <td>
                                    <span class="bj-badge {{ $backJob->status }}">
                                        {{ ucwords(str_replace('_', ' ', $backJob->status)) }}
                                    </span>
                                </td>

                                <td class="text-end">
                                    <a href="{{ route('admin.backjobs.show', $backJob) }}" class="btn btn-sm btn-outline-primary">
                                        Open
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="bj-empty">
                                        <i class="fas fa-rotate-left fa-2x text-primary mb-2"></i>
                                        <div class="fw-bold text-dark">No backjobs found</div>
                                        <div>Approved warranty claims can be converted into backjobs.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3">
                {{ $backJobs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
