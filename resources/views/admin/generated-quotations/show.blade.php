@extends('admin.layouts.app')

@section('title', 'Quotation Details')
@section('topbar_title', 'Generated Quotation')
@section('topbar_subtitle', 'Review the commercial quotation prepared for this service request.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/generated-quotations.css') }}?v=generated-quotations-01">
@endpush

@section('content')
@php
    $statusKey = strtolower((string) $quotation->status);
    $canEdit = in_array($statusKey, ['draft', 'sent'], true);
    $report = $quotation->request?->inspectionReport;
@endphp

<div class="generated-quotation-page">
    <section class="gq-detail-hero">
        <div>
            <span>QUOTATION RECORD</span>
            <h2>{{ $quotation->quotation_no }}</h2>
            <p>{{ $quotation->request?->full_name ?? 'Client' }} · {{ $quotation->request?->service_type ?? 'Service' }}</p>
        </div>

        <div class="gq-detail-actions">
            <a href="{{ route('admin.generated-quotations.index') }}" class="gq-btn"><i class="fas fa-arrow-left"></i>Back</a>

            @if ($canEdit)
                <a href="{{ route('admin.generated-quotations.edit', $quotation) }}" class="gq-btn primary">
                    <i class="fas fa-pen"></i>Edit Quotation
                </a>
            @endif
        </div>
    </section>

    @if (!$canEdit)
        <div class="gq-lock-note">
            <i class="fas fa-lock"></i>
            <div>
                <strong>Quotation locked for editing</strong>
                <span>Accepted, rejected, or finalized quotation records are preserved to keep contracts and invoices consistent.</span>
            </div>
        </div>
    @elseif ($statusKey === 'sent')
        <div class="gq-warning-note">
            <i class="fas fa-triangle-exclamation"></i>
            <div>
                <strong>Editing a sent quotation will return it to Draft</strong>
                <span>It must be reviewed and sent again before the client accepts the revised amount.</span>
            </div>
        </div>
    @endif

    <section class="gq-detail-summary">
        <article><span>Status</span><strong>{{ ucfirst($statusKey) }}</strong></article>
        <article><span>Prepared By</span><strong>{{ $quotation->preparedBy?->name ?? 'HR' }}</strong></article>
        <article><span>VAT</span><strong>{{ number_format((float) $quotation->tax_rate, 2) }}%</strong></article>
        <article><span>Grand Total</span><strong>PHP {{ number_format((float) $quotation->grand_total, 2) }}</strong></article>
    </section>

    @if ($report)
        <section class="gq-source-card">
            <span class="gq-source-icon"><i class="fas fa-clipboard-check"></i></span>
            <div>
                <small>INSPECTION SOURCE</small>
                <strong>{{ $report->report_no }}</strong>
                <p>Inspector preliminary estimate: PHP {{ number_format((float) $report->estimated_total_cost, 2) }}</p>
            </div>
        </section>
    @endif

    <section class="gq-detail-card">
        <header><h3>Quotation Items</h3></header>

        <div class="gq-table-wrap">
            <table class="gq-table">
                <thead><tr><th>Description</th><th>Category</th><th>Qty</th><th>Unit</th><th>Unit Price</th><th>Total</th></tr></thead>
                <tbody>
                    @foreach ($quotation->items as $item)
                        <tr>
                            <td><strong>{{ $item->description }}</strong></td>
                            <td>{{ ucfirst($item->item_category) }}</td>
                            <td>{{ number_format((float) $item->quantity, 2) }}</td>
                            <td>{{ $item->unit }}</td>
                            <td>PHP {{ number_format((float) $item->unit_price, 2) }}</td>
                            <td><strong>PHP {{ number_format((float) $item->total_price, 2) }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <div class="gq-detail-grid">
        <section class="gq-detail-card">
            <header><h3>Request Information</h3></header>
            <div class="gq-info-grid">
                <div><span>Client</span><strong>{{ $quotation->request?->full_name ?? '—' }}</strong></div>
                <div><span>Email</span><strong>{{ $quotation->request?->email ?? '—' }}</strong></div>
                <div><span>Service</span><strong>{{ $quotation->request?->service_type ?? '—' }}</strong></div>
                <div><span>Address</span><strong>{{ $quotation->request?->address ?? '—' }}</strong></div>
            </div>
        </section>

        <aside class="gq-detail-card">
            <header><h3>Cost Summary</h3></header>
            <div class="gq-cost-list">
                <div><span>Materials</span><strong>PHP {{ number_format((float) $quotation->materials_cost, 2) }}</strong></div>
                <div><span>Labor</span><strong>PHP {{ number_format((float) $quotation->labor_cost, 2) }}</strong></div>
                <div><span>Miscellaneous</span><strong>PHP {{ number_format((float) $quotation->miscellaneous_cost, 2) }}</strong></div>
                <div><span>Subtotal</span><strong>PHP {{ number_format((float) $quotation->subtotal_amount, 2) }}</strong></div>
                <div><span>VAT {{ number_format((float) $quotation->tax_rate, 0) }}%</span><strong>PHP {{ number_format((float) $quotation->tax_amount, 2) }}</strong></div>
                <div class="total"><span>Grand Total</span><strong>PHP {{ number_format((float) $quotation->grand_total, 2) }}</strong></div>
            </div>
        </aside>
    </div>
</div>
@endsection