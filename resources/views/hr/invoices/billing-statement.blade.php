@extends('hr.layouts.app')

@section('title', 'Billing Statement')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/hr/billing-statement.css') }}">
@endpush

@section('content')
@php
    $requestData = $invoice->quotation->request;

    $clientName = $requestData->full_name
        ?? trim(
            ($requestData->first_name ?? '') . ' ' .
            ($requestData->last_name ?? '')
        );

    $projectName = $requestData->service_type ?? 'Plumbing Project';
    $projectLocation = $requestData->address ?? 'Cagayan de Oro City';

    $billingNumber = 'BILL-' .
        str_replace('INV-', '', $invoice->invoice_no) .
        '-' .
        str_pad((string) $selectedSchedule->sort_order, 2, '0', STR_PAD_LEFT);
@endphp

<div class="billing-toolbar no-print">
    <div>
        <h1>Progress Billing Statement</h1>
        <p>{{ $invoice->invoice_no }} · {{ $selectedSchedule->label }}</p>
    </div>

    <div class="billing-toolbar-actions">
        <a
            href="{{ route('hr.invoices.show', $invoice) }}"
            class="btn btn-outline-secondary"
        >
            Back to Invoice
        </a>

        <select
            class="form-select billing-phase-select"
            onchange="window.location.href = this.value"
        >
            @foreach ($invoice->paymentSchedules as $schedule)
                @if ($schedule->is_ready_for_billing)
                    <option
                        value="{{ route('hr.invoices.billing-statement', [
                            'invoice' => $invoice,
                            'schedule' => $schedule->id,
                        ]) }}"
                        @selected($schedule->id === $selectedSchedule->id)
                    >
                        {{ $schedule->label }}
                    </option>
                @endif
            @endforeach
        </select>

        <button
            type="button"
            class="btn btn-dark"
            onclick="window.print()"
        >
            Print Billing
        </button>
    </div>
</div>

<div class="billing-preview">
    <article class="billing-document">
        <header class="billing-letterhead">
            <div class="billing-logo">
                <img
                    src="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}"
                    alt="WR Plumbing"
                >
            </div>

            <div class="billing-company-info">
                <h1>WR PLUMBING AND CONSTRUCTION SERVICES</h1>

                <p>
                    139 Upper Zone 4, Bulua, Cagayan de Oro City<br>
                    Misamis Oriental, Philippines, 9000<br>
                    (088) 850-5197 / 09173980672 / 09177717693<br>
                    wrplumbingcon@gmail.com
                </p>
            </div>
        </header>

        <div class="billing-date">
            {{ optional($invoice->invoice_date)->format('F d, Y') }}
        </div>

        <section class="billing-recipient">
            <p>
                Thru: <strong>{{ $clientName }}</strong><br>
                Project Owner
            </p>

            <table class="billing-project-meta">
                <tr>
                    <th>Project</th>
                    <td>: {{ $projectName }}</td>
                </tr>
                <tr>
                    <th>Owner</th>
                    <td>: {{ $clientName }}</td>
                </tr>
                <tr>
                    <th>Location</th>
                    <td>: {{ $projectLocation }}</td>
                </tr>
            </table>
        </section>

        <section class="billing-subject">
            <p>
                Subject:
                <strong>{{ strtoupper($selectedSchedule->label) }} BILLING</strong>
            </p>

            <p>Sir/Madam:</p>

            <p>
                In connection with the above-mentioned project, we respectfully
                submit our <strong>{{ strtolower($selectedSchedule->label) }}</strong>
                billing under Billing No. <strong>{{ $billingNumber }}</strong>,
                amounting to
                <strong>PHP {{ number_format($currentBillingAmount, 2) }}</strong>.
            </p>
        </section>

        <table class="company-billing-table">
            <thead>
                <tr>
                    <th>Scope of Works</th>
                    <th>Contract Weight</th>
                    <th>Contract Amount</th>
                    <th>Current Billing</th>
                    <th>Billing Amount</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($invoice->items as $item)
                    @php
                        $itemTotal = (float) $item->total_price;

                        $itemWeight = (float) $invoice->total_amount > 0
                            ? ($itemTotal / (float) $invoice->total_amount) * 100
                            : 0;

                        $allocatedBilling = (float) $invoice->total_amount > 0
                            ? ($itemTotal / (float) $invoice->total_amount)
                                * $currentBillingAmount
                            : 0;
                    @endphp

                    <tr>
                        <td>{{ $loop->iteration }}. {{ $item->description }}</td>
                        <td>{{ number_format($itemWeight, 2) }}%</td>
                        <td>PHP {{ number_format($itemTotal, 2) }}</td>
                        <td>{{ number_format($currentPhasePercent, 2) }}%</td>
                        <td>PHP {{ number_format($allocatedBilling, 2) }}</td>
                    </tr>
                @endforeach

                <tr class="billing-total-row">
                    <td>TOTAL</td>
                    <td>100%</td>
                    <td>PHP {{ number_format((float) $invoice->total_amount, 2) }}</td>
                    <td>{{ number_format($currentPhasePercent, 2) }}%</td>
                    <td>PHP {{ number_format($currentBillingAmount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <section class="billing-summary">
            <table>
                <tr>
                    <th>Phase Billing Amount</th>
                    <td>PHP {{ number_format($currentBillingAmount, 2) }}</td>
                </tr>

                <tr>
                    <th>Amount Paid for This Phase</th>
                    <td>PHP {{ number_format($currentPaidAmount, 2) }}</td>
                </tr>

                <tr>
                    <th>Remaining Balance for This Phase</th>
                    <td>PHP {{ number_format($currentRemainingAmount, 2) }}</td>
                </tr>

                <tr>
                    <th>Previous Confirmed Payments</th>
                    <td>PHP {{ number_format($previousPaidAmount, 2) }}</td>
                </tr>

                @if (
                    str_contains(
                        strtolower($selectedSchedule->label),
                        'retention'
                    )
                )
                    <tr>
                        <th>Retention Release</th>
                        <td>PHP {{ number_format($retentionAmount, 2) }}</td>
                    </tr>
                @endif

                <tr>
                    <th>Cumulative Billing Percentage</th>
                    <td>{{ number_format($cumulativePercent, 2) }}%</td>
                </tr>

                <tr class="billing-final-total">
                    <th>TOTAL AMOUNT OF THIS BILLING</th>
                    <td>PHP {{ number_format($currentBillingAmount, 2) }}</td>
                </tr>
            </table>
        </section>

        <section class="billing-signatures">
            <div class="billing-signature">
                <span>Submitted by:</span>
                <div class="signature-space"></div>
                <strong>ENGR. WILROSE RUELO DAP-OG</strong>
                <small>Proprietor / Manager</small>
            </div>

            <div class="billing-signature">
                <span>Prepared by:</span>
                <div class="signature-space"></div>
                <strong>
                    {{ strtoupper($invoice->creator?->name ?? 'HR OFFICER') }}
                </strong>
                <small>Authorized Representative</small>
            </div>

            <div class="billing-signature">
                <span>Conforme:</span>
                <div class="signature-space"></div>
                <strong>{{ strtoupper($clientName) }}</strong>
                <small>Project Owner / Representative</small>
            </div>
        </section>

        <footer class="billing-document-footer">
            <span>{{ $billingNumber }}</span>
            <span>Generated {{ now()->format('F d, Y h:i A') }}</span>
        </footer>
    </article>
</div>
@endsection
