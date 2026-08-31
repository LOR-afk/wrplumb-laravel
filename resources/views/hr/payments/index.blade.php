@extends('hr.layouts.app')

@section('title', 'HR Payments')
@section('topbar_title', 'HR Payments')
@section('topbar_subtitle', 'Manage client invoice payments and status.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/hr/payments.css') }}?v=modal-history-02">
@endpush

@section('content')
@php
    $paymentStats = $paymentStats ?? [];
    $activeTab = $tab ?? request('tab', 'all');

    $tabUrl = function (string $tabName) {
        return route(
            'hr.payments.index',
            array_merge(request()->except('page'), ['tab' => $tabName])
        );
    };

    $clientInitial = function (?string $name) {
        return strtoupper(substr(trim($name ?: 'Client'), 0, 1));
    };

    $paymentChip = function (?string $status) {
        return match ($status) {
            'confirmed', 'paid' => 'chip-green',
            'rejected' => 'chip-red',
            'pending', 'partial', 'partially_paid' => 'chip-orange',
            'pending_verification' => 'chip-blue',
            default => 'chip-gray',
        };
    };
@endphp

<div class="hr-payments-board">
    <section class="payments-board-header">
        <div>
            <h2>HR Payments</h2>
            <p>Manage client invoice payments and status.</p>
        </div>
    </section>

    @if (session('success'))
        <div class="alert alert-success mb-0">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mb-0">{{ $errors->first() }}</div>
    @endif

    <section class="payments-summary-grid">
        <article class="payment-summary-card">
            <span class="summary-icon blue"><i class="fas fa-file-invoice"></i></span>
            <div>
                <small>Total Invoices</small>
                <strong>{{ number_format($paymentStats['total_invoices'] ?? 0) }}</strong>
                <em>All generated invoices</em>
            </div>
        </article>

        <article class="payment-summary-card">
            <span class="summary-icon green"><i class="fas fa-circle-check"></i></span>
            <div>
                <small>Fully Paid</small>
                <strong>{{ number_format($paymentStats['fully_paid_count'] ?? 0) }}</strong>
                <em>Completed</em>
            </div>
        </article>

        <article class="payment-summary-card">
            <span class="summary-icon orange"><i class="fas fa-clock"></i></span>
            <div>
                <small>Partial Payments</small>
                <strong>{{ number_format($paymentStats['partial_count'] ?? 0) }}</strong>
                <em>In progress</em>
            </div>
        </article>

        <article class="payment-summary-card">
    <span class="summary-icon violet">
        <i class="fas fa-user-check"></i>
    </span>

    <div>
        <small>Pending Verification</small>
        <strong>
            {{ number_format($paymentStats['pending_count'] ?? 0) }}
        </strong>
        <em>Requires review</em>
    </div>
