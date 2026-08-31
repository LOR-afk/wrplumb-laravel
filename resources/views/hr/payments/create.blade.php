@extends('hr.layouts.app')

@section('title', 'Record Payment')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/hr/payment-create.css') }}?v=payment-create-01">
@endpush

@section('content')

<div class="payment-form-page">
    <div class="payment-hero">
        <div>
            <h2 class="payment-title">Record Payment</h2>
            <p class="payment-subtitle">Record a payment for the selected invoice and update the billing schedule.</p>
        </div>

        <a href="{{ route('hr.invoices.show', $invoice) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Invoice
        </a>
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

    <div class="payment-card">
        <div class="section-title">
            <i class="fas fa-file-invoice text-primary"></i>
            Invoice Summary
        </div>

        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Invoice No.</div>
                <div class="summary-value">{{ $invoice->invoice_no }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Client</div>
                <div class="summary-value">{{ $invoice->quotation->request->full_name }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Invoice Status</div>
                <div class="summary-value text-uppercase">{{ $invoice->status }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Amount</div>
                <div class="summary-value">PHP {{ number_format((float) $invoice->total_amount, 2) }}</div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('hr.payments.store') }}">
        @csrf
        <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="payment-card">
                    <div class="section-title">
                        <i class="fas fa-credit-card text-primary"></i>
                        Payment Details
                    </div>

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
                            <div class="input-group">
                                <span class="input-group-text">PHP</span>
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
                                <option value="pending_verification" @selected(old('status') === 'pending_verification')>For Verification</option>
                                <option value="rejected" @selected(old('status') === 'rejected')>Rejected</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea
                                name="notes"
                                class="form-control"
                                rows="4"
                                placeholder="Optional payment notes..."
                            >{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="payment-card mt-4">
                    <div class="section-title">
                        <i class="fas fa-calendar-check text-primary"></i>
                        Current Payment Schedules
                    </div>

                    @if ($invoice->paymentSchedules->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 schedule-table">
                                <thead>
                                    <tr>
                                        <th>Phase</th>
                                        <th>Due Date</th>
                                        <th class="text-end">Amount Due</th>
                                        <th class="text-end">Amount Paid</th>
                                        <th class="text-end">Remaining</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($invoice->paymentSchedules as $schedule)
                                        @php
                                            $remaining = max(0, (float) $schedule->amount_due - (float) $schedule->amount_paid);
                                            $scheduleStatus = in_array($schedule->status, ['paid', 'partial', 'pending', 'unpaid']) ? $schedule->status : 'default';
                                        @endphp
                                        <tr>
                                            <td class="fw-semibold">{{ $schedule->label }}</td>
                                            <td>{{ optional($schedule->due_date)->format('M d, Y') ?? '—' }}</td>
                                            <td class="text-end">PHP {{ number_format((float) $schedule->amount_due, 2) }}</td>
                                            <td class="text-end">PHP {{ number_format((float) $schedule->amount_paid, 2) }}</td>
                                            <td class="text-end fw-bold">PHP {{ number_format($remaining, 2) }}</td>
                                            <td>
                                                <span class="status-chip {{ $scheduleStatus }}">
                                                    {{ str_replace('_', ' ', $schedule->status) }}
                                                </span>
                                            </td>
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

            <div class="col-lg-4">
                <div class="payment-balance-card sticky-balance p-4">
                    <div class="section-title">
                        <i class="fas fa-wallet text-primary"></i>
                        Invoice Balance
                    </div>

                    <div class="balance-line">
                        <span class="balance-label">Total Invoice</span>
                        <strong class="money-value">PHP {{ number_format((float) $invoice->total_amount, 2) }}</strong>
                    </div>

                    <div class="balance-line">
                        <span class="balance-label">Total Paid</span>
                        <strong class="money-value">PHP {{ number_format((float) $invoice->paymentSchedules->sum('amount_paid'), 2) }}</strong>
                    </div>

                    <div class="balance-total">
                        <div class="d-flex justify-content-between gap-2">
                            <span class="fw-bold text-success">Remaining Balance</span>
                            <strong class="money-value text-success">PHP {{ number_format((float) $invoice->remaining_balance, 2) }}</strong>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-floppy-disk me-2"></i>Record Payment
                        </button>
                        <a href="{{ route('hr.invoices.show', $invoice) }}" class="btn btn-outline-secondary">
                            Back to Invoice
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection