<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>View Quotation - WRPlumb</title>



    <link rel="icon" type="image/png" href="{{ asset('image/logo.png') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/public/quotation-show.css') }}?v=quotation-show-01">
</head>

<body>

<div class="public-quotation-page">

    <div class="quotation-card">

        <div class="quotation-header">

            <div class="quotation-title">WRPlumb Service Quotation</div>

            <p class="quotation-subtitle">

                Review the quotation and select your preferred payment plan before accepting.

            </p>

        </div>



        <div class="quotation-body">

            @if (session('success'))

                <div class="alert alert-success">{{ session('success') }}</div>

            @endif



            @if (session('info'))

                <div class="alert alert-info">{{ session('info') }}</div>

            @endif



            @if ($errors->any())

                <div class="alert alert-danger">

                    <ul class="mb-0">

                        @foreach ($errors->all() as $error)

                            <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

            @endif



            <div class="row g-3 mb-4">

                <div class="col-md-4">

                    <div class="info-box">

                        <div class="info-label">Quotation No.</div>

                        <div class="info-value">{{ $quotation->quotation_no }}</div>

                    </div>

                </div>

                <div class="col-md-4">

                    <div class="info-box">

                        <div class="info-label">Client</div>

                        <div class="info-value">

                            {{ $quotation->request->full_name ?? $quotation->request->email ?? '—' }}

                        </div>

                    </div>

                </div>

                <div class="col-md-4">

                    <div class="info-box">

                        <div class="info-label">Service Type</div>

                        <div class="info-value">{{ $quotation->request->service_type ?? '—' }}</div>

                    </div>

                </div>

            </div>




            @php
                $isCustomQuotation = ($quotation->quotation_format ?? 'standard') === 'custom';
            @endphp

            @if ($isCustomQuotation)
                <div class="custom-public-document mb-4">
                    <div>
                        <span class="custom-public-eyebrow">Custom Quotation Document</span>
                        <h5>{{ $quotation->subject ?? 'Quotation' }}</h5>
                        <p>
                            {{ $quotation->project_name ?? ($quotation->request->service_type ?? 'Service') }}
                            @if ($quotation->project_location)
                                <br>{{ $quotation->project_location }}
                            @endif
                        </p>
                    </div>

                    @if ($quotation->custom_document_path)
                        <a href="{{ $quotation->custom_document_url }}" target="_blank" rel="noopener" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-up-right-from-square me-1"></i>Open Original Quotation
                        </a>
                    @endif
                </div>
            @else
            <div class="table-responsive mb-4">

                <table class="table align-middle">

                    <thead>

                        <tr>

                            <th>Description</th>

                            <th class="text-center">Qty</th>

                            <th class="text-center">Unit</th>

                            <th class="text-end">Unit Price</th>

                            <th class="text-end">Total</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($quotation->items as $item)

                            <tr>

                                <td>{{ $item->description }}</td>

                                <td class="text-center">{{ number_format((float) $item->quantity, 2) }}</td>

                                <td class="text-center">{{ $item->unit ?? 'pcs' }}</td>

                                <td class="text-end">PHP {{ number_format((float) $item->unit_price, 2) }}</td>

                                <td class="text-end">PHP {{ number_format((float) $item->total_price, 2) }}</td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="5" class="text-center text-muted py-4">

                                    No quotation items found.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>



            @endif

            <div class="total-box mb-4">

                <div class="total-label">Grand Total</div>

                <div class="total-value">

                    PHP {{ number_format((float) $quotation->grand_total, 2) }}

                </div>

            </div>



            @if (!$quotation->client_response)

                <h5 class="mb-2">Choose Payment Plan</h5>

                <p class="text-muted">

                    Your selected plan becomes final once you accept the quotation.

                </p>



                <form id="acceptQuotationForm" method="POST" action="{{ route('public.quotations.accept', $quotation->acceptance_token) }}">

                    @csrf



                    <div class="row g-3 mb-4">

                        @foreach ([

                            'full' => ['Full Payment', 'Pay the full amount in one transaction.'],

                            '5050' => ['50 / 50', '50% downpayment and 50% final payment.'],

                            '30303010' => ['30 / 30 / 30 / 10', '30% downpayment, two progress payments, and 10% retention.'],

                        ] as $value => [$title, $description])

                            <div class="col-lg-4">

                                <label class="payment-plan-card d-block">

                                    <div class="d-flex gap-2 align-items-start">

                                        <input

                                            type="radio"

                                            name="payment_plan"

                                            value="{{ $value }}"

                                            class="form-check-input mt-1 payment-plan-radio"

                                            {{ old('payment_plan', $quotation->payment_plan) === $value ? 'checked' : '' }}

                                            required

                                        >

                                        <div>

                                            <div class="fw-bold">{{ $title }}</div>

                                            <div class="small text-muted">{{ $description }}</div>

                                        </div>

                                    </div>

                                </label>

                            </div>

                        @endforeach

                    </div>



                    <div id="paymentPlanPreview"></div>

                </form>

            @else

                <div class="alert alert-info mb-3">

                    This quotation has already been

                    <strong>{{ strtoupper($quotation->client_response) }}</strong>.

                </div>



                @if (!empty($quotation->payment_terms_json['phases']))

                    <h5 class="mb-3">Selected Payment Terms</h5>



                    @foreach ($quotation->payment_terms_json['phases'] as $phase)

                        <div class="d-flex justify-content-between border rounded-3 p-3 mb-2">

                            <div>

                                <div class="fw-semibold">{{ $phase['label'] }}</div>

                                <div class="small text-muted">{{ $phase['percent'] }}%</div>

                            </div>

                            <div class="fw-semibold">

                                PHP {{ number_format((float) $phase['amount'], 2) }}

                            </div>

                        </div>

                    @endforeach

                @endif

            @endif

        </div>



        @if (!$quotation->client_response)

            <div class="action-footer">

                <div class="d-flex flex-wrap gap-2 justify-content-end">

                    <form method="POST" action="{{ route('public.quotations.decline', $quotation->acceptance_token) }}">

                        @csrf

                        <button type="submit" class="btn btn-outline-secondary">

                            Decline Quotation

                        </button>

                    </form>



                    <button type="submit" form="acceptQuotationForm" class="btn btn-primary">

                        Accept Quotation

                    </button>

                </div>

            </div>

        @endif

    </div>

