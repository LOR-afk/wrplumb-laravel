@extends('hr.layouts.app')

@section('title', 'Generate Receipt')

@section('content')
<div class="page-header-card mb-4">
    <h2 class="mb-1">Generate Receipt</h2>
    <p class="text-muted mb-0">Create a receipt for a confirmed payment.</p>
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

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
        <h5 class="mb-3">Payment Summary</h5>

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
                <div class="small text-muted">Client</div>
                <div class="fw-semibold">{{ $payment->invoice->quotation->request->full_name ?? '—' }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Amount</div>
                <div class="fw-semibold">PHP {{ number_format((float) $payment->amount, 2) }}</div>
            </div>

            <div class="col-md-4">
                <div class="small text-muted">Payment Schedule</div>
                <div class="fw-semibold">{{ $payment->paymentSchedule->label ?? '—' }}</div>
            </div>
            <div class="col-md-4">
                <div class="small text-muted">Payment Method</div>
                <div class="fw-semibold">{{ $payment->payment_method ?? '—' }}</div>
            </div>
            <div class="col-md-4">
                <div class="small text-muted">Reference Number</div>
                <div class="fw-semibold">{{ $payment->reference_number ?? '—' }}</div>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('hr.receipts.store') }}">
    @csrf
    <input type="hidden" name="payment_id" value="{{ $payment->id }}">

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="mb-3">Receipt Details</h5>

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
                                rows="4"
                                placeholder="Optional receipt notes..."
                            >{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="mb-3">Receipt Summary</h5>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Payment Status</span>
                        <strong class="text-uppercase">{{ $payment->status }}</strong>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span>Amount Received</span>
                        <strong>PHP {{ number_format((float) $payment->amount, 2) }}</strong>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Generate Receipt</button>
                <a href="{{ route('hr.payments.show', $payment) }}" class="btn btn-outline-secondary">Back to Payment</a>
            </div>
        </div>
    </div>
</form>
@endsection