@extends('hr.layouts.app')

@section('title', 'Receipt Details')

@section('content')
<style>
    .receipt-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
    }

    .receipt-paper {
        max-width: 980px;
        margin: 0 auto;
        background: #ffffff;
        border: 1px solid #dbe5f1;
        border-radius: 22px;
        padding: 42px;
        box-shadow: 0 18px 48px rgba(15, 23, 42, 0.10);
        color: #0f172a;
    }

    .receipt-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 24px;
        border-bottom: 3px solid #0f4c81;
        padding-bottom: 24px;
        margin-bottom: 28px;
    }

    .receipt-title {
        font-size: 2.4rem;
        letter-spacing: 0.08em;
        font-weight: 950;
        color: #0f4c81;
        margin: 0;
    }

    .company-block {
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }

    .receipt-logo {
        width: 74px;
        height: 74px;
        object-fit: cover;
        border-radius: 18px;
        border: 1px solid #dbe5f1;
        padding: 4px;
        background: #ffffff;
    }

    .company-name {
        font-weight: 950;
        font-size: 1.25rem;
        margin-bottom: 4px;
    }

    .company-details {
        color: #64748b;
        line-height: 1.5;
        font-size: 0.92rem;
    }

    .receipt-info-grid {
        display: grid;
        grid-template-columns: 1.3fr 0.9fr;
        gap: 22px;
        margin-bottom: 28px;
    }

    .receipt-box {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 18px;
        background: #f8fafc;
    }

    .receipt-label {
        font-size: 0.78rem;
        font-weight: 950;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 8px;
    }

    .receipt-value {
        color: #0f172a;
        font-weight: 800;
        line-height: 1.6;
    }

    .receipt-meta-stack {
        display: grid;
        gap: 12px;
    }

    .receipt-items {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 24px;
        overflow: hidden;
        border-radius: 16px;
    }

    .receipt-items th {
        background: #0f4c81;
        color: #ffffff;
        padding: 14px 16px;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        text-align: left;
    }

    .receipt-items th.text-end,
    .receipt-items td.text-end {
        text-align: right;
    }

    .receipt-items td {
        border-bottom: 1px solid #e2e8f0;
        padding: 14px 16px;
        color: #334155;
    }

    .receipt-items tbody tr:last-child td {
        border-bottom: 0;
    }

    .receipt-totals {
        max-width: 390px;
        margin-left: auto;
        display: grid;
        gap: 8px;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding: 10px 0;
        border-bottom: 1px solid #e2e8f0;
        color: #334155;
    }

    .total-row.grand {
        border-bottom: 0;
        background: #0f4c81;
        color: #ffffff;
        border-radius: 16px;
        padding: 16px 18px;
        margin-top: 8px;
        font-weight: 950;
        font-size: 1.1rem;
    }

    .payment-method-box {
        margin-top: 26px;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 18px;
        background: #f8fafc;
    }

    .receipt-footer-note {
        margin-top: 28px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
        color: #64748b;
        line-height: 1.65;
    }

    .signature-area {
        display: flex;
        justify-content: flex-end;
        margin-top: 46px;
    }

    .signature-box {
        width: 280px;
        text-align: center;
    }

    .signature-line {
        border-top: 1px solid #0f172a;
        padding-top: 8px;
        font-weight: 800;
    }

    @page {
        margin: 12mm;
    }

    @media print {
        body * {
            visibility: hidden;
        }

        .receipt-paper,
        .receipt-paper * {
            visibility: visible;
        }

        .receipt-paper {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            max-width: none;
            margin: 0;
            padding: 0;
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
        }

        .no-print {
            display: none !important;
        }
    }

    @media (max-width: 767.98px) {
        .receipt-paper {
            padding: 26px;
        }

        .receipt-top,
        .receipt-info-grid {
            grid-template-columns: 1fr;
            flex-direction: column;
        }

        .receipt-title {
            font-size: 2rem;
        }

        .receipt-items {
            font-size: 0.88rem;
        }
    }
</style>

<div class="page-header-card mb-4 no-print">
    <h2 class="mb-1">Receipt Details</h2>
    <p class="text-muted mb-0">Review, print, and issue the generated receipt.</p>
</div>

@if (session('success'))
    <div class="alert alert-success no-print">{{ session('success') }}</div>
@endif

@if (session('info'))
    <div class="alert alert-info no-print">{{ session('info') }}</div>
@endif

<div class="receipt-actions no-print">
    <a href="{{ route('hr.receipts.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Receipts
    </a>

    <a href="{{ route('hr.payments.show', $receipt->payment) }}" class="btn btn-outline-primary">
        <i class="fas fa-wallet me-1"></i> View Payment
    </a>

    @if ($receipt->payment && $receipt->payment->invoice)
        <a href="{{ route('hr.invoices.show', $receipt->payment->invoice) }}" class="btn btn-outline-dark">
            <i class="fas fa-file-invoice me-1"></i> View Invoice
        </a>
    @endif

    <button type="button" class="btn btn-dark" onclick="window.print()">
        <i class="fas fa-print me-1"></i> Print Receipt
    </button>
