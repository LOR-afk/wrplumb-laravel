@extends('client.layouts.app')



@section('title', 'Quotation Details')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/client/quotation-show.css') }}?v=quotation-show-01">
@endpush




@section('content')
<div class="client-quotation-show">

<div class="page-header-card mb-4">

    <h2 class="mb-1">Quotation Details</h2>

    <p class="text-muted mb-0">Review the breakdown and choose your preferred payment plan before accepting.</p>

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



@if (session('success'))

    <div class="alert alert-success">{{ session('success') }}</div>

@endif



@if (session('info'))

    <div class="alert alert-info">{{ session('info') }}</div>

@endif



<div class="card border-0 shadow-sm rounded-4 mb-4">

    <div class="card-body">

        <div class="row g-3">

            <div class="col-md-3">

                <div class="small text-muted">Quotation No.</div>

                <div class="fw-semibold">{{ $quotation->quotation_no }}</div>

            </div>

            <div class="col-md-3">

                <div class="small text-muted">Status</div>

                <div class="fw-semibold text-uppercase">{{ $quotation->status }}</div>

            </div>

            <div class="col-md-3">

                <div class="small text-muted">Service Category</div>

                <div class="fw-semibold">{{ $quotation->request->service_category ?? '—' }}</div>

            </div>

            <div class="col-md-3">

                <div class="small text-muted">Service Type</div>

                <div class="fw-semibold">{{ $quotation->request->service_type ?? '—' }}</div>

            </div>



            <div class="col-md-6">

                <div class="small text-muted">Address</div>

                <div class="fw-semibold">{{ $quotation->request->address ?? '—' }}</div>

            </div>

            <div class="col-md-6">

                <div class="small text-muted">Prepared By</div>

                <div class="fw-semibold">{{ $quotation->preparedBy->name ?? $quotation->preparedBy->first_name ?? '—' }}</div>

            </div>



            <div class="col-12">

                <div class="small text-muted">Problem Details</div>

                <div class="fw-semibold">{{ $quotation->request->details ?? '—' }}</div>

            </div>



            @if (!empty($quotation->notes))

                <div class="col-12">

                    <div class="small text-muted">Notes</div>

                    <div class="fw-semibold">{{ $quotation->notes }}</div>

                </div>

            @endif

        </div>

    </div>

</div>




@php
    $isCustomQuotation = ($quotation->quotation_format ?? 'standard') === 'custom';
@endphp

@if ($isCustomQuotation)
<div class="card border-0 shadow-sm rounded-4 mb-4 custom-quotation-document-card">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
            <div>
                <div class="small text-muted text-uppercase fw-bold mb-1">Custom Quotation</div>
                <h5 class="mb-1">{{ $quotation->subject ?? 'Quotation Document' }}</h5>
                <p class="text-muted mb-0">{{ $quotation->project_name ?? ($quotation->request->service_type ?? 'Service') }}</p>
            </div>

            @if ($quotation->custom_document_path)
                <a href="{{ $quotation->custom_document_url }}" target="_blank" rel="noopener" class="btn btn-primary">
                    <i class="fas fa-file-arrow-down me-1"></i>Open Original Quotation
                </a>
            @endif
        </div>

        <div class="custom-document-summary mt-3">
            <div>
                <span>Project Location</span>
                <strong>{{ $quotation->project_location ?? ($quotation->request->address ?? '—') }}</strong>
            </div>
            <div>
                <span>Quotation Amount</span>
                <strong>PHP {{ number_format((float) $quotation->grand_total, 2) }}</strong>
            </div>
            <div>
                <span>File</span>
                <strong>{{ $quotation->custom_document_name ?? 'Uploaded quotation' }}</strong>
            </div>
        </div>
    </div>
