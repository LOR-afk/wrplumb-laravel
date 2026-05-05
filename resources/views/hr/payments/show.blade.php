@extends('hr.layouts.app')

@section('title', 'Payment Details')

@section('content')
<style>
    .payment-show-page {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .payment-hero,
    .payment-card,
    .payment-danger-card {
        background: #ffffff;
        border: 1px solid #e3ebf3;
        border-radius: 22px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    }

    .payment-hero {
        padding: 24px;
        background: linear-gradient(135deg, #ffffff, #f8fbff);
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .payment-title {
        margin: 0 0 6px;
        font-weight: 900;
        color: #0f172a;
        font-size: 2rem;
    }

    .payment-subtitle {
        color: #64748b;
        margin: 0;
    }

    .payment-card,
    .payment-danger-card {
        padding: 22px;
    }

    .payment-danger-card {
        border-color: #fecaca;
        background: #fff7f7;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .detail-item {
        background: #f8fbff;
        border: 1px solid #e8f0f8;
        border-radius: 16px;
        padding: 14px;
    }

    .detail-label {
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 4px;
    }

    .detail-value {
        color: #0f172a;
        font-weight: 900;
        overflow-wrap: anywhere;
    }

    .amount-hero {
        padding: 18px;
        border-radius: 18px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        min-width: 240px;
    }

    .amount-label {
        color: #166534;
        font-weight: 800;
        font-size: 0.85rem;
        margin-bottom: 4px;
    }

    .amount-value {
        color: #0f172a;
        font-size: 1.45rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .section-title {
        font-size: 1.05rem;
        font-weight: 900;
        color: #0f172a;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .status-chip,
    .method-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 11px;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 900;
    }

    .status-chip.confirmed { background: #dcfce7; color: #166534; }
    .status-chip.pending { background: #fff7ed; color: #c2410c; }
    .status-chip.pending_verification { background: #e0f2fe; color: #075985; }
    .status-chip.rejected { background: #fee2e2; color: #991b1b; }
    .status-chip.default { background: #eef2f7; color: #475569; }

    .method-chip {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .notes-box {
        background: #f8fbff;
        border: 1px solid #e8f0f8;
        border-radius: 16px;
        padding: 14px;
        color: #334155;
        line-height: 1.6;
    }

    .proof-box {
        background: #f8fbff;
        border: 1px dashed #93c5fd;
        border-radius: 18px;
        padding: 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }

    @media (max-width: 1199.98px) {
        .detail-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

@php
    $statusClass = in_array($payment->status, ['confirmed', 'pending', 'pending_verification', 'rejected'])
        ? $payment->status
        : 'default';
    $statusLabel = $payment->status === 'pending_verification'
        ? 'For Verification'
        : ucfirst(str_replace('_', ' ', $payment->status));
@endphp

<div class="payment-show-page">
    <div class="payment-hero">
        <div>
            <h2 class="payment-title">Payment Details</h2>
            <p class="payment-subtitle">Review payment, invoice, receipt, and verification information.</p>
            <div class="mt-3 d-flex gap-2 flex-wrap">
                <span class="status-chip {{ $statusClass }}">{{ $statusLabel }}</span>
                <span class="method-chip">
                    <i class="fas fa-money-bill-wave"></i>
                    {{ $payment->payment_method ?? 'No method' }}
                </span>
            </div>
        </div>

        <div class="amount-hero">
            <div class="amount-label">Payment Amount</div>
            <div class="amount-value">PHP {{ number_format((float) $payment->amount, 2) }}</div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="payment-card">
        <div class="section-title">
            <i class="fas fa-circle-info text-primary"></i>
            Payment Information
        </div>

        <div class="detail-grid">
            <div class="detail-item">
                <div class="detail-label">Payment No.</div>
                <div class="detail-value">{{ $payment->payment_no }}</div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Invoice No.</div>
                <div class="detail-value">{{ $payment->invoice->invoice_no ?? '—' }}</div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Payment Date</div>
                <div class="detail-value">{{ optional($payment->payment_date)->format('M d, Y') ?? '—' }}</div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Payment Schedule</div>
                <div class="detail-value">{{ $payment->paymentSchedule->label ?? '—' }}</div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Client</div>
                <div class="detail-value">{{ $payment->invoice->quotation->request->full_name ?? '—' }}</div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Reference Number</div>
                <div class="detail-value">{{ $payment->reference_number ?? '—' }}</div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Received By</div>
                <div class="detail-value">{{ $payment->receiver->name ?? $payment->receiver->first_name ?? '—' }}</div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Verified By</div>
                <div class="detail-value">{{ $payment->verifier->name ?? $payment->verifier->first_name ?? '—' }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="payment-card h-100">
                <div class="section-title">
                    <i class="fas fa-note-sticky text-primary"></i>
                    Notes and Verification
                </div>

                <div class="mb-3">
                    <div class="detail-label mb-2">Payment Notes</div>
                    <div class="notes-box">{{ $payment->notes ?: 'No payment notes recorded.' }}</div>
                </div>

                <div>
                    <div class="detail-label mb-2">Verification Notes</div>
                    <div class="notes-box">{{ $payment->verification_notes ?: 'No verification notes recorded.' }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="payment-card h-100">
                <div class="section-title">
                    <i class="fas fa-file-shield text-primary"></i>
                    Proof and Receipt
                </div>

                <div class="proof-box mb-3">
                    <div>
                        <div class="fw-bold text-dark">Proof of Payment</div>
                        <div class="text-muted small">Uploaded client payment proof or transaction image.</div>
                    </div>

                    @if (!empty($payment->proof_path))
                        <a href="{{ asset('storage/' . $payment->proof_path) }}" target="_blank" class="btn btn-outline-primary">
                            <i class="fas fa-up-right-from-square me-1"></i> View Proof
                        </a>
                    @else
                        <span class="badge bg-light text-muted border">No proof uploaded</span>
                    @endif
                </div>

                <div class="d-grid gap-2">
                    @if ($payment->receipt)
                        <a href="{{ route('hr.receipts.show', $payment->receipt) }}" class="btn btn-dark">
                            <i class="fas fa-receipt me-1"></i> View Receipt
                        </a>
                    @elseif ($payment->status === 'confirmed')
                        <a href="{{ route('hr.receipts.create', $payment) }}" class="btn btn-dark">
                            <i class="fas fa-receipt me-1"></i> Generate Receipt
                        </a>
                    @else
                        <button type="button" class="btn btn-outline-secondary" disabled>
                            Receipt available after confirmation
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('hr.payments.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Payments
        </a>

        <a href="{{ route('hr.invoices.show', $payment->invoice) }}" class="btn btn-outline-primary">
            <i class="fas fa-file-invoice me-1"></i> View Invoice
        </a>
    </div>

    @if ($payment->status === 'pending_verification')
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="payment-card">
                    <div class="section-title">
                        <i class="fas fa-check-circle text-success"></i>
                        Confirm Payment
                    </div>
                    <form method="POST" action="{{ route('hr.payments.confirm', $payment) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Verification Notes</label>
                            <textarea name="verification_notes" class="form-control" rows="3" placeholder="Optional confirmation notes..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success">
                            Confirm Payment
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="payment-danger-card">
                    <div class="section-title">
                        <i class="fas fa-triangle-exclamation text-danger"></i>
                        Reject Payment
                    </div>

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
        </div>
    @endif
</div>
@endsection
