@extends('client.layouts.app')

@section('title', 'Payment Details')
@section('topbar_title', 'Payment Details')
@section('topbar_subtitle', 'Review payment amount, method, status, and linked invoice.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/client/payment-show.css') }}?v=20260818a">
@endpush

@section('content')
@php
    $statusKey = strtolower((string) ($payment->status ?? 'pending'));

    $statusClass = match ($statusKey) {
        'confirmed', 'paid', 'approved' => 'green',
        'pending', 'submitted' => 'orange',
        'rejected', 'failed' => 'red',
        'cancelled', 'canceled' => 'gray',
        default => 'blue',
    };

    $statusLabel = match ($statusKey) {
        'cancelled', 'canceled' => 'Cancelled',
        default => ucfirst(str_replace('_', ' ', $statusKey)),
    };

    $invoiceStatusKey = strtolower((string) ($payment->invoice->status ?? 'pending'));

    $invoiceStatusLabel = match ($invoiceStatusKey) {
        'partially_paid' => 'Partially Paid',
        'cancelled', 'canceled' => 'Cancelled',
        default => ucfirst(str_replace('_', ' ', $invoiceStatusKey)),
    };
@endphp

<div class="client-payment-show-page">
    <section class="payment-show-hero">
        <div class="payment-show-hero-copy">
            <span>Payment Record</span>

            <div class="payment-show-title-row">
                <h2>{{ $payment->payment_no }}</h2>
                <em class="payment-show-status {{ $statusClass }}">
                    {{ $statusLabel }}
                </em>
            </div>

            <p>
                Invoice {{ $payment->invoice->invoice_no ?? '—' }}
            </p>
        </div>

        <div class="payment-show-actions">
            <a href="{{ route('client.payments.index') }}" class="payment-show-btn secondary">
                <i class="fas fa-arrow-left"></i>
                My Payments
            </a>

            <a href="{{ route('client.invoices.show', $payment->invoice) }}" class="payment-show-btn outline">
                <i class="fas fa-file-invoice-dollar"></i>
                View Invoice
            </a>

            @if ($payment->receipt)
                <a href="{{ route('client.receipts.show', $payment->receipt) }}" class="payment-show-btn primary">
                    <i class="fas fa-receipt"></i>
                    View Receipt
                </a>
            @endif
        </div>
    </section>

    <section class="payment-show-summary">
        <article>
            <span>Payment Date</span>
            <strong>{{ optional($payment->payment_date)->format('M d, Y') ?? '—' }}</strong>
        </article>

        <article>
            <span>Schedule</span>
            <strong>{{ $payment->paymentSchedule->label ?? '—' }}</strong>
        </article>

        <article>
            <span>Method</span>
            <strong>{{ $payment->payment_method ?? '—' }}</strong>
        </article>

        <article>
            <span>Amount</span>
            <strong>PHP {{ number_format((float) $payment->amount, 2) }}</strong>
        </article>
    </section>

    <div class="payment-show-grid">
        <section class="payment-show-card">
            <div class="payment-show-card-head">
                <span class="payment-show-card-icon blue">
                    <i class="fas fa-circle-info"></i>
                </span>

                <div>
                    <h3>Payment Information</h3>
                    <p>Reference and invoice details for this transaction.</p>
                </div>
            </div>

            <div class="payment-show-card-body">
                <div class="payment-info-grid">
                    <div>
                        <span>Payment No.</span>
                        <strong>{{ $payment->payment_no }}</strong>
                    </div>

                    <div>
                        <span>Invoice No.</span>
                        <strong>{{ $payment->invoice->invoice_no ?? '—' }}</strong>
                    </div>

                    <div>
                        <span>Payment Date</span>
                        <strong>{{ optional($payment->payment_date)->format('M d, Y') ?? '—' }}</strong>
                    </div>

                    <div>
                        <span>Payment Schedule</span>
                        <strong>{{ $payment->paymentSchedule->label ?? '—' }}</strong>
                    </div>

                    <div>
                        <span>Payment Method</span>
                        <strong>{{ $payment->payment_method ?? '—' }}</strong>
                    </div>

                    <div>
                        <span>Invoice Status</span>
                        <strong>{{ $invoiceStatusLabel }}</strong>
                    </div>

                    <div>
                        <span>Reference Number</span>
                        <strong>{{ $payment->reference_number ?? '—' }}</strong>
                    </div>

                    <div>
                        <span>Payment Status</span>
                        <strong>{{ $statusLabel }}</strong>
                    </div>
                </div>

                @if (!empty($payment->verification_notes))
                    <div class="payment-note-box">
                        <span>Verification Notes</span>
                        <p>{{ $payment->verification_notes }}</p>
                    </div>
                @endif
            </div>
        </section>

        <aside class="payment-amount-card">
            <div class="payment-show-card-head">
                <span class="payment-show-card-icon green">
                    <i class="fas fa-peso-sign"></i>
                </span>

                <div>
                    <h3>Payment Amount</h3>
                    <p>Recorded transaction value.</p>
                </div>
            </div>

            <div class="payment-amount-card-body">
                <span>Amount Paid</span>
                <strong>PHP {{ number_format((float) $payment->amount, 2) }}</strong>

                <em class="payment-amount-status {{ $statusClass }}">
                    {{ $statusLabel }}
                </em>

                <div class="payment-linked-invoice">
                    <span>Linked Invoice</span>
                    <strong>{{ $payment->invoice->invoice_no ?? '—' }}</strong>
                    <small>{{ $invoiceStatusLabel }}</small>
                </div>
            </div>
        </aside>
    </div>

    @if (!empty($payment->proof_path))
        <section class="payment-show-card">
            <div class="payment-show-card-head">
                <span class="payment-show-card-icon violet">
                    <i class="fas fa-file-image"></i>
                </span>

                <div>
                    <h3>Proof of Payment</h3>
                    <p>Uploaded supporting document for this transaction.</p>
                </div>
            </div>

            <div class="payment-proof-body">
                <div>
                    <strong>Payment proof is available.</strong>
                    <span>Open the uploaded file in a new tab for review.</span>
                </div>

                <a
                    href="{{ asset('storage/' . $payment->proof_path) }}"
                    target="_blank"
                    class="payment-proof-btn"
                >
                    <i class="fas fa-arrow-up-right-from-square"></i>
                    View Uploaded Proof
                </a>
            </div>
        </section>
    @endif
</div>
@endsection