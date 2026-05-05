@extends('hr.layouts.app')

@section('title', 'Generate Receipt')

@section('content')
<style>
    .receipt-create-card,
    .receipt-preview-card {
        border: 0;
        border-radius: 24px;
        box-shadow: 0 14px 36px rgba(15, 23, 42, 0.08);
    }

    .receipt-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .receipt-summary-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 14px 16px;
    }

    .receipt-summary-label {
        color: #64748b;
        font-size: 0.76rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 4px;
    }

    .receipt-summary-value {
        color: #0f172a;
        font-weight: 900;
        line-height: 1.45;
    }

    .receipt-preview-card {
        position: sticky;
        top: 96px;
        background: linear-gradient(135deg, #ffffff, #f8fbff);
    }

    .receipt-preview-header {
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 16px;
        margin-bottom: 16px;
    }

    .preview-title {
        color: #0f4c81;
        font-weight: 950;
        letter-spacing: -0.04em;
        margin: 0;
    }

    .preview-total {
        font-size: 1.8rem;
        font-weight: 950;
        color: #0f172a;
    }

    .form-label {
        font-weight: 800;
        color: #334155;
    }

    .form-control {
        border-radius: 14px;
        padding: 12px 14px;
    }

    .btn-receipt-submit {
        border-radius: 14px;
        padding: 12px 16px;
        font-weight: 900;
    }

    @media (max-width: 991.98px) {
        .receipt-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .receipt-preview-card {
            position: static;
        }
    }

    @media (max-width: 575.98px) {
        .receipt-summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="page-header-card mb-4">
    <h2 class="mb-1">Generate Receipt</h2>
    <p class="text-muted mb-0">Create an official receipt for a confirmed payment.</p>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card receipt-create-card mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
            <div>
                <h5 class="fw-bold mb-1">Payment Summary</h5>
                <p class="text-muted mb-0">This information will be used in the official receipt.</p>
            </div>

            <span class="badge bg-success text-uppercase px-3 py-2">
                {{ $payment->status }}
            </span>
        </div>

        <div class="receipt-summary-grid">
            <div class="receipt-summary-item">
                <div class="receipt-summary-label">Payment No.</div>
                <div class="receipt-summary-value">{{ $payment->payment_no }}</div>
            </div>

            <div class="receipt-summary-item">
                <div class="receipt-summary-label">Invoice No.</div>
                <div class="receipt-summary-value">{{ $payment->invoice->invoice_no ?? '—' }}</div>
            </div>

            <div class="receipt-summary-item">
                <div class="receipt-summary-label">Client</div>
                <div class="receipt-summary-value">{{ $payment->invoice->quotation->request->full_name ?? '—' }}</div>
            </div>

            <div class="receipt-summary-item">
                <div class="receipt-summary-label">Amount</div>
                <div class="receipt-summary-value">PHP {{ number_format((float) $payment->amount, 2) }}</div>
            </div>

            <div class="receipt-summary-item">
                <div class="receipt-summary-label">Schedule</div>
                <div class="receipt-summary-value">{{ $payment->paymentSchedule->label ?? '—' }}</div>
            </div>

            <div class="receipt-summary-item">
                <div class="receipt-summary-label">Method</div>
                <div class="receipt-summary-value">{{ $payment->payment_method ?? '—' }}</div>
            </div>

            <div class="receipt-summary-item">
                <div class="receipt-summary-label">Reference No.</div>
                <div class="receipt-summary-value">{{ $payment->reference_number ?? '—' }}</div>
            </div>

            <div class="receipt-summary-item">
                <div class="receipt-summary-label">Payment Date</div>
                <div class="receipt-summary-value">{{ optional($payment->payment_date)->format('M d, Y') ?? '—' }}</div>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('hr.receipts.store') }}">
    @csrf
    <input type="hidden" name="payment_id" value="{{ $payment->id }}">

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card receipt-create-card">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Receipt Details</h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Receipt Date</label>
                            <input
                                type="date"
                                name="receipt_date"
                                class="form-control"
                                value="{{ old('receipt_date', now()->format('Y-m-d')) }}"
                                required
                            >
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea
                                name="notes"
                                class="form-control"
                                rows="5"
                                placeholder="Optional receipt notes..."
                            >{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card receipt-preview-card">
                <div class="card-body">
                    <div class="receipt-preview-header">
                        <div class="small text-muted fw-bold text-uppercase">Receipt Preview</div>
                        <h4 class="preview-title">WRPlumb Receipt</h4>
                    </div>

                    <div class="mb-3">
                        <div class="small text-muted">Billed To</div>
                        <div class="fw-bold">{{ $payment->invoice->quotation->request->full_name ?? '—' }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-muted">Payment For</div>
                        <div class="fw-bold">
                            {{ $payment->paymentSchedule->label ?? 'Invoice Payment' }}
                            <div class="small text-muted">{{ $payment->invoice->invoice_no ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-muted">Total Paid</div>
                        <div class="preview-total">PHP {{ number_format((float) $payment->amount, 2) }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="small text-muted">Payment Method</div>
                        <div class="fw-bold">{{ $payment->payment_method ?? '—' }}</div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-receipt-submit">
                            <i class="fas fa-receipt me-2"></i>Generate Receipt
                        </button>
                        <a href="{{ route('hr.payments.show', $payment) }}" class="btn btn-outline-secondary btn-receipt-submit">
                            Back to Payment
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
