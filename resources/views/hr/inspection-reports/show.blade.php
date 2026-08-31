@extends('hr.layouts.app')

@section('title', 'Inspection Report Details')
@section('topbar_title', 'Inspection Report')
@section('topbar_subtitle', 'Review inspector findings and preliminary project estimates.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/hr/inspection-reports.css') }}?v=inspection-reports-01">
@endpush

@section('content')
@php
    $requestRecord = $inspectionReport->quotationRequest;
    $quotation = $requestRecord?->quotation;

    $inspectorName = $inspectionReport->inspector->name
        ?? trim(($inspectionReport->inspector->first_name ?? '') . ' ' . ($inspectionReport->inspector->last_name ?? ''))
        ?: 'Inspector';

    $completedChecklist = $inspectionReport->checklistItems->where('is_completed', true)->count();
    $totalChecklist = $inspectionReport->checklistItems->count();
@endphp

<div class="hr-inspection-page">
    <section class="inspection-detail-hero">
        <div>
            <span>INSPECTION REPORT</span>
            <div class="inspection-title-row">
                <h2>{{ $inspectionReport->report_no }}</h2>

                @if ($inspectionReport->reviewed_at)
                    <em class="inspection-status green">Reviewed</em>
                @else
                    <em class="inspection-status orange">Pending Review</em>
                @endif
            </div>

            <p>
                {{ $requestRecord?->full_name ?? 'Client' }}
                ·
                {{ $requestRecord?->service_type ?? 'Service Request' }}
            </p>
        </div>

        <div class="inspection-detail-actions">
            <a href="{{ route('hr.inspection-reports.index') }}" class="inspection-action-btn secondary">
                <i class="fas fa-arrow-left"></i>
                Reports
            </a>

            @if ($quotation)
                <a href="{{ route('hr.quotations.show', $quotation) }}" class="inspection-action-btn">
                    <i class="fas fa-file-invoice-dollar"></i>
                    View Quotation
                </a>
            @else
                <a href="{{ route('hr.quotations.create', $requestRecord) }}" class="inspection-action-btn primary">
                    <i class="fas fa-plus"></i>
                    Create Quotation
                </a>
            @endif
        </div>
    </section>

    <section class="inspection-detail-summary">
        <article><span>Inspector</span><strong>{{ $inspectorName }}</strong></article>
        <article><span>Submitted</span><strong>{{ optional($inspectionReport->submitted_at)->format('M d, Y h:i A') ?? '—' }}</strong></article>
        <article><span>Checklist</span><strong>{{ $completedChecklist }}/{{ $totalChecklist }} Complete</strong></article>
        <article><span>Estimate</span><strong>PHP {{ number_format((float) $inspectionReport->estimated_total_cost, 2) }}</strong></article>
    </section>

    <div class="inspection-detail-grid">
        <section class="inspection-detail-card">
            <header>
                <span class="inspection-card-icon blue"><i class="fas fa-file-lines"></i></span>
                <div>
                    <h3>Findings & Recommendations</h3>
                    <p>Inspector's submitted field assessment.</p>
                </div>
            </header>

            <div class="inspection-card-body">
                <div class="inspection-copy-block">
                    <span>Inspection Findings</span>
                    <p>{{ $inspectionReport->findings }}</p>
                </div>

                <div class="inspection-copy-block">
                    <span>Recommendations</span>
                    <p>{{ $inspectionReport->recommendations ?: 'No recommendation provided.' }}</p>
                </div>

                <div class="inspection-notes-grid">
                    <div class="inspection-copy-block">
                        <span>Client-visible Notes</span>
                        <p>{{ $inspectionReport->client_visible_notes ?: '—' }}</p>
                    </div>

                    <div class="inspection-copy-block">
                        <span>Internal Notes</span>
                        <p>{{ $inspectionReport->internal_notes ?: '—' }}</p>
                    </div>
                </div>
            </div>
        </section>

        <aside class="inspection-detail-card">
            <header>
                <span class="inspection-card-icon green"><i class="fas fa-calculator"></i></span>
                <div>
                    <h3>Preliminary Estimate</h3>
                    <p>Reference only. HR may revise quotation pricing.</p>
                </div>
            </header>

            <div class="inspection-card-body">
                <div class="inspection-cost-list">
                    <div><span>Materials</span><strong>PHP {{ number_format((float) $inspectionReport->estimated_material_cost, 2) }}</strong></div>
                    <div><span>Labor</span><strong>PHP {{ number_format((float) $inspectionReport->estimated_labor_cost, 2) }}</strong></div>
                    <div><span>Miscellaneous</span><strong>PHP {{ number_format((float) $inspectionReport->estimated_miscellaneous_cost, 2) }}</strong></div>
                    <div class="total"><span>Estimated Total</span><strong>PHP {{ number_format((float) $inspectionReport->estimated_total_cost, 2) }}</strong></div>
                </div>
            </div>
        </aside>
    </div>

    <section class="inspection-detail-card">
        <header>
            <span class="inspection-card-icon violet"><i class="fas fa-list"></i></span>
            <div>
                <h3>Estimated Materials</h3>
                <p>Materials submitted by the inspector.</p>
            </div>
        </header>

        <div class="inspection-table-wrap">
            <table class="inspection-material-table">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>Unit Cost</th>
                        <th>Total</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($inspectionReport->materialItems as $item)
                        <tr>
                            <td><strong>{{ $item->item_name }}</strong></td>
                            <td>{{ number_format((float) $item->quantity, 2) }}</td>
                            <td>{{ $item->unit }}</td>
                            <td>PHP {{ number_format((float) $item->unit_cost, 2) }}</td>
                            <td><strong>PHP {{ number_format((float) $item->subtotal, 2) }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No materials listed.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if ($inspectionReport->photos->count())
        <section class="inspection-detail-card">
            <header>
                <span class="inspection-card-icon orange"><i class="fas fa-images"></i></span>
                <div>
                    <h3>Inspection Photos</h3>
                    <p>{{ $inspectionReport->photos->count() }} uploaded photo(s).</p>
                </div>
            </header>

            <div class="inspection-photo-grid">
                @foreach ($inspectionReport->photos as $photo)
                    <a href="{{ $photo->url }}" target="_blank" rel="noopener noreferrer">
                        <img src="{{ $photo->url }}" alt="{{ $photo->caption ?: 'Inspection photo' }}">
                        <span>{{ $photo->caption ?: ucfirst($photo->category) }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="inspection-detail-card">
        <header>
            <span class="inspection-card-icon slate"><i class="fas fa-user-check"></i></span>
            <div>
                <h3>HR Review</h3>
                <p>Document that HR has reviewed the submitted report.</p>
            </div>
        </header>

        <div class="inspection-card-body">
            @if ($inspectionReport->reviewed_at)
                <div class="inspection-reviewed-box">
                    <i class="fas fa-circle-check"></i>
                    <div>
                        <strong>Reviewed {{ optional($inspectionReport->reviewed_at)->format('M d, Y h:i A') }}</strong>
                        <p>{{ $inspectionReport->review_notes ?: 'No review notes.' }}</p>
                    </div>
                </div>
            @else
                <form method="POST" action="{{ route('hr.inspection-reports.review', $inspectionReport) }}">
                    @csrf
                    @method('PATCH')

                    <label class="inspection-review-label">Review Notes</label>
                    <textarea
                        name="review_notes"
                        rows="3"
                        class="inspection-review-textarea"
                        placeholder="Optional HR review notes..."
                    >{{ old('review_notes') }}</textarea>

                    <div class="inspection-review-actions">
                        <button type="submit" class="inspection-action-btn primary">
                            <i class="fas fa-circle-check"></i>
                            Mark as Reviewed
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </section>
</div>
@endsection