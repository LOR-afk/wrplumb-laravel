@extends('client.layouts.app')

@section('title', 'Payment Details')

@section('content')
<div class="page-header-card mb-4">
    <h2 class="mb-1">Payment Details</h2>
    <p class="text-muted mb-0">Review your payment record.</p>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="small text-muted">Payment No.</div>
                <div class="fw-semibold">{{ $payment->payment_no }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Invoice No.</div>
                <div class="fw-semibold">{{ $payment->invoice->invoice_no ?? '—' }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Payment Date</div>
                <div class="fw-semibold">{{ optional($payment->payment_date)->format('M d, Y') }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Status</div>
                <div class="fw-semibold text-uppercase">{{ $payment->status }}</div>
            </div>

            <div class="col-md-4">
                <div class="small text-muted">Payment Schedule</div>
                <div class="fw-semibold">{{ $payment->paymentSchedule->label ?? '—' }}</div>
            </div>
            <div class="col-md-4">
                <div class="small text-muted">Amount</div>
                <div class="fw-semibold">PHP {{ number_format((float) $payment->amount, 2) }}</div>
            </div>
            <div class="col-md-4">
                <div class="small text-muted">Method</div>
                <div class="fw-semibold">{{ $payment->payment_method ?? '—' }}</div>
            </div>

            <div class="col-md-6">
                <div class="small text-muted">Reference Number</div>
                <div class="fw-semibold">{{ $payment->reference_number ?? '—' }}</div>
            </div>

            <div class="col-md-6">
                <div class="small text-muted">Invoice Status</div>
                <div class="fw-semibold text-uppercase">{{ $payment->invoice->status ?? '—' }}</div>
            </div>

            @if (!empty($payment->proof_path))
                <div class="col-md-6">
                    <div class="small text-muted">Proof of Payment</div>
                    <div class="fw-semibold">
                        <a href="{{ asset('storage/' . $payment->proof_path) }}" target="_blank">View Uploaded Proof</a>
                    </div>
                </div>
            @endif

            @if (!empty($payment->verification_notes))
                <div class="col-md-6">
                    <div class="small text-muted">Verification Notes</div>
                    <div class="fw-semibold">{{ $payment->verification_notes }}</div>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="d-flex gap-2 flex-wrap">
    <a href="{{ route('client.payments.index') }}" class="btn btn-outline-secondary">Back to My Payments</a>
    <a href="{{ route('client.invoices.show', $payment->invoice) }}" class="btn btn-outline-primary">View Invoice</a>

    @if ($payment->receipt)
        <a href="{{ route('client.receipts.show', $payment->receipt) }}" class="btn btn-dark">View Receipt</a>
    @endif
</div>
@endsection