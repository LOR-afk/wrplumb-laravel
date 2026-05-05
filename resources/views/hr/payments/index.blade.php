@extends('hr.layouts.app')

@section('title', 'Payments')

@section('content')
<style>
    .payment-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .payment-stat-card,
    .payment-filter-card,
    .client-payment-card {
        background: #fff;
        border: 1px solid #e3ebf3;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    }

    .payment-stat-card {
        padding: 18px;
    }

    .payment-stat-label {
        color: #64748b;
        font-size: 0.82rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 8px;
    }

    .payment-stat-value {
        color: #0f172a;
        font-size: 1.45rem;
        font-weight: 900;
        line-height: 1;
    }

    .payment-filter-card {
        padding: 18px;
        margin-bottom: 18px;
    }

    .client-payment-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .client-payment-card {
        overflow: hidden;
    }

    .client-payment-card summary {
        list-style: none;
        cursor: pointer;
    }

    .client-payment-card summary::-webkit-details-marker {
        display: none;
    }

    .client-summary {
        display: grid;
        grid-template-columns: 1.7fr repeat(3, minmax(140px, 1fr)) 42px;
        gap: 16px;
        align-items: center;
        padding: 18px 20px;
    }

    .client-summary:hover {
        background: #fbfdff;
    }

    .client-name {
        font-size: 1rem;
        font-weight: 900;
        color: #0f172a;
        margin-bottom: 4px;
    }

    .client-subtext {
        color: #64748b;
        font-size: 0.88rem;
    }

    .summary-label {
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 5px;
    }

    .summary-value {
        font-weight: 900;
        color: #0f172a;
    }

    .summary-arrow {
        width: 38px;
        height: 38px;
        border-radius: 14px;
        background: #eef6ff;
        color: #1d9bf0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s ease;
    }

    .client-payment-card[open] .summary-arrow {
        transform: rotate(180deg);
    }

    .client-payment-body {
        border-top: 1px solid #e3ebf3;
        background: #fcfdff;
        padding: 18px;
    }

    .invoice-card {
        background: #fff;
        border: 1px solid #e3ebf3;
        border-radius: 18px;
        padding: 18px;
        margin-bottom: 16px;
    }

    .invoice-card:last-child {
        margin-bottom: 0;
    }

    .invoice-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 14px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .invoice-title {
        font-size: 1rem;
        font-weight: 900;
        color: #0f172a;
        margin-bottom: 4px;
    }

    .invoice-meta {
        color: #64748b;
        font-size: 0.88rem;
    }

    .progress-wrap {
        background: #f8fbff;
        border: 1px solid #edf2f7;
        border-radius: 18px;
        padding: 14px;
        margin-bottom: 16px;
    }

    .payment-progress {
        height: 12px;
        border-radius: 999px;
        background: #e5edf5;
        overflow: hidden;
        margin: 10px 0;
    }

    .payment-progress-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #1d9bf0, #22c55e);
    }

    .schedule-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 16px;
    }

    .schedule-step {
        border: 1px solid #e3ebf3;
        border-radius: 16px;
        padding: 14px;
        background: #fff;
    }

    .schedule-step-title {
        font-weight: 900;
        color: #0f172a;
        margin-bottom: 6px;
    }

    .schedule-step-sub {
        color: #64748b;
        font-size: 0.84rem;
        line-height: 1.45;
    }

    .payment-history-table {
        border: 1px solid #e3ebf3;
        border-radius: 16px;
        overflow: hidden;
    }

    .payment-history-table table {
        margin-bottom: 0;
    }

    .payment-history-table thead th {
        background: #f8fbff;
        color: #334155;
        font-weight: 900;
        font-size: 0.84rem;
        white-space: nowrap;
    }

    .money-cell {
        font-weight: 900;
        text-align: right;
        white-space: nowrap;
    }

    .chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 7px 11px;
        font-size: 0.78rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .chip-green { background: #dcfce7; color: #166534; }
    .chip-red { background: #fee2e2; color: #b91c1c; }
    .chip-orange { background: #fff7ed; color: #c2410c; }
    .chip-blue { background: #eaf4ff; color: #1d4ed8; }
    .chip-gray { background: #eef2f7; color: #475569; }
    .chip-dark { background: #0f172a; color: #fff; }

    @media (max-width: 1199.98px) {
        .client-summary {
            grid-template-columns: 1fr 1fr;
        }

        .summary-arrow {
            justify-self: end;
        }
    }

    @media (max-width: 991.98px) {
        .payment-stats-grid,
        .schedule-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .payment-stats-grid,
        .client-summary,
        .schedule-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

@php
    $paymentStats = $paymentStats ?? [
        'total_payments' => 0,
        'confirmed_count' => 0,
        'pending_count' => 0,
        'rejected_count' => 0,
        'collected_amount' => 0,
    ];

    $statusChip = function (?string $status) {
        return match ($status) {
            'confirmed', 'paid' => 'chip-green',
            'rejected' => 'chip-red',
            'pending', 'partial' => 'chip-orange',
            'pending_verification' => 'chip-blue',
            default => 'chip-gray',
        };
    };

    $clientGroups = $paymentInvoices->getCollection()->groupBy(function ($invoice) {
        $request = $invoice->quotation?->request;
        return $request?->id ?? ($request?->full_name ?? 'Unknown Client');
    });
@endphp

<div class="page-header-card mb-4">
    <h2 class="mb-1">Payments</h2>
    <p class="text-muted mb-0">Monitor client payments by invoice, schedule, progress, and transaction history.</p>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="payment-stats-grid">
    <div class="payment-stat-card">
        <div class="payment-stat-label">Total Payments</div>
        <div class="payment-stat-value">{{ number_format($paymentStats['total_payments']) }}</div>
    </div>

    <div class="payment-stat-card">
        <div class="payment-stat-label">Confirmed</div>
        <div class="payment-stat-value text-success">{{ number_format($paymentStats['confirmed_count']) }}</div>
    </div>

    <div class="payment-stat-card">
        <div class="payment-stat-label">Pending Review</div>
        <div class="payment-stat-value text-warning">{{ number_format($paymentStats['pending_count']) }}</div>
    </div>

    <div class="payment-stat-card">
        <div class="payment-stat-label">Total Collected</div>
        <div class="payment-stat-value">PHP {{ number_format((float) $paymentStats['collected_amount'], 2) }}</div>
    </div>
</div>

<div class="payment-filter-card">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-lg-3 col-md-6">
            <label class="form-label">Search</label>
            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Client, invoice, payment no., reference...">
        </div>

        <div class="col-lg-2 col-md-6">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                <option value="confirmed" @selected(request('status') === 'confirmed')>Confirmed</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                <option value="pending_verification" @selected(request('status') === 'pending_verification')>Pending Verification</option>
                <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
            </select>
        </div>

        <div class="col-lg-2 col-md-6">
            <label class="form-label">Method</label>
            <select name="method" class="form-select">
                <option value="">All</option>
                <option value="Cash" @selected(request('method') === 'Cash')>Cash</option>
                <option value="GCash" @selected(request('method') === 'GCash')>GCash</option>
                <option value="Bank Transfer" @selected(request('method') === 'Bank Transfer')>Bank Transfer</option>
            </select>
        </div>

        <div class="col-lg-2 col-md-6">
            <label class="form-label">Schedule</label>
            <input type="text" name="schedule" class="form-control" value="{{ request('schedule') }}" placeholder="Downpayment, Final...">
        </div>

        <div class="col-lg-3 col-md-12 d-flex gap-2">
            <button class="btn btn-primary flex-fill">
                <i class="fas fa-filter me-1"></i> Apply
            </button>
            <a href="{{ route('hr.payments.index') }}" class="btn btn-outline-secondary flex-fill">Reset</a>
        </div>
    </form>
</div>

@if ($paymentInvoices->count())
    <div class="client-payment-list">
        @foreach ($clientGroups as $clientKey => $invoices)
            @php
                $firstInvoice = $invoices->first();
                $request = $firstInvoice->quotation?->request;
                $clientName = $request?->full_name ?? trim(($request->first_name ?? '') . ' ' . ($request->last_name ?? '')) ?: 'Unknown Client';
                $clientEmail = $request?->email ?? null;
                $clientPhone = $request?->phone ?? null;
                $clientPayments = $invoices->flatMap->payments;
                $clientConfirmedPaid = (float) $clientPayments->where('status', 'confirmed')->sum('amount');
                $clientLatestPayment = $clientPayments->sortByDesc('payment_date')->first();
                $clientHasRejected = $clientPayments->where('status', 'rejected')->isNotEmpty();
            @endphp

            <details class="client-payment-card" @if ($loop->first) open @endif>
                <summary class="client-summary">
                    <div>
                        <div class="client-name">{{ $clientName }}</div>
                        <div class="client-subtext">
                            {{ $clientEmail ?? 'No email' }} @if ($clientPhone) • {{ $clientPhone }} @endif
                        </div>
                    </div>

                    <div>
                        <div class="summary-label">Invoices</div>
                        <div class="summary-value">{{ $invoices->count() }}</div>
                    </div>

                    <div>
                        <div class="summary-label">Confirmed Paid</div>
                        <div class="summary-value">PHP {{ number_format($clientConfirmedPaid, 2) }}</div>
                    </div>

                    <div>
                        <div class="summary-label">Latest Payment</div>
                        <div class="summary-value">
                            {{ optional($clientLatestPayment?->payment_date)->format('M d, Y') ?? '—' }}
                        </div>
                    </div>

                    <div class="summary-arrow">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </summary>

                <div class="client-payment-body">
                    @if ($clientHasRejected)
                        <div class="alert alert-danger py-2 px-3 mb-3">
                            <i class="fas fa-triangle-exclamation me-1"></i>
                            This client has at least one rejected payment that may need review.
                        </div>
                    @endif

                    @foreach ($invoices as $invoice)
                        @php
                            $totalAmount = (float) ($invoice->total_amount ?? 0);
                            $schedulePaid = (float) $invoice->paymentSchedules->sum('amount_paid');
                            $confirmedPaid = (float) $invoice->payments->where('status', 'confirmed')->sum('amount');
                            $paidAmount = $schedulePaid > 0 ? $schedulePaid : $confirmedPaid;
                            $remaining = max($totalAmount - $paidAmount, 0);
                            $progress = $totalAmount > 0 ? min(100, round(($paidAmount / $totalAmount) * 100, 2)) : 0;
                            $invoiceHasRejected = $invoice->payments->where('status', 'rejected')->isNotEmpty();
                            $invoiceHasPending = $invoice->payments->whereIn('status', ['pending', 'pending_verification'])->isNotEmpty();
                            $overallLabel = $invoiceHasRejected
                                ? 'Needs Review'
                                : ($progress >= 100 ? 'Fully Paid' : ($paidAmount > 0 ? 'Partially Paid' : 'Unpaid'));
                            $overallClass = $invoiceHasRejected
                                ? 'chip-red'
                                : ($progress >= 100 ? 'chip-green' : ($paidAmount > 0 ? 'chip-orange' : 'chip-gray'));
                            $planLabel = $invoice->paymentSchedules->pluck('label')->filter()->implode(' + ') ?: 'No payment schedule';
                        @endphp

                        <div class="invoice-card">
                            <div class="invoice-top">
                                <div>
                                    <div class="invoice-title">{{ $invoice->invoice_no }}</div>
                                    <div class="invoice-meta">
                                        Payment Plan: {{ $planLabel }}
                                    </div>
                                </div>

                                <div class="d-flex gap-2 flex-wrap">
                                    <span class="chip {{ $overallClass }}">{{ $overallLabel }}</span>
                                    @if ($invoiceHasPending)
                                        <span class="chip chip-blue">Pending Review</span>
                                    @endif
                                    <a href="{{ route('hr.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary">
                                        View Invoice
                                    </a>
                                </div>
                            </div>

                            <div class="progress-wrap">
                                <div class="d-flex justify-content-between flex-wrap gap-2">
                                    <strong>Invoice Payment Progress</strong>
                                    <span class="text-muted small">
                                        PHP {{ number_format($paidAmount, 2) }} / PHP {{ number_format($totalAmount, 2) }} paid
                                    </span>
                                </div>

                                <div class="payment-progress" aria-label="Invoice payment progress">
                                    <div class="payment-progress-fill" style="width: {{ $progress }}%;"></div>
                                </div>

                                <div class="d-flex justify-content-between flex-wrap gap-2 small text-muted">
                                    <span>{{ $progress }}% completed</span>
                                    <span>Remaining: PHP {{ number_format($remaining, 2) }}</span>
                                </div>
                            </div>

                            @if ($invoice->paymentSchedules->isNotEmpty())
                                <div class="schedule-grid">
                                    @foreach ($invoice->paymentSchedules as $schedule)
                                        @php
                                            $amountDue = (float) $schedule->amount_due;
                                            $amountPaid = (float) $schedule->amount_paid;
                                            $scheduleProgress = $amountDue > 0 ? min(100, round(($amountPaid / $amountDue) * 100, 2)) : 0;
                                            $schedulePercent = $totalAmount > 0 ? round(($amountDue / $totalAmount) * 100) : null;
                                        @endphp

                                        <div class="schedule-step">
                                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                                <div>
                                                    <div class="schedule-step-title">
                                                        {{ $schedule->label }}
                                                        @if ($schedulePercent)
                                                            <span class="text-muted">({{ $schedulePercent }}%)</span>
                                                        @endif
                                                    </div>
                                                    <div class="schedule-step-sub">
                                                        Due: PHP {{ number_format($amountDue, 2) }}<br>
                                                        Paid: PHP {{ number_format($amountPaid, 2) }}
                                                    </div>
                                                </div>
                                                <span class="chip {{ $statusChip($schedule->status) }}">
                                                    {{ ucfirst(str_replace('_', ' ', $schedule->status ?? 'unpaid')) }}
                                                </span>
                                            </div>

                                            <div class="payment-progress" style="height: 8px; margin-bottom: 0;">
                                                <div class="payment-progress-fill" style="width: {{ $scheduleProgress }}%;"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="payment-history-table table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Payment</th>
                                            <th>Schedule</th>
                                            <th class="text-end">Amount</th>
                                            <th>Method</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($invoice->payments as $payment)
                                            <tr>
                                                <td>
                                                    <strong>{{ $payment->payment_no }}</strong><br>
                                                    <small class="text-muted">Ref: {{ $payment->reference_number ?? '—' }}</small>
                                                </td>
                                                <td>
                                                    <span class="chip chip-gray">
                                                        <i class="fas fa-layer-group"></i>
                                                        {{ $payment->paymentSchedule->label ?? $payment->payment_type ?? '—' }}
                                                    </span>
                                                </td>
                                                <td class="money-cell">PHP {{ number_format((float) $payment->amount, 2) }}</td>
                                                <td>
                                                    <span class="chip chip-blue">
                                                        <i class="fas fa-money-bill-wave"></i>
                                                        {{ $payment->payment_method ?? '—' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="chip {{ $statusChip($payment->status) }}">
                                                        {{ ucfirst(str_replace('_', ' ', $payment->status)) }}
                                                    </span>
                                                </td>
                                                <td>{{ optional($payment->payment_date)->format('M d, Y') ?? '—' }}</td>
                                                <td class="text-end">
                                                    <a href="{{ route('hr.payments.show', $payment) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye me-1"></i> View Details
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </details>
        @endforeach
    </div>

    <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="text-muted small">
            Showing {{ $paymentInvoices->firstItem() }} to {{ $paymentInvoices->lastItem() }} of {{ $paymentInvoices->total() }} invoice groups
        </div>

        <div>
            {{ $paymentInvoices->links('pagination::bootstrap-5') }}
        </div>
    </div>
@else
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body text-center py-5 text-muted">
            <div class="mb-3" style="width:64px;height:64px;border-radius:18px;background:#eef6ff;color:#1d9bf0;display:inline-flex;align-items:center;justify-content:center;font-size:24px;">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="fw-bold text-dark mb-1">No payment records found</div>
            <div>Recorded client payments will appear here.</div>
        </div>
    </div>
@endif
@endsection
