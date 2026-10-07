@extends('hr.layouts.app')



@section('title', 'Quotation Details - WRPlumb')

@section('topbar_title', 'HR Panel')

@section('topbar_subtitle', 'Handle routed support concerns and client communication.')



@push('styles')

    <link rel="stylesheet" href="{{ asset('css/hr/quotation-show.css') }}?v=quotation-show-01">

@endpush



@section('content')

@php

    $status = strtolower((string) $quotation->status);

    $isAccepted = $quotation->status === 'accepted' || $quotation->client_response === 'accepted';



    $statusClass = match ($status) {

        'sent' => 'status-sent',

        'draft' => 'status-draft',

        'accepted', 'approved' => 'status-accepted',

        'declined', 'rejected', 'cancelled' => 'status-rejected',

        default => 'status-default',

    };



    $clientName = $quotation->request->full_name

        ?? trim(($quotation->request->first_name ?? '') . ' ' . ($quotation->request->last_name ?? ''))

        ?: 'Client';



    $preparedBy = $quotation->preparedBy->name

        ?? trim(($quotation->preparedBy->first_name ?? '') . ' ' . ($quotation->preparedBy->last_name ?? ''))

        ?: '—';



    $materialsTotal = (float) $quotation->items->where('item_category', 'material')->sum(fn ($item) => ((float) $item->quantity) * ((float) $item->unit_price));

    $laborTotal = (float) $quotation->items->where('item_category', 'labor')->sum(fn ($item) => ((float) $item->quantity) * ((float) $item->unit_price));

    $miscTotal = (float) $quotation->items->where('item_category', 'misc')->sum(fn ($item) => ((float) $item->quantity) * ((float) $item->unit_price));



    $materialsCost = (float) ($quotation->materials_cost ?: $materialsTotal);

    $laborCost = (float) ($quotation->labor_cost ?: $laborTotal);

    $miscellaneousCost = (float) ($quotation->miscellaneous_cost ?: $miscTotal);

    $subtotalAmount = (float) ($quotation->subtotal_amount ?: ($materialsCost + $laborCost + $miscellaneousCost));

    $taxAmount = (float) $quotation->tax_amount;

    $grandTotal = (float) ($quotation->grand_total ?: ($subtotalAmount + $taxAmount));

@endphp



