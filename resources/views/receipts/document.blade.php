@php
    $payment = $receipt->payment;
    $invoice = $payment?->invoice;
    $quotationRequest = $invoice?->quotation?->request;
    $schedule = $payment?->paymentSchedule;

    $clientName = $quotationRequest?->full_name
        ?? trim(
            ($quotationRequest?->first_name ?? '') . ' ' .
            ($quotationRequest?->last_name ?? '')
        )
        ?: '—';

    $description = ($schedule?->label ?? 'Invoice Payment')
        . ' - '
        . ($invoice?->invoice_no ?? 'Invoice');

    $amount = (float) $receipt->amount_received;

    $issuerName = $receipt->issuer?->name
        ?? trim(
            ($receipt->issuer?->first_name ?? '') . ' ' .
            ($receipt->issuer?->last_name ?? '')
        )
        ?: 'Authorized Representative';
@endphp

<article class="official-receipt">
    <header class="official-receipt-header">
        <div class="receipt-company">
            <img
                src="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}"
                alt="WR Plumbing Logo"
                class="receipt-company-logo"
            >

            <div class="receipt-company-copy">
                <h1>WR Plumbing and Construction Services</h1>

                <p>
                    139 Upper Zone 4, Bulua, Cagayan de Oro City<br>
                    Misamis Oriental, Philippines, 9000<br>
                    Phone: (088) 850-5197<br>
                    Email: wrplumbingcon@gmail.com
                </p>
            </div>
        </div>

        <div class="receipt-document-heading">
            <h2>OFFICIAL RECEIPT</h2>
            <span>Payment Acknowledgement</span>
        </div>
    </header>

    <section class="receipt-reference-bar">
        <div>
            <span>Receipt No.</span>
            <strong>{{ $receipt->receipt_no }}</strong>
        </div>

        <div>
            <span>Receipt Date</span>
            <strong>
                {{ optional($receipt->receipt_date)->format('F d, Y') ?? '—' }}
            </strong>
        </div>

        <div>
            <span>Invoice No.</span>
            <strong>{{ $invoice?->invoice_no ?? '—' }}</strong>
        </div>
    </section>

    <section class="receipt-party-section">
        <div class="receipt-party-box">
            <span class="receipt-section-label">Received From</span>

            <strong>{{ $clientName }}</strong>

            <p>
                {{ $quotationRequest?->address ?? '—' }}<br>
                {{ $quotationRequest?->email ?? '—' }}<br>
                {{ $quotationRequest?->phone ?? '—' }}
            </p>
        </div>

        <div class="receipt-payment-box">
            <span class="receipt-section-label">Payment Reference</span>

            <dl>
                <div>
                    <dt>Payment No.</dt>
                    <dd>{{ $payment?->payment_no ?? '—' }}</dd>
                </div>

                <div>
                    <dt>Billing Phase</dt>
                    <dd>{{ $schedule?->label ?? '—' }}</dd>
                </div>

                <div>
                    <dt>Payment Method</dt>
                    <dd>
                        {{ $receipt->payment_method
                            ?? $payment?->payment_method
                            ?? '—'
                        }}
                    </dd>
                </div>

                <div>
                    <dt>Reference No.</dt>
                    <dd>
                        {{ $receipt->reference_number
                            ?? $payment?->reference_number
                            ?? '—'
                        }}
                    </dd>
                </div>
            </dl>
        </div>
    </section>

    <section class="receipt-amount-statement">
        <span>Amount Received</span>

        <strong>
            PHP {{ number_format($amount, 2) }}
        </strong>
    </section>

    <section class="receipt-description-section">
        <table class="receipt-payment-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Unit Amount</th>
                    <th>Total</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>
                        <strong>{{ $description }}</strong>

                        <small>
                            Payment No. {{ $payment?->payment_no ?? '—' }}

                            @if ($payment?->reference_number)
                                · Reference {{ $payment->reference_number }}
                            @endif
                        </small>
                    </td>

                    <td>1</td>

                    <td>
                        PHP {{ number_format($amount, 2) }}
                    </td>

                    <td>
                        PHP {{ number_format($amount, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </section>

    <section class="receipt-total-section">
        <div class="receipt-total-row">
            <span>Subtotal</span>
            <strong>PHP {{ number_format($amount, 2) }}</strong>
        </div>

        <div class="receipt-total-row">
            <span>Discount</span>
            <strong>PHP 0.00</strong>
        </div>

        <div class="receipt-total-row">
            <span>Tax</span>
            <strong>PHP 0.00</strong>
        </div>

        <div class="receipt-total-row receipt-grand-total">
            <span>Total Paid</span>
            <strong>PHP {{ number_format($amount, 2) }}</strong>
        </div>
    </section>

    @if (!empty($receipt->notes))
        <section class="receipt-notes">
            <span class="receipt-section-label">Notes</span>
            <p>{{ $receipt->notes }}</p>
        </section>
    @endif

    <section class="receipt-acknowledgement">
        <p>
            This official receipt acknowledges that WR Plumbing and Construction
            Services received the amount stated above as payment for the referenced
            invoice and billing phase.
        </p>
    </section>

    <footer class="receipt-signature-section">
        <div class="receipt-issued-details">
            <div>
                <span>Issued At</span>
                <strong>
                    {{ optional($receipt->issued_at)->format('F d, Y h:i A') ?? '—' }}
                </strong>
            </div>

            <div>
                <span>Printed At</span>
                <strong class="receipt-printed-at"></strong>
            </div>
        </div>

        <div class="receipt-signature-box">
            <div class="receipt-signature-space"></div>

            <strong>{{ strtoupper($issuerName) }}</strong>

            <span>Authorized Representative / Issued By</span>
        </div>
    </footer>

    <div class="receipt-footer-reference">
        {{ $receipt->receipt_no }}
    </div>
</article>