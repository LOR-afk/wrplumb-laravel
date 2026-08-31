@extends('hr.layouts.app')

@section('title', 'Invoice Details')

@section('content')
<div class="page-header-card mb-4">
    <h2 class="mb-1">Invoice Details</h2>
    <p class="text-muted mb-0">Review the generated invoice and billing breakdown.</p>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if (session('info'))
    <div class="alert alert-info">{{ session('info') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        {{ $errors->first() }}
    </div>
@endif

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="small text-muted">Invoice No.</div>
                <div class="fw-semibold">{{ $invoice->invoice_no }}</div>
            </div>

            <div class="col-md-3">
                <div class="small text-muted">Status</div>
                <div class="fw-semibold text-uppercase">{{ $invoice->status }}</div>
            </div>

            <div class="col-md-3">
                <div class="small text-muted">Invoice Date</div>
                <div class="fw-semibold">{{ optional($invoice->invoice_date)->format('M d, Y') }}</div>
            </div>

            <div class="col-md-3">
                <div class="small text-muted">Due Date</div>
                <div class="fw-semibold">{{ optional($invoice->due_date)->format('M d, Y') ?? '—' }}</div>
            </div>

            <div class="col-md-6">
                <div class="small text-muted">Client</div>
                <div class="fw-semibold">{{ $invoice->quotation->request->full_name }}</div>
            </div>

            <div class="col-md-6">
                <div class="small text-muted">Service Type</div>
                <div class="fw-semibold">{{ $invoice->quotation->request->service_type }}</div>
            </div>

            @if (!empty($invoice->description))
                <div class="col-12">
                    <div class="small text-muted">Description</div>
                    <div class="fw-semibold">{{ $invoice->description }}</div>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
        <h5 class="mb-3">Invoice Items</h5>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($invoice->items as $item)
                        <tr>
                            <td>{{ $item->description }}</td>
                            <td>{{ number_format((float) $item->quantity, 2) }}</td>
                            <td>PHP {{ number_format((float) $item->unit_price, 2) }}</td>
                            <td>PHP {{ number_format((float) $item->total_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row justify-content-end">
            <div class="col-md-4">
                <div class="d-flex justify-content-between">
                    <span>Total Amount</span>
                    <strong>PHP {{ number_format((float) $invoice->total_amount, 2) }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
        <h5 class="mb-3">Payment Schedules</h5>

        @if ($invoice->paymentSchedules->isNotEmpty())
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Phase</th>
                            <th>Percent</th>
                            <th>Due Date</th>
                            <th>Amount Due</th>
                            <th>Amount Paid</th>
                            <th>Remaining</th>
                            <th>Milestone</th>
                            <th>Payment</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($invoice->paymentSchedules as $schedule)
                            @php
                                $milestoneStatus = $schedule->milestone_status ?? 'not_ready';

                                $milestoneClass = match ($milestoneStatus) {
                                    'ready_for_billing' => 'bg-primary',
                                    'billed' => 'bg-info text-dark',
                                    'paid' => 'bg-success',
                                    default => 'bg-secondary',
                                };

                                $paymentClass = match ($schedule->status) {
                                    'paid' => 'bg-success',
                                    'partial' => 'bg-warning text-dark',
                                    'overdue' => 'bg-danger',
                                    default => 'bg-secondary',
                                };

                                $previousSchedule = $invoice->paymentSchedules
                                    ->where('sort_order', '<', $schedule->sort_order)
                                    ->sortByDesc('sort_order')
                                    ->first();

                                $canMarkReady = !$previousSchedule
                                    || $previousSchedule->status === 'paid';
                            @endphp

                            <tr>
                                <td>
                                    <strong>{{ $schedule->label }}</strong>

                                    @if ($schedule->milestone_notes)
                                        <div class="small text-muted mt-1">
                                            {{ $schedule->milestone_notes }}
                                        </div>
                                    @endif
                                </td>

                                <td>{{ number_format((float) $schedule->percent, 2) }}%</td>
                                <td>{{ optional($schedule->due_date)->format('M d, Y') ?? '—' }}</td>
                                <td>PHP {{ number_format((float) $schedule->amount_due, 2) }}</td>
                                <td>PHP {{ number_format((float) $schedule->amount_paid, 2) }}</td>
                                <td>PHP {{ number_format($schedule->remaining_amount, 2) }}</td>

                                <td>
                                    <span class="badge {{ $milestoneClass }}">
                                        {{ strtoupper(str_replace('_', ' ', $milestoneStatus)) }}
                                    </span>

                                    @if ($schedule->ready_for_billing_at)
                                        <div class="small text-muted mt-1">
                                            {{ $schedule->ready_for_billing_at->format('M d, Y') }}
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge {{ $paymentClass }}">
                                        {{ strtoupper(str_replace('_', ' ', $schedule->status)) }}
                                    </span>
                                </td>

                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @if ($schedule->is_ready_for_billing)
                                            <a
                                                href="{{ route('hr.invoices.billing-statement', [
                                                    'invoice' => $invoice,
                                                    'schedule' => $schedule->id,
                                                ]) }}"
                                                class="btn btn-sm btn-outline-dark"
                                            >
                                                View Billing
                                            </a>
                                        @elseif ($canMarkReady)
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#markReadyModal{{ $schedule->id }}"
                                            >
                                                Mark Ready
                                            </button>
                                        @else
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-secondary"
                                                disabled
                                            >
                                                Waiting for {{ $previousSchedule->label }}
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @foreach ($invoice->paymentSchedules as $schedule)
                @php
                    $previousSchedule = $invoice->paymentSchedules
                        ->where('sort_order', '<', $schedule->sort_order)
                        ->sortByDesc('sort_order')
                        ->first();

                    $canMarkReady = !$previousSchedule
                        || $previousSchedule->status === 'paid';
                @endphp

                @if (!$schedule->is_ready_for_billing && $canMarkReady)
                    <div
                        class="modal fade"
                        id="markReadyModal{{ $schedule->id }}"
                        tabindex="-1"
                        aria-labelledby="markReadyModalLabel{{ $schedule->id }}"
                        aria-hidden="true"
                    >
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 rounded-4">
                                <form
                                    method="POST"
                                    action="{{ route('hr.invoices.payment-schedules.mark-ready', [
                                        'invoice' => $invoice,
                                        'paymentSchedule' => $schedule,
                                    ]) }}"
                                >
                                    @csrf

                                    <div class="modal-header">
                                        <h5 class="modal-title" id="markReadyModalLabel{{ $schedule->id }}">
                                            Mark {{ $schedule->label }} Ready
                                        </h5>

                                        <button
                                            type="button"
                                            class="btn-close"
                                            data-bs-dismiss="modal"
                                            aria-label="Close"
                                        ></button>
                                    </div>

                                    <div class="modal-body">
                                        <p class="text-muted">
                                            Confirm that the milestone for
                                            <strong>{{ $schedule->label }}</strong>
                                            has been completed and may now be billed.
                                        </p>

                                        <div class="mb-3">
                                            <label class="form-label">
                                                Milestone Notes
                                                <span class="text-muted">(Optional)</span>
                                            </label>

                                            <textarea
                                                name="milestone_notes"
                                                class="form-control"
                                                rows="3"
                                                placeholder="Example: First phase plumbing installation completed."
                                            ></textarea>
                                        </div>

                                        <div class="alert alert-warning mb-0">
                                            Once marked ready, HR may generate the billing statement for this phase.
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button
                                            type="button"
                                            class="btn btn-outline-secondary"
                                            data-bs-dismiss="modal"
                                        >
                                            Cancel
                                        </button>

                                        <button type="submit" class="btn btn-primary">
                                            Confirm Ready for Billing
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        @else
            <div class="text-muted">No payment schedules generated yet.</div>
        @endif
    </div>
</div>

<div class="d-flex flex-wrap gap-2">
    <a href="{{ route('hr.invoices.index') }}" class="btn btn-outline-secondary">
        Back to Invoices
    </a>

    @php
        $readySchedule = $invoice->paymentSchedules
            ->first(fn ($schedule) => $schedule->is_ready_for_billing && $schedule->status !== 'paid');
    @endphp

    @if ($readySchedule)
        <a
            href="{{ route('hr.invoices.billing-statement', [
                'invoice' => $invoice,
                'schedule' => $readySchedule->id,
            ]) }}"
            class="btn btn-dark"
        >
            View Billing Statement
        </a>
    @endif

    @if ($invoice->status !== 'paid')
        <a href="{{ route('hr.payments.create', $invoice) }}" class="btn btn-outline-primary">
            Record Payment
        </a>
    @endif
</div>
@endsection
