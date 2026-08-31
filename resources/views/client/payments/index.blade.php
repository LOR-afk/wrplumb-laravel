@extends('client.layouts.app')

@section('title', 'My Payments')
@section('topbar_title', 'My Payments')
@section('topbar_subtitle', 'Review payments grouped by service project.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/client/payments-index.css') }}?v=20260818b">
@endpush

@section('content')
@php
    $visibleInvoices = collect($projectInvoices->items());

    $visiblePayments = $visibleInvoices
        ->flatMap(fn ($invoice) => $invoice->payments);

    $visiblePaid = $visiblePayments
        ->filter(fn ($payment) => in_array(strtolower((string) $payment->status), ['confirmed', 'paid', 'approved']))
        ->sum(fn ($payment) => (float) $payment->amount);

    $visiblePaymentCount = $visiblePayments->count();
@endphp

<div class="client-project-payments-page">
    <section class="project-payment-stats">
        <article>
            <span class="project-payment-stat-icon blue">
                <i class="fas fa-diagram-project"></i>
            </span>
            <div>
                <small>Projects With Payments</small>
                <strong>{{ $projectInvoices->total() }}</strong>
            </div>
        </article>

        <article>
            <span class="project-payment-stat-icon violet">
                <i class="fas fa-credit-card"></i>
            </span>
            <div>
                <small>Visible Transactions</small>
                <strong>{{ $visiblePaymentCount }}</strong>
            </div>
        </article>

        <article>
            <span class="project-payment-stat-icon green">
                <i class="fas fa-peso-sign"></i>
            </span>
            <div>
                <small>Visible Confirmed Amount</small>
                <strong class="money">PHP {{ number_format($visiblePaid, 2) }}</strong>
            </div>
        </article>
    </section>

    <section class="project-payment-list">
        <div class="project-payment-list-head">
            <div>
                <span>Payment History</span>
                <h3>Payments by Project</h3>
            </div>

            <small>
                {{ $projectInvoices->total() }}
                {{ $projectInvoices->total() === 1 ? 'project' : 'projects' }}
            </small>
        </div>

        @forelse ($projectInvoices as $invoice)
            @php
                $request = $invoice->quotation?->request;

                $serviceName = $request?->service_type ?? 'Service Project';
                $projectType = $request?->project_type ?? null;
                $address = $request?->address ?? null;

                $invoiceStatusKey = strtolower((string) ($invoice->status ?? 'pending'));
                $invoiceStatusLabel = match ($invoiceStatusKey) {
                    'partially_paid' => 'Partially Paid',
                    'cancelled', 'canceled' => 'Cancelled',
                    default => ucfirst(str_replace('_', ' ', $invoiceStatusKey)),
                };

                $invoiceStatusClass = match ($invoiceStatusKey) {
                    'paid' => 'green',
                    'partially_paid', 'partial' => 'orange',
                    'overdue' => 'red',
                    'cancelled', 'canceled', 'void' => 'gray',
                    default => 'blue',
                };

                $confirmedPaid = $invoice->payments
                    ->filter(fn ($payment) => in_array(
                        strtolower((string) $payment->status),
                        ['confirmed', 'paid', 'approved']
                    ))
                    ->sum(fn ($payment) => (float) $payment->amount);

                $invoiceTotal = (float) ($invoice->total_amount ?? 0);
                $remaining = max(0, $invoiceTotal - $confirmedPaid);
                $paidPercent = $invoiceTotal > 0
                    ? min(100, round(($confirmedPaid / $invoiceTotal) * 100))
                    : 0;
            @endphp

            <article class="project-payment-card">
                <div class="project-payment-summary">
                    <div class="project-payment-project">
                        <span class="project-payment-icon">
                            <i class="fas fa-screwdriver-wrench"></i>
                        </span>

                        <div>
                            <h4>{{ $serviceName }}</h4>

                            <p>
                                {{ $invoice->invoice_no }}
                                @if ($projectType)
                                    · {{ $projectType }}
                                @endif
                            </p>

                            @if ($address)
                                <small>
                                    <i class="fas fa-location-dot"></i>
                                    {{ $address }}
                                </small>
                            @endif
                        </div>
                    </div>

                    <div class="project-payment-money">
                        <div>
                            <span>Invoice Total</span>
                            <strong>PHP {{ number_format($invoiceTotal, 2) }}</strong>
                        </div>

                        <div>
                            <span>Paid</span>
                            <strong class="paid">PHP {{ number_format($confirmedPaid, 2) }}</strong>
                        </div>

                        <div>
                            <span>Remaining</span>
                            <strong class="{{ $remaining > 0 ? 'remaining' : 'paid' }}">
                                PHP {{ number_format($remaining, 2) }}
                            </strong>
                        </div>
                    </div>

                    <div class="project-payment-state">
                        <span class="project-invoice-status {{ $invoiceStatusClass }}">
                            {{ $invoiceStatusLabel }}
                        </span>

                        <strong>{{ $paidPercent }}% Paid</strong>
                    </div>

                    <button
                        type="button"
                        class="project-payment-toggle"
                        aria-expanded="false"
                    >
                        <span>View Payments</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>

                <div class="project-payment-progress">
                    <i style="width: {{ $paidPercent }}%;"></i>
                </div>

                <div class="project-payment-transactions" hidden>
                    <div class="project-payment-transactions-head">
                        <strong>Payment Transactions</strong>
                        <span>{{ $invoice->payments->count() }} record(s)</span>
                    </div>

                    @if ($invoice->payments->count())
                        <div class="project-payment-table-wrap">
                            <table class="project-payment-table">
                                <thead>
                                    <tr>
                                        <th>Payment No.</th>
                                        <th>Schedule</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($invoice->payments as $payment)
                                        @php
                                            $statusKey = strtolower((string) ($payment->status ?? 'pending'));

                                            $statusClass = match ($statusKey) {
                                                'confirmed', 'paid', 'approved' => 'green',
                                                'pending', 'submitted', 'pending_verification' => 'orange',
                                                'rejected', 'failed' => 'red',
                                                'cancelled', 'canceled' => 'gray',
                                                default => 'blue',
                                            };

                                            $statusLabel = match ($statusKey) {
                                                'pending_verification' => 'Pending Verification',
                                                'cancelled', 'canceled' => 'Cancelled',
                                                default => ucfirst(str_replace('_', ' ', $statusKey)),
                                            };
                                        @endphp

                                        <tr>
                                            <td>
                                                <a
                                                    href="{{ route('client.payments.show', $payment) }}"
                                                    class="project-payment-number"
                                                >
                                                    {{ $payment->payment_no }}
                                                </a>
                                            </td>

                                            <td>{{ $payment->paymentSchedule->label ?? '—' }}</td>

                                            <td>
                                                <strong>PHP {{ number_format((float) $payment->amount, 2) }}</strong>
                                            </td>

                                            <td>
                                                <span class="project-payment-status {{ $statusClass }}">
                                                    {{ $statusLabel }}
                                                </span>
                                            </td>

                                            <td>
                                                {{ optional($payment->payment_date)->format('M d, Y') ?? '—' }}
                                            </td>

                                            <td class="text-end">
                                                <button
                                                    type="button"
                                                    class="project-payment-view project-payment-modal-trigger"
                                                    data-payment-modal="paymentModal{{ $payment->id }}"
                                                >
                                                    <i class="fas fa-eye"></i>
                                                    View Payment
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="project-payment-empty">
                            No payment transactions have been recorded for this project yet.
                        </div>
                    @endif

                    <div class="project-payment-footer">
                        <a
                            href="{{ route('client.invoices.show', $invoice) }}"
                            class="project-invoice-link"
                        >
                            <i class="fas fa-file-invoice-dollar"></i>
                            View Invoice
                        </a>
                    </div>
                </div>

                @foreach ($invoice->payments as $payment)
                    @php
                        $modalStatusKey = strtolower((string) ($payment->status ?? 'pending'));

                        $modalStatusClass = match ($modalStatusKey) {
                            'confirmed', 'paid', 'approved' => 'green',
                            'pending', 'submitted', 'pending_verification' => 'orange',
                            'rejected', 'failed' => 'red',
                            'cancelled', 'canceled' => 'gray',
                            default => 'blue',
                        };

                        $modalStatusLabel = match ($modalStatusKey) {
                            'pending_verification' => 'Pending Verification',
                            'cancelled', 'canceled' => 'Cancelled',
                            default => ucfirst(str_replace('_', ' ', $modalStatusKey)),
                        };
                    @endphp

                    <div
                        class="project-payment-modal"
                        id="paymentModal{{ $payment->id }}"
                        aria-hidden="true"
                    >
                        <div class="project-payment-modal-backdrop" data-payment-modal-close></div>

                        <div
                            class="project-payment-modal-dialog"
                            role="dialog"
                            aria-modal="true"
                            aria-labelledby="paymentModalTitle{{ $payment->id }}"
                        >
                            <div class="project-payment-modal-head">
                                <div>
                                    <span>Payment Transaction</span>
                                    <h3 id="paymentModalTitle{{ $payment->id }}">
                                        {{ $payment->payment_no }}
                                    </h3>
                                    <p>{{ $invoice->invoice_no }} · {{ $serviceName }}</p>
                                </div>

                                <button
                                    type="button"
                                    class="project-payment-modal-close"
                                    data-payment-modal-close
                                    aria-label="Close payment details"
                                >
                                    <i class="fas fa-xmark"></i>
                                </button>
                            </div>

                            <div class="project-payment-modal-summary">
                                <article>
                                    <span>Amount</span>
                                    <strong>PHP {{ number_format((float) $payment->amount, 2) }}</strong>
                                </article>

                                <article>
                                    <span>Schedule</span>
                                    <strong>{{ $payment->paymentSchedule->label ?? '—' }}</strong>
                                </article>

                                <article>
                                    <span>Payment Date</span>
                                    <strong>{{ optional($payment->payment_date)->format('M d, Y') ?? '—' }}</strong>
                                </article>

                                <article>
                                    <span>Status</span>
                                    <strong class="project-payment-modal-status {{ $modalStatusClass }}">
                                        {{ $modalStatusLabel }}
                                    </strong>
                                </article>
                            </div>

                            <div class="project-payment-modal-body">
                                <div class="project-payment-modal-grid">
                                    <div>
                                        <span>Invoice No.</span>
                                        <strong>{{ $invoice->invoice_no }}</strong>
                                    </div>

                                    <div>
                                        <span>Payment Method</span>
                                        <strong>{{ $payment->payment_method ?? '—' }}</strong>
                                    </div>

                                    <div>
                                        <span>Reference Number</span>
                                        <strong>{{ $payment->reference_number ?? '—' }}</strong>
                                    </div>

                                    <div>
                                        <span>Invoice Status</span>
                                        <strong>{{ $invoiceStatusLabel }}</strong>
                                    </div>
                                </div>

                                @if (!empty($payment->verification_notes))
                                    <div class="project-payment-modal-note">
                                        <span>Verification Notes</span>
                                        <p>{{ $payment->verification_notes }}</p>
                                    </div>
                                @endif
                            </div>

                            <div class="project-payment-modal-actions">
                                @if (!empty($payment->proof_path))
                                    <a
                                        href="{{ asset('storage/' . $payment->proof_path) }}"
                                        target="_blank"
                                        class="project-payment-modal-btn secondary"
                                    >
                                        <i class="fas fa-file-image"></i>
                                        View Proof
                                    </a>
                                @endif

                                @if ($payment->receipt)
                                    <a
                                        href="{{ route('client.receipts.show', $payment->receipt) }}"
                                        class="project-payment-modal-btn outline"
                                    >
                                        <i class="fas fa-receipt"></i>
                                        View Receipt
                                    </a>
                                @endif

                                <button
                                    type="button"
                                    class="project-payment-modal-btn primary"
                                    data-payment-modal-close
                                >
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </article>
        @empty
            <div class="project-payments-empty">
                <span><i class="fas fa-credit-card"></i></span>
                <strong>No payment history yet</strong>
                <p>Projects with recorded payments will appear here.</p>
            </div>
        @endforelse

        @if ($projectInvoices->hasPages())
            <div class="project-payment-pagination">
                {{ $projectInvoices->links() }}
            </div>
        @endif
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.project-payment-toggle').forEach(function (button) {
        button.addEventListener('click', function () {
            const card = button.closest('.project-payment-card');
            const transactions = card.querySelector('.project-payment-transactions');
            const expanded = button.getAttribute('aria-expanded') === 'true';

            button.setAttribute('aria-expanded', expanded ? 'false' : 'true');
            transactions.hidden = expanded;
            card.classList.toggle('is-open', !expanded);

            button.querySelector('span').textContent = expanded
                ? 'View Payments'
                : 'Hide Payments';
        });
    });

    function openPaymentModal(modal) {
        if (!modal) return;

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('payment-modal-open');
    }

    function closePaymentModal(modal) {
        if (!modal) return;

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');

        if (!document.querySelector('.project-payment-modal.is-open')) {
            document.body.classList.remove('payment-modal-open');
        }
    }

    document.querySelectorAll('.project-payment-modal-trigger').forEach(function (button) {
        button.addEventListener('click', function () {
            const modalId = button.dataset.paymentModal;
            openPaymentModal(document.getElementById(modalId));
        });
    });

    document.querySelectorAll('[data-payment-modal-close]').forEach(function (element) {
        element.addEventListener('click', function () {
            closePaymentModal(element.closest('.project-payment-modal'));
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;

        const modal = document.querySelector('.project-payment-modal.is-open');
        if (modal) {
            closePaymentModal(modal);
        }
    });
});
</script>
@endsection