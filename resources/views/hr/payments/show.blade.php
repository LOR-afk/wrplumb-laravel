@extends('hr.layouts.app')

@section('title', 'Payment Details')

@section('content')
<div class="page-header-card mb-4">
    <h2 class="mb-1">Payment Details</h2>
    <p class="text-muted mb-0">Review the recorded payment information.</p>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

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
                <div class="small text-muted">Client</div>
                <div class="fw-semibold">{{ $payment->invoice->quotation->request->full_name ?? '—' }}</div>
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
            <div class="col-md-4">
                <div class="small text-muted">Reference Number</div>
                <div class="fw-semibold">{{ $payment->reference_number ?? '—' }}</div>
            </div>
            <div class="col-md-4">
                <div class="small text-muted">Received By</div>
                <div class="fw-semibold">{{ $payment->receiver->name ?? $payment->receiver->first_name ?? '—' }}</div>
            </div>

            @if (!empty($payment->notes))
                <div class="col-12">
                    <div class="small text-muted">Notes</div>
                    <div class="fw-semibold">{{ $payment->notes }}</div>
                </div>
            @endif

            @if (!empty($payment->proof_path))
            <div class="col-md-4">
                <div class="small text-muted">Proof of Payment</div>
                <div class="fw-semibold">
                    <a href="{{ asset('storage/' . $payment->proof_path) }}" target="_blank">View Uploaded Proof</a>
                </div>
            </div>
        @endif

        <div class="col-md-4">
            <div class="small text-muted">Submitted By</div>
            <div class="fw-semibold">{{ $payment->submitter->name ?? $payment->submitter->first_name ?? '—' }}</div>
        </div>

        <div class="col-md-4">
            <div class="small text-muted">Verified By</div>
            <div class="fw-semibold">{{ $payment->verifier->name ?? $payment->verifier->first_name ?? '—' }}</div>
        </div>

        @if (!empty($payment->verification_notes))
            <div class="col-12">
                <div class="small text-muted">Verification Notes</div>
                <div class="fw-semibold">{{ $payment->verification_notes }}</div>
            </div>
        @endif
                </div>
            </div>
        </div>

<div class="d-flex flex-wrap gap-2">
    <a href="{{ route('hr.payments.index') }}" class="btn btn-outline-secondary">
        Back to Payments
    </a>

    <a href="{{ route('hr.invoices.show', $payment->invoice) }}" class="btn btn-outline-primary">
        View Invoice
    </a>

    @if ($payment->receipt)
        <a href="{{ route('hr.receipts.show', $payment->receipt) }}" class="btn btn-outline-dark">
            View Receipt
        </a>
    @elseif ($payment->status === 'confirmed')
        <a href="{{ route('hr.receipts.create', $payment) }}" class="btn btn-dark">
            Generate Receipt
        </a>
    @endif
</div>

@if ($payment->status === 'pending_verification')
    <div class="card border-0 shadow-sm rounded-4 mt-4">
        <div class="card-body">
            <h5 class="mb-3">Reject Payment</h5>

            <form method="POST" action="{{ route('hr.payments.reject', $payment) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Reason for Rejection</label>
                    <textarea
                        name="verification_notes"
                        class="form-control"
                        rows="3"
                        placeholder="Enter reason for rejection..."
                        required
                    ></textarea>
                </div>

                <button type="submit" class="btn btn-danger">
                    Reject Payment
                </button>
            </form>
        </div>
    </div>
@endif
@endsection