</div>

@php
    $payment = $receipt->payment;
    $invoice = $payment?->invoice;
    $request = $invoice?->quotation?->request;
    $schedule = $payment?->paymentSchedule;
    $clientName = $request->full_name ?? trim(($request->first_name ?? '') . ' ' . ($request->last_name ?? '')) ?: '—';
    $description = ($schedule->label ?? 'Invoice Payment') . ' - ' . ($invoice->invoice_no ?? 'Invoice');
    $amount = (float) $receipt->amount_received;
@endphp

<div class="receipt-paper">
    <div class="receipt-top">
        <div class="company-block">
            <img src="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}" alt="WRPlumb Logo" class="receipt-logo">
            <div>
                <div class="company-name">WR Plumbing and Construction Services</div>
                <div class="company-details">
                    139 Upper Zone 4 Bulua, Cagayan de Oro, Philippines<br>
                    Phone: (088) 850 5197<br>
                    Email: wrplumbingcon@gmail.com
                </div>
            </div>
        </div>

        <div class="text-end">
            <h1 class="receipt-title">RECEIPT</h1>
            <div class="small text-muted fw-bold">Official Payment Receipt</div>
        </div>
    </div>

    <div class="receipt-info-grid">
        <div class="receipt-box">
            <div class="receipt-label">Billed To</div>
            <div class="receipt-value">
                {{ $clientName }}<br>
                {{ $request->address ?? '—' }}<br>
                {{ $request->email ?? '—' }}<br>
                {{ $request->phone ?? '—' }}
            </div>
        </div>

        <div class="receipt-meta-stack">
            <div class="receipt-box">
                <div class="receipt-label">Receipt Number</div>
                <div class="receipt-value">{{ $receipt->receipt_no }}</div>
            </div>

            <div class="receipt-box">
                <div class="receipt-label">Date</div>
                <div class="receipt-value">{{ optional($receipt->receipt_date)->format('F d, Y') ?? '—' }}</div>
            </div>
        </div>
    </div>

    <table class="receipt-items">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-end">Cost per Unit</th>
                <th class="text-end">Qty</th>
                <th class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>{{ $description }}</strong><br>
                    <span class="text-muted small">
                        Payment No: {{ $payment->payment_no ?? '—' }}
                        @if($payment?->reference_number)
                            | Ref: {{ $payment->reference_number }}
                        @endif
                    </span>
                </td>
                <td class="text-end">PHP {{ number_format($amount, 2) }}</td>
                <td class="text-end">1</td>
                <td class="text-end">PHP {{ number_format($amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="receipt-totals">
        <div class="total-row">
            <span>Subtotal</span>
            <strong>PHP {{ number_format($amount, 2) }}</strong>
        </div>

        <div class="total-row">
            <span>Discount</span>
            <strong>PHP 0.00</strong>
        </div>

        <div class="total-row">
            <span>Tax</span>
            <strong>PHP 0.00</strong>
        </div>

        <div class="total-row grand">
            <span>Total Paid</span>
            <span>PHP {{ number_format($amount, 2) }}</span>
        </div>
    </div>

    <div class="payment-method-box">
        <div class="receipt-label">Payment Method</div>
        <div class="receipt-value">
            {{ $receipt->payment_method ?? $payment->payment_method ?? '—' }}
            @if($receipt->reference_number)
                <br><span class="text-muted">Reference Number: {{ $receipt->reference_number }}</span>
            @endif
        </div>
    </div>

    @if (!empty($receipt->notes))
        <div class="payment-method-box">
            <div class="receipt-label">Notes</div>
            <div class="receipt-value">{{ $receipt->notes }}</div>
        </div>
    @endif

    <div class="receipt-footer-note">
        This receipt confirms that the total paid amount above has been received by WR Plumbing and Construction Services for the referenced invoice/payment.
    </div>

    <div class="signature-area">
        <div class="signature-box">
            <div class="signature-line">
                {{ $receipt->issuer->name ?? $receipt->issuer->first_name ?? 'Authorized Representative' }}
            </div>
            <div class="small text-muted">Issued By</div>
            <div class="small text-muted">
                Issued at: {{ optional($receipt->issued_at)->format('F d, Y h:i A') ?? '—' }}
            </div>

            <div class="small text-muted">
                Printed at: <span id="printedAt"></span>
            </div>
        </div>
    </div>
</div>

<script>
    function updatePrintedAt() {
        const now = new Date();

        const formatted = now.toLocaleString('en-US', {
            month: 'long',
            day: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        });

        document.getElementById('printedAt').textContent = formatted;
    }

    updatePrintedAt();
    setInterval(updatePrintedAt, 1000);
</script>
@endsection