</div>



@if (!$quotation->client_response)

<script>

document.addEventListener('DOMContentLoaded', function () {

    const total = {{ (float) $quotation->grand_total }};

    const preview = document.getElementById('paymentPlanPreview');

    const radios = document.querySelectorAll('.payment-plan-radio');



    function money(value) {

        return 'PHP ' + Number(value).toLocaleString('en-PH', {

            minimumFractionDigits: 2,

            maximumFractionDigits: 2

        });

    }



    function phases(plan) {

        if (plan === 'full') {

            return [{ label: 'Full Payment', percent: 100 }];

        }



        if (plan === '30303010') {

            return [

                { label: 'Downpayment', percent: 30 },

                { label: 'Progress 1', percent: 30 },

                { label: 'Progress 2', percent: 30 },

                { label: 'Retention', percent: 10 },

            ];

        }



        return [

            { label: 'Downpayment', percent: 50 },

            { label: 'Final', percent: 50 },

        ];

    }



    function render() {

        const selected = document.querySelector('.payment-plan-radio:checked');



        if (!selected) {

            preview.innerHTML = '';

            return;

        }



        const selectedPhases = phases(selected.value);

        let running = 0;



        preview.innerHTML = `

            <div class="fw-semibold mb-2">Payment Breakdown Preview</div>

            ${selectedPhases.map((phase, index) => {

                const amount = index === selectedPhases.length - 1

                    ? Math.max(0, total - running)

                    : Math.round((total * phase.percent / 100) * 100) / 100;



                if (index !== selectedPhases.length - 1) {

                    running += amount;

                }



                return `

                    <div class="d-flex justify-content-between border rounded-3 p-3 mb-2">

                        <div>

                            <div class="fw-semibold">${phase.label}</div>

                            <div class="small text-muted">${phase.percent}%</div>

                        </div>

                        <div class="fw-semibold">${money(amount)}</div>

                    </div>

                `;

            }).join('')}

        `;

    }



    radios.forEach(radio => radio.addEventListener('change', render));

    render();

});

</script>

@endif

</body>

</html>