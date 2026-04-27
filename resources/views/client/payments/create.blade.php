@extends('client.layouts.app')

@section('title', 'Submit Payment')

@section('content')
<div class="page-header-card mb-4">
    <h2 class="mb-1">Submit Payment</h2>
    <p class="text-muted mb-0">Submit your payment details and upload proof for verification.</p>
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
                <div class="small text-muted">Service Type</div>
                <div class="fw-semibold">{{ $invoice->quotation->request->service_type }}</div>
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

<form method="POST" action="{{ route('client.payments.store') }}" enctype="multipart/form-data">
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
                            <select name="payment_schedule_id" class="form-select" required>
                                <option value="">Select schedule</option>
                                @foreach ($invoice->paymentSchedules as $schedule)
                                    <option value="{{ $schedule->id }}" @selected(old('payment_schedule_id') == $schedule->id)>
                                        {{ $schedule->label }} — PHP {{ number_format((float) $schedule->amount_due, 2) }}
                                        (Remaining: PHP {{ number_format((float) ($schedule->amount_due - $schedule->amount_paid), 2) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="">Select method</option>
                                <option value="GCash" @selected(old('payment_method') === 'GCash')>GCash</option>
                                <option value="Bank Transfer" @selected(old('payment_method') === 'Bank Transfer')>Bank Transfer</option>
                                <option value="Cash" @selected(old('payment_method') === 'Cash')>Cash</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Reference Number</label>
                            <input
                                type="text"
                                name="reference_number"
                                class="form-control"
                                value="{{ old('reference_number') }}"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Amount Paid</label>
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
                            <label class="form-label">Proof of Payment</label>
                            <input
                                type="file"
                                name="proof"
                                class="form-control"
                                accept=".jpg,.jpeg,.png,.pdf"
                                required
                            >
                            <div class="form-text">Accepted: JPG, JPEG, PNG, PDF. Max: 5MB.</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea
                                name="notes"
                                class="form-control"
                                rows="4"
                                placeholder="Optional message or payment note..."
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
                        <div class="text-muted">No payment schedules available.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="mb-3">Submission Notice</h5>

                    <div class="small text-muted">
                        Your submitted payment will be marked as <strong>Pending Verification</strong>.
                        It will only affect your invoice balance after HR/Admin confirms it.
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Submit Payment</button>
                <a href="{{ route('client.invoices.show', $invoice) }}" class="btn btn-outline-secondary">Back to Invoice</a>
            </div>
        </div>
    </div>
</form>
@endsection