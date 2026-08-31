@extends('client.layouts.app')

@section('title', 'Invoice Details')
@section('topbar_title', 'Invoice Details')
@section('topbar_subtitle', 'Review invoice charges, due dates, and payment status.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/client/invoice-show.css') }}?v=20260818a">
@endpush

@section('content')
@php
    $statusKey = strtolower((string) ($invoice->status ?? 'pending'));

    $statusClass = match ($statusKey) {
        'paid' => 'green',
        'partially_paid', 'partial' => 'orange',
        'unpaid', 'pending' => 'blue',
        'overdue' => 'red',
        'cancelled', 'canceled', 'void' => 'gray',
        default => 'gray',
    };

    $statusLabel = match ($statusKey) {
        'partially_paid' => 'Partially Paid',
        'cancelled', 'canceled' => 'Cancelled',
        default => ucfirst(str_replace('_', ' ', $statusKey)),
    };

    $serviceType = $invoice->quotation->request->service_type ?? 'Service';
    $serviceAddress = $invoice->quotation->request->address ?? '—';
@endphp

<div class="client-invoice-show-page">
    <section class="invoice-show-hero">
        <div class="invoice-show-hero-copy">
            <span>Invoice</span>

            <div class="invoice-show-title-row">
                <h2>{{ $invoice->invoice_no }}</h2>
                <em class="invoice-show-status {{ $statusClass }}">
                    {{ $statusLabel }}
                </em>
            </div>

            <p>{{ $serviceType }}</p>
        </div>

        <div class="invoice-show-actions">
            <a href="{{ route('client.invoices.index') }}" class="invoice-show-btn secondary">
                <i class="fas fa-arrow-left"></i>
                My Invoices
            </a>

            @if ($statusKey !== 'paid')
                <a href="{{ route('client.payments.create', $invoice) }}" class="invoice-show-btn primary">
                    <i class="fas fa-credit-card"></i>
                    Submit Payment
                </a>
            @endif
        </div>
    </section>

    <section class="invoice-show-summary">
        <article>
            <span>Invoice Date</span>
            <strong>{{ optional($invoice->invoice_date)->format('M d, Y') ?? '—' }}</strong>
        </article>

        <article>
            <span>Due Date</span>
            <strong>{{ optional($invoice->due_date)->format('M d, Y') ?? '—' }}</strong>
        </article>

        <article>
            <span>Status</span>
            <strong>{{ $statusLabel }}</strong>
        </article>

        <article>
            <span>Total Amount</span>
            <strong>PHP {{ number_format((float) $invoice->total_amount, 2) }}</strong>
        </article>
    </section>

    <div class="invoice-show-grid">
        <section class="invoice-show-card">
            <div class="invoice-show-card-head">
                <span class="invoice-show-card-icon blue">
                    <i class="fas fa-circle-info"></i>
                </span>

                <div>
                    <h3>Billing Information</h3>
                    <p>Service and invoice reference details.</p>
                </div>
            </div>

            <div class="invoice-show-card-body">
                <div class="invoice-info-grid">
                    <div>
                        <span>Invoice No.</span>
                        <strong>{{ $invoice->invoice_no }}</strong>
                    </div>

                    <div>
                        <span>Service Type</span>
                        <strong>{{ $serviceType }}</strong>
                    </div>

                    <div>
                        <span>Address</span>
                        <strong>{{ $serviceAddress }}</strong>
                    </div>

                    <div>
                        <span>Invoice Date</span>
                        <strong>{{ optional($invoice->invoice_date)->format('M d, Y') ?? '—' }}</strong>
                    </div>

                    <div>
                        <span>Due Date</span>
                        <strong>{{ optional($invoice->due_date)->format('M d, Y') ?? '—' }}</strong>
                    </div>

                    <div>
                        <span>Payment Status</span>
                        <strong>{{ $statusLabel }}</strong>
                    </div>
                </div>

                @if (!empty($invoice->description))
                    <div class="invoice-description-box">
                        <span>Description</span>
                        <p>{{ $invoice->description }}</p>
                    </div>
                @endif
            </div>
        </section>

        <aside class="invoice-total-card">
            <div class="invoice-total-card-head">
                <span class="invoice-show-card-icon green">
                    <i class="fas fa-receipt"></i>
                </span>

                <div>
                    <h3>Amount Due</h3>
                    <p>Invoice billing total.</p>
                </div>
            </div>

            <div class="invoice-total-card-body">
                <span>Total Invoice</span>
                <strong>PHP {{ number_format((float) $invoice->total_amount, 2) }}</strong>

                <em class="invoice-total-status {{ $statusClass }}">
                    {{ $statusLabel }}
                </em>
            </div>
        </aside>
    </div>

    <section class="invoice-show-card">
        <div class="invoice-show-card-head">
            <span class="invoice-show-card-icon violet">
                <i class="fas fa-list"></i>
            </span>

            <div>
                <h3>Invoice Items</h3>
                <p>Charges included in this billing record.</p>
            </div>
        </div>

        <div class="invoice-items-wrap">
            <table class="invoice-items-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($invoice->items as $item)
                        <tr>
                            <td>{{ $item->description }}</td>
                            <td>{{ number_format((float) $item->quantity, 2) }}</td>
                            <td>PHP {{ number_format((float) $item->unit_price, 2) }}</td>
                            <td class="text-end">
                                <strong>PHP {{ number_format((float) $item->total_price, 2) }}</strong>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="invoice-grand-total">
            <span>Total Amount</span>
            <strong>PHP {{ number_format((float) $invoice->total_amount, 2) }}</strong>
        </div>
    </section>
</div>
@endsection