<div class="quotation-show-page">



    @if (session('info'))
        <div class="quotation-inline-notice" role="status">
            <span class="quotation-inline-notice-icon">
                <i class="fas fa-circle-info"></i>
            </span>

            <div class="quotation-inline-notice-copy">
                <strong>Existing quotation</strong>
                <span>{{ session('info') }}</span>
            </div>

            <button
                type="button"
                class="quotation-inline-notice-close"
                aria-label="Dismiss"
                onclick="this.closest('.quotation-inline-notice').remove()"
            >
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif



    @if ($errors->any())

        <div class="alert alert-danger mb-3">

            <ul class="mb-0">

                @foreach ($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif



    <div class="quotation-top-actions">

        <a href="{{ route('hr.quotations.index') }}" class="quotation-back-link">

            <i class="fas fa-arrow-left"></i>

            Back to Quotations

        </a>



        <div class="quotation-action-buttons">

            @if (in_array($quotation->status, ['draft', 'sent']))

                <form method="POST" action="{{ route('hr.quotations.send', $quotation) }}" class="d-inline">

                    @csrf

                    <button type="submit" class="btn btn-primary quotation-main-btn">

                        <i class="fas fa-envelope me-2"></i>Send Quotation Email

                    </button>

                </form>

            @endif



            <div class="dropdown">

                <button class="btn quotation-menu-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">

                    <i class="fas fa-ellipsis"></i>

                </button>

                <ul class="dropdown-menu dropdown-menu-end">

                    <li>

                        <a class="dropdown-item" href="{{ route('hr.quotations.index') }}">

                            <i class="fas fa-list me-2"></i>View All Quotations

                        </a>

                    </li>



                    @if ($quotation->contract)

                        <li>

                            <a class="dropdown-item" href="{{ route('hr.contracts.show', $quotation->contract) }}">

                                <i class="fas fa-file-contract me-2"></i>View Contract

                            </a>

                        </li>

                    @elseif ($isAccepted)

                        <li>

                            <a class="dropdown-item" href="{{ route('hr.contracts.create', $quotation) }}">

                                <i class="fas fa-file-contract me-2"></i>Generate Contract

                            </a>

                        </li>

                    @endif



                    @if ($quotation->invoice)

                        <li>

                            <a class="dropdown-item" href="{{ route('hr.invoices.show', $quotation->invoice) }}">

                                <i class="fas fa-file-invoice me-2"></i>View Invoice

                            </a>

                        </li>

                    @elseif ($quotation->contract)

                        <li>

                            <a class="dropdown-item" href="{{ route('hr.invoices.create', $quotation) }}">

                                <i class="fas fa-file-invoice me-2"></i>Create Invoice

                            </a>

                        </li>

                    @endif

                </ul>

            </div>

        </div>

    </div>



    <section class="quotation-summary-strip">

        <div class="quotation-summary-cell quotation-cell-wide">

            <span>Quotation No.</span>

            <strong>{{ $quotation->quotation_no }}</strong>

            <em class="quotation-status {{ $statusClass }}">{{ strtoupper(str_replace('_', ' ', $quotation->status)) }}</em>

        </div>



        <div class="quotation-summary-cell">

            <span><i class="fas fa-user me-1"></i>Client</span>

            <strong>{{ $clientName }}</strong>

        </div>



        <div class="quotation-summary-cell quotation-cell-service">

            <span><i class="fas fa-tools me-1"></i>Service Type</span>

            <strong>{{ $quotation->request->service_type ?? '—' }}</strong>

        </div>



        <div class="quotation-summary-cell">

            <span><i class="fas fa-calendar-days me-1"></i>Prepared On</span>

            <strong>{{ optional($quotation->created_at)->format('M d, Y h:i A') }}</strong>

        </div>



        <div class="quotation-summary-cell total">

            <span>Grand Total</span>

            <strong>PHP {{ number_format($grandTotal, 2) }}</strong>

        </div>

    </section>



    <section class="quotation-tabs-card">

        <div class="quotation-tabs">

            <button type="button" class="quotation-tab active">

                <i class="fas fa-chart-line"></i>Overview

            </button>

            <button type="button" class="quotation-tab" data-scroll-target="#quotationCostBreakdown">

                <i class="fas fa-coins"></i>Cost Breakdown

            </button>

            <button type="button" class="quotation-tab" data-scroll-target="#quotationProcessingStatus">

                <i class="fas fa-clock"></i>Timeline

            </button>

        </div>

    </section>



    <section class="quotation-overview-grid">

        <article class="quotation-card client-card">

            <div class="quotation-card-title">

                <span class="card-title-icon blue"><i class="fas fa-user"></i></span>

                <h5>Client and Request Details</h5>

            </div>



            <div class="quotation-detail-list">

                <div>

                    <span>Client</span>

                    <strong>{{ $clientName }}</strong>

                </div>

                <div>

                    <span>Prepared By</span>

                    <strong>{{ $preparedBy }}</strong>

                </div>

                <div>

                    <span>Service Type</span>

                    <strong>{{ $quotation->request->service_type ?? '—' }}</strong>

                </div>

                <div>

                    <span>Address</span>

                    <strong>{{ $quotation->request->address ?? '—' }}</strong>

                </div>

                <div class="wide">

                    <span>Problem Details</span>

                    <strong>{{ $quotation->request->details ?? 'No problem details provided.' }}</strong>

                </div>

            </div>

        </article>



        <article class="quotation-card process-card" id="quotationProcessingStatus">

            <div class="quotation-card-title">

                <span class="card-title-icon violet"><i class="fas fa-clipboard-check"></i></span>

                <h5>Processing Status</h5>

            </div>



            <div class="quotation-process-list">

                <div class="quotation-process-item done">

                    <span><i class="fas fa-check"></i></span>

                    <strong>Quotation Prepared</strong>

                    <em>{{ optional($quotation->created_at)->format('M d, Y h:i A') }}</em>

                </div>



                <div class="quotation-process-item {{ $isAccepted ? 'done' : 'current' }}">

                    <span><i class="fas {{ $isAccepted ? 'fa-check' : 'fa-clock' }}"></i></span>

                    <strong>Client Acceptance</strong>

                    <em>{{ $isAccepted ? 'Accepted' : 'Awaiting client response' }}</em>

                </div>



                <div class="quotation-process-item {{ $quotation->contract ? 'done' : '' }}">

                    <span><i class="fas {{ $quotation->contract ? 'fa-check' : 'fa-file-contract' }}"></i></span>

                    <strong>Contract</strong>

                    <em>{{ $quotation->contract ? 'Generated' : 'Not generated yet' }}</em>

                </div>



                <div class="quotation-process-item {{ $quotation->invoice ? 'done' : '' }}">

                    <span><i class="fas {{ $quotation->invoice ? 'fa-check' : 'fa-file-invoice' }}"></i></span>

                    <strong>Invoice</strong>

                    <em>{{ $quotation->invoice ? 'Created' : 'Not created yet' }}</em>

                </div>

            </div>

        </article>



        <aside class="quotation-card payment-card">

            <div class="quotation-card-title">

                <span class="card-title-icon green"><i class="fas fa-money-bill-wave"></i></span>

                <h5>Payment Terms</h5>

            </div>



            @if (!empty($quotation->payment_terms_json['phases']))

                <div class="payment-term-list">

                    @foreach ($quotation->payment_terms_json['phases'] as $phase)

                        <div class="payment-term-row">

                            <div>

                                <strong>{{ $phase['label'] }}</strong>

                                <span>{{ $phase['percent'] }}% of total quotation</span>

                            </div>

                            <b>PHP {{ number_format((float) $phase['amount'], 2) }}</b>

                        </div>

                    @endforeach

                </div>

            @else

                <p class="quotation-muted mb-0">No payment terms available.</p>

            @endif



            @unless ($isAccepted)

                <div class="quotation-info-box mt-3">

                    <i class="fas fa-info-circle"></i>

                    <span>Generate Contract appears after client accepts the quotation.</span>

                </div>

            @endunless

        </aside>

    </section>



    <section class="quotation-content-grid">

        <article class="quotation-card items-card">

            <div class="quotation-card-title">

                <span class="card-title-icon violet"><i class="fas fa-list"></i></span>

                <h5>Quotation Items</h5>

                <small>{{ $quotation->items->count() }} item(s)</small>

            </div>



            <div class="table-responsive quotation-items-wrap">

                <table class="table quotation-items-table align-middle">

                    <thead>

                        <tr>

                            <th>Description</th>

                            <th>Category</th>

                            <th class="text-end">Qty</th>

                            <th class="text-end">Unit Price</th>

                            <th class="text-end">Total</th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach ($quotation->items as $item)

                            @php

                                $lineTotal = (float) ($item->total_price ?: (((float) $item->quantity) * ((float) $item->unit_price)));

                            @endphp

                            <tr>

                                <td class="quotation-item-description">{{ $item->description }}</td>

                                <td>{{ ucfirst($item->item_category) }}</td>

                                <td class="text-end">{{ number_format((float) $item->quantity, 2) }}</td>

                                <td class="text-end">PHP {{ number_format((float) $item->unit_price, 2) }}</td>

                                <td class="text-end fw-bold">PHP {{ number_format($lineTotal, 2) }}</td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>



            @unless ($isAccepted)

                <div class="quotation-info-box compact">

                    <i class="fas fa-info-circle"></i>

                    <span>Generate Contract appears after client accepts the quotation.</span>

                </div>

            @endunless

        </article>



        <aside class="quotation-card totals-card" id="quotationCostBreakdown">

            <div class="quotation-card-title">

                <span class="card-title-icon teal"><i class="fas fa-calculator"></i></span>

                <h5>Totals Summary</h5>

            </div>



            <div class="quotation-totals-list">

                <div><span>Materials</span><strong>PHP {{ number_format($materialsCost, 2) }}</strong></div>

                <div><span>Labor</span><strong>PHP {{ number_format($laborCost, 2) }}</strong></div>

                <div><span>Miscellaneous</span><strong>PHP {{ number_format($miscellaneousCost, 2) }}</strong></div>

                <hr>

                <div><span>Subtotal</span><strong>PHP {{ number_format($subtotalAmount, 2) }}</strong></div>

                <div><span>Tax (12%)</span><strong>PHP {{ number_format($taxAmount, 2) }}</strong></div>

                <div class="grand"><span>Grand Total</span><strong>PHP {{ number_format($grandTotal, 2) }}</strong></div>

            </div>

        </aside>

    </section>



    <section class="quotation-bottom-actions">

        <a href="{{ route('hr.quotations.index') }}" class="btn btn-outline-secondary quotation-action-btn">

            <i class="fas fa-arrow-left me-1"></i>Back to Quotations

        </a>



        <div class="quotation-bottom-center">

            @if ($quotation->contract)

                <a href="{{ route('hr.contracts.show', $quotation->contract) }}" class="btn btn-outline-dark quotation-action-btn">

                    <i class="fas fa-file-contract me-1"></i>View Contract

                </a>

            @elseif ($isAccepted)

                <a href="{{ route('hr.contracts.create', $quotation) }}" class="btn btn-outline-dark quotation-action-btn">

                    <i class="fas fa-file-contract me-1"></i>Generate Contract

                </a>

            @else

                <span class="workflow-note">

                    <i class="fas fa-clock me-2"></i>Generate Contract appears after client accepts the quotation.

                </span>

            @endif



            @if ($quotation->invoice)

                <a href="{{ route('hr.invoices.show', $quotation->invoice) }}" class="btn btn-outline-info quotation-action-btn">

                    <i class="fas fa-file-invoice me-1"></i>View Invoice

                </a>

            @elseif ($quotation->contract)

                <a href="{{ route('hr.invoices.create', $quotation) }}" class="btn btn-outline-primary quotation-action-btn">

                    <i class="fas fa-file-invoice me-1"></i>Create Invoice

                </a>

            @endif

        </div>



        @if (in_array($quotation->status, ['draft', 'sent']))

            <form method="POST" action="{{ route('hr.quotations.send', $quotation) }}">

                @csrf

                <button type="submit" class="btn btn-primary quotation-main-btn">

                    <i class="fas fa-envelope me-2"></i>Send Quotation Email

                </button>

            </form>

        @endif

    </section>

</div>

@endsection



@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('[data-scroll-target]').forEach(function (button) {

        button.addEventListener('click', function () {

            const target = document.querySelector(button.dataset.scrollTarget);

            if (target) {

                target.scrollIntoView({ behavior: 'smooth', block: 'start' });

            }

        });

    });

});

</script>

@endpush
