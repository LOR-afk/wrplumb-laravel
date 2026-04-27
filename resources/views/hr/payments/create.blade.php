@extends('hr.layouts.app')

@section('title', 'Record Payment')

@section('content')
<div class="page-header-card mb-4">
    <h2 class="mb-1">Record Payment</h2>
    <p class="text-muted mb-0">Record a payment for the selected invoice.</p>
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
        <h5 class="mb-3">Invoice Summary</h5>

        <div class="row g-3">
            <div class="col-md-3">
                <div class="small text-muted">Invoice No.</div>
                <div class="fw-semibold">{{ $invoice->invoice_no }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Client</div>
                <div class="fw-semibold">{{ $invoice->quotation->request->full_name }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Invoice Status</div>
                <div class="fw-semibold text-uppercase">{{ $invoice->status }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Total Amount</div>
                <div class="fw-semibold">PHP {{ number_format((float) $invoice->total_amount, 2) }}</div>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('hr.payments.store') }}">
    @csrf
    <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="mb-3">Payment Details</h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Payment Schedule</label>
                            <select name="payment_schedule_id" class="form-select">
                                <option value="">No specific schedule</option>
                                @foreach ($invoice->paymentSchedules as $schedule)
                                    <option value="{{ $schedule->id }}" @selected(old('payment_schedule_id') == $schedule->id)>
                                        {{ $schedule->label }} — PHP {{ number_format((float) $schedule->amount_due, 2) }} ({{ strtoupper($schedule->status) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Payment Type</label>
                            <input
                                type="text"
                                name="payment_type"
                                class="form-control"
                                value="{{ old('payment_type') }}"
                                placeholder="e.g. Downpayment"
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-select">
                                <option value="">Select method</option>
                                <option value="Cash" @selected(old('payment_method') === 'Cash')>Cash</option>
                                <option value="GCash" @selected(old('payment_method') === 'GCash')>GCash</option>
                                <option value="Bank Transfer" @selected(old('payment_method') === 'Bank Transfer')>Bank Transfer</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Reference Number</label>
                            <input
                                type="text"
                                name="reference_number"
                                class="form-control"
                                value="{{ old('reference_number') }}"
                                placeholder="Optional reference number"
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Amount</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0.01"
                                name="amount"
                                class="form-control"
                                value="{{ old('amount') }}"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Payment Date</label>
                            <input
                                type="date"
                                name="payment_date"
                                class="form-control"
                                value="{{ old('payment_date', now()->format('Y-m-d')) }}"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="confirmed" @selected(old('status', 'confirmed') === 'confirmed')>Confirmed</option>
                                <option value="pending" @selected(old('status') === 'pending')>Pending</option>
                                <option value="rejected" @selected(old('status') === 'rejected')>Rejected</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea
                                name="notes"
                                class="form-control"
                                rows="4"
                                placeholder="Optional notes..."
                            >{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mt-4">
                <div class="card-body">
                    <h5 class="mb-3">Current Payment Schedules</h5>

                    @if ($invoice->paymentSchedules->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Phase</th>
                                        <th>Due Date</th>
                                        <th>Amount Due</th>
                                        <th>Amount Paid</th>
                                        <th>Remaining</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($invoice->paymentSchedules as $schedule)
                                        <tr>
                                            <td>{{ $schedule->label }}</td>
                                            <td>{{ optional($schedule->due_date)->format('M d, Y') ?? '—' }}</td>
                                            <td>PHP {{ number_format((float) $schedule->amount_due, 2) }}</td>
                                            <td>PHP {{ number_format((float) $schedule->amount_paid, 2) }}</td>
                                            <td>PHP {{ number_format((float) ($schedule->amount_due - $schedule->amount_paid), 2) }}</td>
                                            <td class="text-uppercase">{{ $schedule->status }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-muted">No payment schedules found for this invoice.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="mb-3">Invoice Balance</h5>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Invoice</span>
                        <strong>PHP {{ number_format((float) $invoice->total_amount, 2) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Paid</span>
                        <strong>PHP {{ number_format((float) $invoice->paymentSchedules->sum('amount_paid'), 2) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span>Remaining Balance</span>
                        <strong>PHP {{ number_format((float) $invoice->remaining_balance, 2) }}</strong>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Record Payment</button>
                <a href="{{ route('hr.invoices.show', $invoice) }}" class="btn btn-outline-secondary">Back to Invoice</a>
            </div>
        </div>
    </div>
</form>
@endsection