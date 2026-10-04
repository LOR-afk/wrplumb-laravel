@extends('hr.layouts.app')

@section('title', 'Invoices')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/hr/index.invoices.css') }}?v=invoices-compact-01">
@endpush

@section('content')

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

                            $invoice = $invoice ?? null;

                            $totalAmount = $invoice?->total_amount ?? 0;

                            $paidAmount = $invoice?->paid_amount ?? 0;

                            $remainingBalance = $invoice?->remaining_balance ?? $totalAmount;

                            $progress = $invoice?->payment_progress ?? 0;

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

                                {{-- <div class="invoice-money-muted">Balance: PHP {{ number_format($remainingAmount, 2) }}</div> --}}

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