</article>

        <article class="payment-summary-card">
            <span class="summary-icon red"><i class="fas fa-circle-exclamation"></i></span>
            <div>
                <small>Needs Payment</small>
                <strong>{{ number_format($paymentStats['needs_payment_count'] ?? 0) }}</strong>
                <em>Unpaid invoices</em>
            </div>
        </article>

        <article class="payment-summary-card total-collected">
            <span class="summary-icon blue"><i class="fas fa-peso-sign"></i></span>
            <div>
                <small>Total Collected</small>
                <strong>
                    PHP {{ number_format(
                        (float) ($paymentStats['collected_amount'] ?? 0),
                        2
                    ) }}
                </strong>
                <em>Confirmed payments</em>
            </div>
        </article>
    </section>

    <section class="payments-filter-panel">
        <form
            method="GET"
            action="{{ route('hr.payments.index') }}"
            class="payments-filter-grid"
        >
            <input type="hidden" name="tab" value="{{ $activeTab }}">

            <div class="payments-search-box">
                <i class="fas fa-search"></i>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    class="form-control"
                    placeholder="Search client, invoice, payment no., or reference..."
                >
            </div>

            <select name="method" class="form-select">
                <option value="">All Methods</option>
                <option value="Cash" @selected(request('method') === 'Cash')>Cash</option>
                <option value="GCash" @selected(request('method') === 'GCash')>GCash</option>
                <option value="Bank Transfer" @selected(request('method') === 'Bank Transfer')>Bank Transfer</option>
                <option value="Check" @selected(request('method') === 'Check')>Check</option>
            </select>

            <input
                type="text"
                name="schedule"
                value="{{ request('schedule') }}"
                class="form-control"
                placeholder="Schedule type..."
            >

            <button class="btn btn-primary">
                <i class="fas fa-sliders me-1"></i>Filters
            </button>

            <a
                href="{{ route('hr.payments.index') }}"
                class="btn btn-outline-secondary"
            >
                Reset
            </a>
        </form>
    </section>

    <section class="client-payment-panel payment-client-selector">
        <div class="client-payment-panel-head">
            <div>
                <span>Payment History by Client</span>
                <h4>Client Accounts</h4>
                <p>Select a client to review invoices and payment activity.</p>
            </div>
        </div>

        <nav class="payment-tabs">
            <a href="{{ $tabUrl('all') }}" class="{{ $activeTab === 'all' ? 'active' : '' }}">All Clients</a>
            <a href="{{ $tabUrl('needs_payment') }}" class="{{ $activeTab === 'needs_payment' ? 'active' : '' }}">Needs Payment</a>
            <a href="{{ $tabUrl('partial') }}" class="{{ $activeTab === 'partial' ? 'active' : '' }}">Partial</a>
            <a href="{{ $tabUrl('fully_paid') }}" class="{{ $activeTab === 'fully_paid' ? 'active' : '' }}">Fully Paid</a>
        </nav>

        <div class="client-payment-list payment-client-list-clean">
            @forelse ($clientPaymentGroups as $group)
                @php
                    $serviceSummary = $group['service_types']->take(2)->implode(' · ');
                    $statusClass = str_replace('_', '-', $group['status_key']);
                    $modalId = 'client-payment-history-' . $group['key'];
                @endphp

                <button
                    type="button"
                    class="payment-client-row"
                    data-bs-toggle="modal"
                    data-bs-target="#{{ $modalId }}"
                >
                    <span class="payment-client-main">
                        <span class="client-payment-avatar">
                            {{ $clientInitial($group['client_name']) }}
                        </span>

                        <span class="payment-client-copy">
                            <span class="payment-client-title-row">
                                <strong>{{ $group['client_name'] }}</strong>
                                <em class="payment-status-pill {{ $statusClass }}">
                                    {{ $group['status_label'] }}
                                </em>
                            </span>

                            <small>{{ $group['client_email'] }}</small>
                            <span class="payment-client-service">{{ $serviceSummary ?: 'Service project' }}</span>
                        </span>
                    </span>

                    <span class="payment-client-summary">
                        <span class="payment-client-paid-line">
                            <span>
                                Paid
                                <strong>PHP {{ number_format($group['paid_amount'], 2) }}</strong>
                                of PHP {{ number_format($group['total_amount'], 2) }}
                            </span>
                            <b>{{ round($group['progress']) }}%</b>
                        </span>

                        <span class="payment-client-progress">
                            <i style="width: {{ $group['progress'] }}%;"></i>
                        </span>

                        <span class="payment-client-balance">
                            Remaining:
                            <strong class="{{ $group['remaining_amount'] > 0 ? 'text-danger' : 'text-success' }}">
                                PHP {{ number_format($group['remaining_amount'], 2) }}
                            </strong>
                        </span>
                    </span>

                    <span class="payment-client-chevron">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                </button>

                <div
                    class="modal fade payment-history-modal"
                    id="{{ $modalId }}"
                    tabindex="-1"
                    aria-hidden="true"
                >
                    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header payment-history-modal-head">
                                <div>
                                    <span>Payment History</span>
                                    <h5>{{ $group['client_name'] }}</h5>
                                    <p>{{ $group['client_email'] }} · {{ $serviceSummary ?: 'Service project' }}</p>
                                </div>

                                <div class="payment-history-modal-head-actions">
                                    <em class="payment-status-pill {{ $statusClass }}">
                                        {{ $group['status_label'] }}
                                    </em>
                                </div>
                            </div>

                            <div class="modal-body payment-history-modal-body">
                                <section class="payment-history-summary-card">
                                    <div class="payment-history-kpis">
                                        <div>
                                            <span>Total Contract</span>
                                            <strong>PHP {{ number_format($group['total_amount'], 2) }}</strong>
                                        </div>
                                        <div>
                                            <span>Total Paid</span>
                                            <strong class="text-success">PHP {{ number_format($group['paid_amount'], 2) }}</strong>
                                        </div>
                                        <div>
                                            <span>Remaining</span>
                                            <strong class="{{ $group['remaining_amount'] > 0 ? 'text-danger' : 'text-success' }}">
                                                PHP {{ number_format($group['remaining_amount'], 2) }}
                                            </strong>
                                        </div>
                                    </div>

                                    <div class="payment-history-progress-row">
                                        <div class="payment-history-progress-track">
                                            <i style="width: {{ $group['progress'] }}%;"></i>
                                        </div>
                                        <strong>{{ round($group['progress']) }}% Paid</strong>
                                    </div>
                                </section>

                                <section class="payment-history-section">
                                    <div class="payment-history-section-head">
                                        <div>
                                            <span>Billing</span>
                                            <h6>Invoices</h6>
                                        </div>
                                        <em>{{ $group['invoice_count'] }} record(s)</em>
                                    </div>

                                    <div class="payment-history-invoice-list">
                                        @foreach ($group['invoices'] as $invoice)
                                            @php
                                                $invoiceRequest = $invoice->quotation?->request;
                                            @endphp

                                            <article class="payment-history-invoice-row">
                                                <div>
                                                    <strong>{{ $invoice->invoice_no }}</strong>
                                                    <span>{{ $invoiceRequest?->service_type ?? 'Service' }}</span>
                                                </div>

                                                <div>
                                                    <span>Total Amount</span>
                                                    <strong>PHP {{ number_format((float) $invoice->total_amount, 2) }}</strong>
                                                </div>

                                                <a
                                                    href="{{ route('hr.invoices.show', $invoice) }}"
                                                    class="btn btn-sm btn-outline-primary"
                                                >
                                                    <i class="fas fa-eye me-1"></i>
                                                    View
                                                </a>
                                            </article>
                                        @endforeach
                                    </div>
                                </section>

                                <section class="payment-history-section">
                                    <div class="payment-history-section-head">
                                        <div>
                                            <span>Ledger</span>
                                            <h6>Payment Transactions</h6>
                                        </div>
                                        <em>{{ $group['payment_count'] }} transaction(s)</em>
                                    </div>

                                    <div class="payment-history-table-wrap">
                                        <table class="payment-history-table">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Payment No.</th>
                                                    <th>Method</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($group['payments'] as $payment)
                                                    <tr>
                                                        <td>{{ optional($payment->payment_date)->format('M d, Y') ?? '—' }}</td>
                                                        <td><strong>{{ $payment->payment_no }}</strong></td>
                                                        <td>{{ $payment->payment_method ?? '—' }}</td>
                                                        <td><strong>PHP {{ number_format((float) $payment->amount, 2) }}</strong></td>
                                                        <td>
                                                            <em class="payment-chip {{ $paymentChip($payment->status) }}">
                                                                {{ ucfirst(str_replace('_', ' ', $payment->status)) }}
                                                            </em>
                                                        </td>
                                                        <td>
                                                            <a
                                                                href="{{ route('hr.payments.show', $payment) }}"
                                                                class="btn btn-sm btn-outline-primary"
                                                            >
                                                                View
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6">
                                                            <div class="payment-empty-mini">No recorded payments yet.</div>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </section>
                            </div>

                            <div class="modal-footer payment-history-modal-footer">
                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    data-bs-dismiss="modal"
                                >
                                    Close
                                </button>

                                @if ($group['next_invoice'])
                                    <a
                                        href="{{ route('hr.payments.create', $group['next_invoice']) }}"
                                        class="btn btn-primary"
                                    >
                                        <i class="fas fa-plus me-1"></i>
                                        Record Payment
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="payment-empty-state">
                    <div><i class="fas fa-users"></i></div>
                    <h5>No client payment accounts found</h5>
                    <p>Client payment accounts will appear after invoices are generated.</p>
                </div>
            @endforelse
        </div>

        @if ($clientPaymentGroups->hasPages())
            <div class="payment-pagination inline-pagination">
                <div class="text-muted small">
                    Showing {{ $clientPaymentGroups->firstItem() }}
                    to {{ $clientPaymentGroups->lastItem() }}
                    of {{ $clientPaymentGroups->total() }} clients
                </div>
                <div>{{ $clientPaymentGroups->links('pagination::bootstrap-5') }}</div>
            </div>
        @endif
    </section>
</div>

@endsection