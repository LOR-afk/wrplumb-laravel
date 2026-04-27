@extends('client.layouts.app')

@section('title', 'Receipt Details')

@section('content')
<style>
    .receipt-wrapper {
        background: #fff;
        border: 1px solid #dbe5f1;
        border-radius: 20px;
        padding: 32px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
    }

    .receipt-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        gap: 20px;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 20px;
        margin-bottom: 24px;
    }

    .receipt-brand h2 {
        margin: 0;
        font-weight: 800;
        font-size: 1.5rem;
    }

    .receipt-brand p {
        margin: 6px 0 0;
        color: #64748b;
    }

    .receipt-meta {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .meta-box,
    .section-box {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 18px;
        background: #f8fafc;
    }

    .meta-label,
    .section-label {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        margin-bottom: 8px;
        font-weight: 700;
    }

    .meta-value,
    .section-value {
        color: #0f172a;
        font-weight: 600;
        line-height: 1.6;
    }

    .print-actions {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    @page {
        margin: 12mm;
    }

    @media print {
        body * {
            visibility: hidden;
        }

        .receipt-wrapper,
        .receipt-wrapper * {
            visibility: visible;
        }

        .receipt-wrapper {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
            box-shadow: none !important;
            border: none !important;
            background: #fff !important;
        }

        .no-print {
            display: none !important;
        }
    }
</style>

<div class="page-header-card mb-4 no-print">
    <h2 class="mb-1">Receipt Details</h2>
    <p class="text-muted mb-0">Review and print your receipt.</p>
</div>

<div class="print-actions no-print">
    <a href="{{ route('client.receipts.index') }}" class="btn btn-outline-secondary">Back to My Receipts</a>
    <a href="{{ route('client.payments.show', $receipt->payment) }}" class="btn btn-outline-primary">View Payment</a>
    <button type="button" class="btn btn-dark" onclick="window.print()">Print Receipt</button>
</div>

<div class="receipt-wrapper">
    <div class="receipt-header">
        <div class="receipt-brand">
            <h2>WR Plumbing and Construction Services</h2>
            <p>Official Receipt</p>
        </div>

        <div class="text-end">
            <div class="meta-label">Receipt No.</div>
            <div class="meta-value">{{ $receipt->receipt_no }}</div>
        </div>
    </div>

    <div class="receipt-meta">
        <div class="meta-box">
            <div class="meta-label">Receipt Date</div>
            <div class="meta-value">{{ optional($receipt->receipt_date)->format('F d, Y') ?? '—' }}</div>
        </div>

        <div class="meta-box">
            <div class="meta-label">Client</div>
            <div class="meta-value">{{ $receipt->payment->invoice->quotation->request->full_name ?? '—' }}</div>
        </div>

        <div class="meta-box">
            <div class="meta-label">Invoice No.</div>
            <div class="meta-value">{{ $receipt->payment->invoice->invoice_no ?? '—' }}</div>
        </div>

        <div class="meta-box">
            <div class="meta-label">Payment No.</div>
            <div class="meta-value">{{ $receipt->payment->payment_no ?? '—' }}</div>
        </div>

        <div class="meta-box">
            <div class="meta-label">Payment Schedule</div>
            <div class="meta-value">{{ $receipt->payment->paymentSchedule->label ?? '—' }}</div>
        </div>

        <div class="meta-box">
            <div class="meta-label">Issued At</div>
            <div class="meta-value">{{ optional($receipt->issued_at)->format('F d, Y h:i A') ?? '—' }}</div>
        </div>
    </div>

    <div class="section-box mb-4">
        <div class="section-label">Amount Received</div>
        <div class="section-value">PHP {{ number_format((float) $receipt->amount_received, 2) }}</div>
    </div>

    <div class="section-box mb-4">
        <div class="section-label">Payment Information</div>
        <div class="section-value">
            Method: {{ $receipt->payment_method ?? '—' }}<br>
            Reference Number: {{ $receipt->reference_number ?? '—' }}
        </div>
    </div>

    @if (!empty($receipt->notes))
        <div class="section-box mb-4">
            <div class="section-label">Notes</div>
            <div class="section-value">{{ $receipt->notes }}</div>
        </div>
    @endif

    <div class="section-box">
        <div class="section-label">Acknowledgement</div>
        <div class="section-value">
            This receipt confirms that the amount stated above has been received by WR Plumbing and Construction Services for the payment referenced in this document.
        </div>
    </div>
</div>
@endsection