</div>
@else
<div class="card border-0 shadow-sm rounded-4 mb-4">

    <div class="card-body">

        <h5 class="mb-3">Quotation Items</h5>



        <div class="table-responsive">

            <table class="table align-middle">

                <thead>

                    <tr>

                        <th>Description</th>

                        <th>Category</th>

                        <th>Qty</th>

                        <th>Unit</th>

                        <th>Unit Price</th>

                        <th>Total</th>

                    </tr>

                </thead>

                <tbody>

                    @foreach ($quotation->items as $item)

                        <tr>

                            <td>{{ $item->description }}</td>

                            <td class="text-capitalize">{{ $item->item_category }}</td>

                            <td>{{ number_format((float) $item->quantity, 2) }}</td>

                            <td>{{ $item->unit ?? 'pcs' }}</td>

                            <td>PHP {{ number_format((float) $item->unit_price, 2) }}</td>

                            <td>PHP {{ number_format((float) $item->total_price, 2) }}</td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>



        <div class="row justify-content-end">

            <div class="col-md-4">

                <div class="d-flex justify-content-between mb-2">

                    <span>Materials</span>

                    <strong>PHP {{ number_format((float) $quotation->materials_cost, 2) }}</strong>

                </div>

                <div class="d-flex justify-content-between mb-2">

                    <span>Labor</span>

                    <strong>PHP {{ number_format((float) $quotation->labor_cost, 2) }}</strong>

                </div>

                <div class="d-flex justify-content-between mb-2">

                    <span>Miscellaneous</span>

                    <strong>PHP {{ number_format((float) $quotation->miscellaneous_cost, 2) }}</strong>

                </div>

                <div class="d-flex justify-content-between mb-2">

                    <span>Subtotal</span>

                    <strong>PHP {{ number_format((float) $quotation->subtotal_amount, 2) }}</strong>

                </div>

                <div class="d-flex justify-content-between mb-2">

                    <span>VAT ({{ number_format((float) $quotation->tax_rate, 2) }}%)</span>

                    <strong>PHP {{ number_format((float) $quotation->tax_amount, 2) }}</strong>

                </div>

                <div class="d-flex justify-content-between">

                    <span>Grand Total</span>

                    <strong>PHP {{ number_format((float) $quotation->grand_total, 2) }}</strong>

                </div>

            </div>

        </div>

    </div>

</div>



@endif

<div class="card border-0 shadow-sm rounded-4 mb-4">

    <div class="card-body">

        <h5 class="mb-2">Payment Plan</h5>

        <p class="text-muted mb-3">

            Select your preferred payment arrangement. Your selection becomes final once the quotation is accepted.

        </p>



        @if (!$quotation->client_response)

            <form method="POST" action="{{ route('client.quotations.accept', $quotation) }}">

                @csrf



                <div class="row g-3 mb-4">

                    @foreach ([

                        'full' => ['Full Payment', 'Pay the complete amount in one transaction.'],

                        '5050' => ['50 / 50', '50% downpayment and 50% final payment.'],

                        '30303010' => ['30 / 30 / 30 / 10', '30% downpayment, two 30% progress payments, and 10% retention.'],

                    ] as $value => [$title, $description])

                        <div class="col-lg-4">

                            <label class="border rounded-4 p-3 h-100 d-block payment-plan-option">

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



                <div id="paymentPlanPreview" class="mb-4"></div>



                <div class="d-flex flex-wrap gap-2">

                    <button type="submit" class="btn btn-primary">

                        Accept Quotation

                    </button>



                    <a href="{{ route('client.quotations.index') }}" class="btn btn-outline-secondary">

                        Back to My Quotations

                    </a>

                </div>

            </form>

        @else

            <div class="alert alert-info mb-3">

                This quotation has already been

                <strong>{{ strtoupper($quotation->client_response) }}</strong>.

            </div>



            @if (!empty($quotation->payment_terms_json['phases']))

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



            <a href="{{ route('client.quotations.index') }}" class="btn btn-outline-secondary mt-3">

                Back to My Quotations

            </a>

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



    function getPhases(plan) {

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



        const phases = getPhases(selected.value);

        let running = 0;



        const rows = phases.map((phase, index) => {

            const amount = index === phases.length - 1

                ? Math.max(0, total - running)

                : Math.round((total * phase.percent / 100) * 100) / 100;



            if (index !== phases.length - 1) {

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

        }).join('');



        preview.innerHTML = `

            <div class="fw-semibold mb-2">Payment Breakdown Preview</div>

            ${rows}

        `;

    }



    radios.forEach(radio => radio.addEventListener('change', render));

    render();

});

</script>

@endif

</div>

@endsection