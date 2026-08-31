@extends('inspector.layouts.app')

@section('title', 'Request Details - WRPlumb')
@section('topbar_title', '')
@section('topbar_subtitle', '')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/inspector/request-show.css') }}?v=request-tabs-01">
@endpush

@section('content')
@php
    $inspectionReport = $quotation->inspectionReport;
    $latestInspectionStatus = $quotation->inspectionStatusLogs->first()?->status;
    $reportLocked = $inspectionReport?->status === 'submitted';

    $inspectionSteps = [
        'on_the_way' => ['label' => 'On the Way', 'icon' => 'fa-truck-fast'],
        'arrived_on_site' => ['label' => 'Arrived On Site', 'icon' => 'fa-location-dot'],
        'inspection_started' => ['label' => 'Start Inspection', 'icon' => 'fa-magnifying-glass'],
        'inspection_completed' => ['label' => 'Complete Inspection', 'icon' => 'fa-circle-check'],
    ];

    $statusOrder = [
        'assigned',
        'on_the_way',
        'arrived_on_site',
        'inspection_started',
        'inspection_completed',
        'report_submitted',
    ];

    $latestStatusIndex = array_search($latestInspectionStatus, $statusOrder, true);

    $completedChecklistCount = $inspectionReport
        ? $inspectionReport->checklistItems->where('is_completed', true)->count()
        : 0;

    $totalChecklistCount = $inspectionReport
        ? $inspectionReport->checklistItems->count()
        : 0;

    $hasCoordinates = filled($quotation->latitude) && filled($quotation->longitude);

    $mapDestination = $hasCoordinates
        ? $quotation->latitude . ',' . $quotation->longitude
        : trim(
            collect([
                $quotation->address,
                'Cagayan de Oro City',
                'Philippines',
            ])->filter()->implode(', ')
        );

    $encodedMapDestination = urlencode($mapDestination);

    $googleMapsEmbedUrl =
        'https://www.google.com/maps?q=' .
        $encodedMapDestination .
        '&output=embed';

    $googleMapsDirectionUrl =
        'https://www.google.com/maps/dir/?api=1&destination=' .
        $encodedMapDestination;

    $photoGroups = [
        'before' => 'Before Inspection',
        'during' => 'During Inspection',
        'after' => 'After Inspection',
    ];
@endphp

<div class="request-workspace">
    <div class="request-breadcrumb">
        <a href="{{ route('inspector.quotations.index') }}">Assigned Requests</a>
        <i class="fas fa-chevron-right"></i>
        <span>Request #QR-{{ now()->format('Ymd') }}-{{ str_pad($quotation->id, 4, '0', STR_PAD_LEFT) }}</span>
    </div>

    <section class="request-summary-card">
        <div class="request-summary-main">
            <div>
                <div class="request-title-row">
                    <h1>{{ $quotation->full_name }}</h1>
                    <span class="request-code">
                        QR-{{ now()->format('Ymd') }}-{{ str_pad($quotation->id, 4, '0', STR_PAD_LEFT) }}
                    </span>
                </div>

                <div class="request-tags">
                    <span><i class="fas fa-droplet"></i>{{ $quotation->service_type }}</span>
                    <span><i class="fas fa-flag"></i>{{ ucfirst($quotation->service_category) }}</span>
                </div>
            </div>

            <div class="request-status-cards">
                <div class="status-card">
                    <small>Current Status</small>
                    <strong>
                        <i class="fas fa-circle"></i>
                        {{ $latestInspectionStatus
                            ? ucwords(str_replace('_', ' ', $latestInspectionStatus))
                            : 'Assigned' }}
                    </strong>
                    <span>Updated {{ optional($quotation->updated_at)->format('M d, Y h:i A') }}</span>
                </div>

                <div class="report-status-card {{ $reportLocked ? 'submitted' : 'draft' }}">
                    {{ $reportLocked ? 'SUBMITTED REPORT' : 'DRAFT REPORT' }}
                </div>
            </div>
        </div>

        <div class="workflow-strip">
            @foreach ($inspectionSteps as $status => $step)
                @php
                    $stepIndex = array_search($status, $statusOrder, true);
                    $isCompleted = $latestStatusIndex !== false && $stepIndex <= $latestStatusIndex;
                    $isCurrent = !$isCompleted && (
                        ($latestStatusIndex === false && $status === 'on_the_way') ||
                        ($latestStatusIndex !== false && $stepIndex === $latestStatusIndex + 1)
                    );
                    $isDisabled = $reportLocked || !$isCurrent;
                @endphp

                <form method="POST"
                      action="{{ route('inspector.quotations.inspection-status', $quotation) }}">
                    @csrf
                    <input type="hidden" name="inspection_status" value="{{ $status }}">

                    <button type="submit"
                            class="workflow-pill {{ $isCompleted ? 'completed' : '' }} {{ $isCurrent ? 'current' : '' }}"
                            @disabled($isDisabled)>
                        <i class="fas {{ $step['icon'] }}"></i>
                        <span>{{ $step['label'] }}</span>
                        @if ($isCompleted)
                            <i class="fas fa-circle-check"></i>
                        @endif
                    </button>
                </form>
            @endforeach
        </div>
    </section>

    <nav class="workspace-tabs" aria-label="Request sections">
        <button type="button" class="workspace-tab active" data-tab="overview">
            <i class="fas fa-house"></i>Overview
        </button>
        <button type="button" class="workspace-tab" data-tab="photos">
            <i class="fas fa-camera"></i>Photos
            <span>{{ $inspectionReport?->photos->count() ?? 0 }}</span>
        </button>
        <button type="button" class="workspace-tab" data-tab="checklist">
            <i class="fas fa-list-check"></i>Checklist
            <span>{{ $completedChecklistCount }}/{{ $totalChecklistCount }}</span>
        </button>
        <button type="button" class="workspace-tab" data-tab="report">
            <i class="fas fa-file-lines"></i>Report
        </button>
        <button type="button" class="workspace-tab" data-tab="timeline">
            <i class="fas fa-clock-rotate-left"></i>Timeline
        </button>
        <button type="button" class="workspace-tab" data-tab="estimates">
            <i class="fas fa-calculator"></i>Estimates
        </button>
    </nav>

    <section class="workspace-panel active" data-panel="overview">
        <div class="overview-grid">
            <div class="overview-main">
                <article class="workspace-card">
                    <header>
                        <h2><i class="fas fa-circle-info"></i>Request Information</h2>
                    </header>

                    <div class="workspace-card-body">
                        <div class="info-grid">
                            <div>
                                <i class="fas fa-envelope"></i>
                                <span><strong>Email</strong>{{ $quotation->email }}</span>
                            </div>
                            <div>
                                <i class="fas fa-phone"></i>
                                <span><strong>Phone</strong>{{ $quotation->phone }}</span>
                            </div>
                            <div>
                                <i class="fas fa-location-dot"></i>
                                <span><strong>Address</strong>{{ $quotation->address }}</span>
                            </div>
                            <div>
                                <i class="fas fa-calendar"></i>
                                <span><strong>Preferred Date</strong>{{ optional($quotation->preferred_date)->format('F d, Y') ?? '—' }}</span>
                            </div>
                            <div>
                                <i class="fas fa-calendar-check"></i>
                                <span>
                                    <strong>Appointment</strong>
                                    {{ optional($quotation->appointment_date)->format('F d, Y') ?? 'Not scheduled' }}
                                    @if ($quotation->appointment_time)
                                        at {{ date('h:i A', strtotime($quotation->appointment_time)) }}
                                    @endif
                                </span>
                            </div>
                            <div>
                                <i class="fas fa-clipboard"></i>
                                <span><strong>Problem Details</strong>{{ $quotation->details }}</span>
                            </div>
                            <div class="info-full">
                                <i class="fas fa-message"></i>
                                <span><strong>Admin Notes</strong>{{ $quotation->admin_notes ?? '—' }}</span>
                            </div>
                        </div>

                        <div class="location-section">
                            <h3><i class="fas fa-map-location-dot"></i>Location</h3>

                            <div class="location-grid">
                                <div class="map-frame">
                                    <iframe
                                        src="{{ $googleMapsEmbedUrl }}"
                                        title="Client location map"
                                        loading="lazy"
                                        allowfullscreen
                                        referrerpolicy="no-referrer-when-downgrade"
                                    ></iframe>
                                </div>

                                <div class="location-details">
                                    <strong>Service Address</strong>
                                    <p>{{ $quotation->address }}</p>

                                    <a href="{{ $googleMapsDirectionUrl }}"
                                       target="_blank"
                                       rel="noopener noreferrer">
                                        <i class="fab fa-google"></i>
                                        Open in Google Maps
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="workspace-card quick-actions-card">
                    <header>
                        <h2><i class="fas fa-bolt"></i>Quick Actions</h2>
                    </header>

                    <div class="quick-actions">
                        <button type="button" data-open-tab="photos" class="action-button blue">
                            <i class="fas fa-cloud-arrow-up"></i>Upload Photos
                        </button>
                        <button type="button" data-open-tab="checklist" class="action-button orange">
                            <i class="fas fa-list-check"></i>Save Checklist
                        </button>
                        <button type="button" data-open-tab="report" class="action-button violet">
                            <i class="fas fa-floppy-disk"></i>Save Draft
                        </button>
                        <button type="button" data-open-tab="report" class="action-button red">
                            <i class="fas fa-paper-plane"></i>Submit Report
                        </button>
                    </div>
                </article>
            </div>

            <aside class="overview-side">
                <article class="workspace-card">
                    <header>
                        <h2><i class="fas fa-clock-rotate-left"></i>Inspection Timeline</h2>
                        <button type="button" data-open-tab="timeline">View all</button>
                    </header>

                    <div class="workspace-card-body compact">
                        <div class="mini-timeline">
                            @forelse ($quotation->inspectionStatusLogs->take(4) as $log)
                                <div class="mini-timeline-item">
                                    <span><i class="fas fa-circle-check"></i></span>
                                    <div>
                                        <strong>{{ ucwords(str_replace('_', ' ', $log->status)) }}</strong>
                                        <p>{{ $log->notes ?: 'Inspection status updated.' }}</p>
                                    </div>
                                    <time>{{ optional($log->recorded_at)->format('M d, h:i A') }}</time>
                                </div>
                            @empty
                                <div class="empty-compact">No inspection activity yet.</div>
                            @endforelse
                        </div>
                    </div>
                </article>

                <article class="workspace-card">
                    <header>
                        <h2><i class="fas fa-peso-sign"></i>Estimate Summary</h2>
                    </header>

                    <div class="workspace-card-body compact">
                        <div class="summary-list">
                            <div><span>Materials</span><strong>₱{{ number_format($inspectionReport?->estimated_material_cost ?? 0, 2) }}</strong></div>
                            <div><span>Labor</span><strong>₱{{ number_format($inspectionReport?->estimated_labor_cost ?? 0, 2) }}</strong></div>
                            <div><span>Miscellaneous</span><strong>₱{{ number_format($inspectionReport?->estimated_miscellaneous_cost ?? 0, 2) }}</strong></div>
                            <div class="total"><span>Estimated Total</span><strong>₱{{ number_format($inspectionReport?->estimated_total_cost ?? 0, 2) }}</strong></div>
                        </div>

                        <div class="summary-note">
                            <i class="fas fa-circle-info"></i>
                            Preliminary estimate only. Final quotation will be prepared by HR.
                        </div>
                    </div>
                </article>
            </aside>
        </div>
    </section>

    <section class="workspace-panel" data-panel="photos">
        <article class="workspace-card">
            <header>
                <h2><i class="fas fa-camera"></i>Inspection Photos</h2>
                <span class="counter-badge">{{ $inspectionReport?->photos->count() ?? 0 }} photos</span>
            </header>

            <div class="workspace-card-body">
                @if (!$reportLocked)
                    <form method="POST"
                          action="{{ route('inspector.quotations.inspection-photos.upload', $quotation) }}"
                          enctype="multipart/form-data"
                          class="photo-upload-form">
                        @csrf

                        <div class="upload-fields">
                            <div class="field-group">
                                <label>Photo Category</label>
                                <select name="category" required>
                                    <option value="">Select category</option>
                                    <option value="before" @selected(old('category') === 'before')>Before Inspection</option>
                                    <option value="during" @selected(old('category') === 'during')>During Inspection</option>
                                    <option value="after" @selected(old('category') === 'after')>After Inspection</option>
                                </select>
                            </div>

                            <div class="field-group">
                                <label>Caption</label>
                                <input type="text"
                                       name="caption"
                                       maxlength="500"
                                       value="{{ old('caption') }}"
                                       placeholder="Optional photo description">
                            </div>
                        </div>

                        <label class="photo-dropzone">
                            <input type="file"
                                   name="photos[]"
                                   id="inspectionPhotosInput"
                                   accept="image/jpeg,image/png,image/webp"
                                   multiple
                                   required>

                            <i class="fas fa-cloud-arrow-up"></i>
                            <strong>Choose inspection photos</strong>
                            <small>JPG, PNG or WEBP. Maximum 5 MB each. Up to 8 photos.</small>
                        </label>

                        <div id="inspectionPhotoPreview" class="photo-preview-grid"></div>

                        <div class="form-actions">
                            <button type="submit" class="primary-action">
                                <i class="fas fa-cloud-arrow-up"></i>Upload Photos
                            </button>
                        </div>
                    </form>
                @endif

                <div class="photo-category-grid">
                    @foreach ($photoGroups as $category => $label)
                        @php
                            $categoryPhotos = $inspectionReport
                                ? $inspectionReport->photos->where('category', $category)
                                : collect();
                        @endphp

                        <section class="photo-category">
                            <div class="photo-category-header">
                                <h3>{{ $label }}</h3>
                                <span>{{ $categoryPhotos->count() }} {{ \Illuminate\Support\Str::plural('photo', $categoryPhotos->count()) }}</span>
                            </div>

                            @if ($categoryPhotos->isNotEmpty())
                                <div class="photo-gallery">
                                    @foreach ($categoryPhotos as $photo)
                                        <article class="photo-card">
                                            <a href="{{ $photo->url }}" target="_blank" rel="noopener noreferrer">
                                                <img src="{{ $photo->url }}"
                                                     alt="{{ $photo->caption ?: $photo->category_label }}"
                                                     loading="lazy">
                                            </a>

                                            <div>
                                                <p>{{ $photo->caption ?: 'No caption provided.' }}</p>
                                                <small>{{ optional($photo->created_at)->format('M d, Y h:i A') }}</small>

                                                @if (!$reportLocked)
                                                    <form method="POST"
                                                          action="{{ route('inspector.quotations.inspection-photos.delete', [$quotation, $photo]) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                onclick="return confirm('Delete this inspection photo?')">
                                                            <i class="fas fa-trash"></i>Delete
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            @else
                                <div class="empty-category">
                                    <i class="fas fa-image"></i>
                                    <span>No photos uploaded.</span>
                                </div>
                            @endif
                        </section>
                    @endforeach
                </div>
            </div>
        </article>
    </section>

    <section class="workspace-panel" data-panel="checklist">
        <article class="workspace-card">
            <header>
                <h2><i class="fas fa-list-check"></i>Completion Checklist</h2>
                <span class="counter-badge">{{ $completedChecklistCount }}/{{ $totalChecklistCount }}</span>
            </header>

            <div class="workspace-card-body">
                @if ($inspectionReport)
                    @error('checklist')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror

                    <form method="POST"
                          action="{{ route('inspector.quotations.inspection-checklist.update', $quotation) }}">
                        @csrf

                        <div class="checklist-grid">
                            @foreach ($inspectionReport->checklistItems as $item)
                                <label class="checklist-item">
                                    <input type="checkbox"
                                           name="items[]"
                                           value="{{ $item->id }}"
                                           @checked($item->is_completed)
                                           @disabled($reportLocked)>

                                    <span class="check-box"><i class="fas fa-check"></i></span>

                                    <span>
                                        <strong>{{ $item->label }}</strong>
                                        <small>
                                            {{ $item->is_completed
                                                ? 'Completed ' . optional($item->completed_at)->format('M d, Y h:i A')
                                                : 'Required before report submission' }}
                                        </small>
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        @if (!$reportLocked)
                            <div class="form-actions">
                                <button type="submit" class="secondary-action">
                                    <i class="fas fa-floppy-disk"></i>Save Checklist
                                </button>
                            </div>
                        @endif
                    </form>
                @else
                    <div class="empty-panel">
                        Save the inspection report as draft first to initialize the checklist.
                    </div>
                @endif
            </div>
        </article>
    </section>

    <section class="workspace-panel" data-panel="report">
        <article class="workspace-card">
            <header>
                <h2><i class="fas fa-file-lines"></i>Inspection Report</h2>
                @if ($inspectionReport)
                    <span class="counter-badge">{{ $inspectionReport->report_no }}</span>
                @endif
            </header>

            <div class="workspace-card-body">
                <form method="POST"
                      action="{{ route('inspector.quotations.inspection-report', $quotation) }}"
                      id="inspectionReportForm">
                    @csrf
                    <input type="hidden" name="action" id="inspectionReportAction" value="">

                    <div class="report-grid">
                        <div class="report-fields">
                            <div class="field-group">
                                <label>Inspection Findings <span class="required">*</span></label>
                                <textarea name="findings"
                                          required
                                          @disabled($reportLocked)
                                          placeholder="Describe the observed plumbing issue and site condition...">{{ old('findings', $inspectionReport?->findings) }}</textarea>
                            </div>

                            <div class="field-group">
                                <label>Recommendations</label>
                                <textarea name="recommendations"
                                          @disabled($reportLocked)
                                          placeholder="Enter the recommended repair or installation work...">{{ old('recommendations', $inspectionReport?->recommendations) }}</textarea>
                            </div>

                            <div class="report-note-grid">
                                <div class="field-group">
                                    <label>Client-visible Notes</label>
                                    <textarea name="client_visible_notes"
                                              @disabled($reportLocked)
                                              placeholder="Notes that may be shown to the client...">{{ old('client_visible_notes', $inspectionReport?->client_visible_notes) }}</textarea>
                                </div>

                                <div class="field-group">
                                    <label>Internal Notes</label>
                                    <textarea name="internal_notes"
                                              @disabled($reportLocked)
                                              placeholder="Private notes for Admin and HR...">{{ old('internal_notes', $inspectionReport?->internal_notes) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <aside class="estimate-editor">
                            <div class="estimate-editor-heading">
                                <div>
                                    <h3>Preliminary Cost Estimate</h3>
                                    <p>Add the materials needed for the proposed work.</p>
                                </div>

                                @if (!$reportLocked)
                                    <button
                                        type="button"
                                        class="add-material-button"
                                        id="addMaterialButton"
                                    >
                                        <i class="fas fa-plus"></i>
                                        Add Material
                                    </button>
                                @endif
                            </div>

                            @php
                                $existingMaterials = old(
                                    'materials',
                                    $inspectionReport
                                        ? $inspectionReport->materialItems
                                            ->map(fn ($item) => [
                                                'item_name' => $item->item_name,
                                                'quantity' => $item->quantity,
                                                'unit' => $item->unit,
                                                'unit_cost' => $item->unit_cost,
                                            ])
                                            ->toArray()
                                        : []
                                );
                            @endphp

                            <div class="material-table-wrap">
                                <table class="material-table">
                                    <thead>
                                        <tr>
                                            <th>Material</th>
                                            <th>Qty</th>
                                            <th>Unit</th>
                                            <th>Unit Cost</th>
                                            <th>Subtotal</th>
                                            @if (!$reportLocked)
                                                <th></th>
                                            @endif
                                        </tr>
                                    </thead>

                                    <tbody id="materialItemsBody">
                                        @forelse ($existingMaterials as $index => $material)
                                            <tr class="material-row">
                                                <td>
                                                    <input
                                                        type="text"
                                                        name="materials[{{ $index }}][item_name]"
                                                        value="{{ $material['item_name'] }}"
                                                        placeholder="PVC Pipe"
                                                        required
                                                        @disabled($reportLocked)
                                                    >
                                                </td>

                                                <td>
                                                    <input
                                                        type="number"
                                                        name="materials[{{ $index }}][quantity]"
                                                        value="{{ $material['quantity'] }}"
                                                        min="0.01"
                                                        step="0.01"
                                                        class="material-quantity"
                                                        required
                                                        @disabled($reportLocked)
                                                    >
                                                </td>

                                                <td>
                                                    <input
                                                        type="text"
                                                        name="materials[{{ $index }}][unit]"
                                                        value="{{ $material['unit'] }}"
                                                        placeholder="pcs"
                                                        required
                                                        @disabled($reportLocked)
                                                    >
                                                </td>

                                                <td>
                                                    <input
                                                        type="number"
                                                        name="materials[{{ $index }}][unit_cost]"
                                                        value="{{ $material['unit_cost'] }}"
                                                        min="0"
                                                        step="0.01"
                                                        class="material-unit-cost"
                                                        required
                                                        @disabled($reportLocked)
                                                    >
                                                </td>

                                                <td>
                                                    <span class="material-subtotal">₱0.00</span>
                                                </td>

                                                @if (!$reportLocked)
                                                    <td>
                                                        <button
                                                            type="button"
                                                            class="remove-material-button"
                                                            aria-label="Remove material"
                                                        >
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                @endif
                                            </tr>
                                        @empty
                                            @if (!$reportLocked)
                                                <tr class="material-empty-row">
                                                    <td colspan="6">
                                                        No materials added yet.
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="material-total-row">
                                <span>Materials Total</span>
                                <strong id="materialsTotalDisplay">
                                    ₱{{ number_format($inspectionReport?->estimated_material_cost ?? 0, 2) }}
                                </strong>
                            </div>

                            <div class="estimate-input-grid">
                                <div class="field-group">
                                    <label>Labor (₱)</label>

                                    <input
                                        type="number"
                                        name="estimated_labor_cost"
                                        min="0"
                                        step="0.01"
                                        id="laborCostInput"
                                        value="{{ old('estimated_labor_cost', $inspectionReport?->estimated_labor_cost ?? 0) }}"
                                        @disabled($reportLocked)
                                    >
                                </div>

                                <div class="field-group">
                                    <label>Miscellaneous (₱)</label>

                                    <input
                                        type="number"
                                        name="estimated_miscellaneous_cost"
                                        min="0"
                                        step="0.01"
                                        id="miscellaneousCostInput"
                                        value="{{ old('estimated_miscellaneous_cost', $inspectionReport?->estimated_miscellaneous_cost ?? 0) }}"
                                        @disabled($reportLocked)
                                    >
                                </div>
                            </div>

                            <div class="grand-total-row">
                                <span>Estimated Total</span>

                                <strong id="estimatedTotalDisplay">
                                    ₱{{ number_format($inspectionReport?->estimated_total_cost ?? 0, 2) }}
                                </strong>
                            </div>
                        </aside>
                    </div>

                    @if (!$reportLocked)
                        <div class="form-actions">
                            <button
                                type="submit"
                                class="secondary-action"
                                onclick="document.getElementById('inspectionReportAction').value='draft'"
                            >
                                <i class="fas fa-floppy-disk"></i>
                                Save Draft
                            </button>

                            <button
                                type="button"
                                class="primary-action"
                                id="openSubmitReportModal"
                            >
                                <i class="fas fa-paper-plane"></i>
                                Submit Report
                            </button>
                        </div>
                    @else
                        <div class="submitted-message">
                            <i class="fas fa-circle-check"></i>
                            Submitted on {{ optional($inspectionReport->submitted_at)->format('F d, Y h:i A') }}.
                        </div>
                    @endif
                </form>
            </div>
        </article>
    </section>

    <div class="inspection-submit-modal" id="inspectionSubmitModal" hidden>
        <div class="inspection-submit-backdrop" data-close-submit-modal></div>

        <div
            class="inspection-submit-dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="inspectionSubmitTitle"
        >
            <div class="inspection-submit-icon">
                <i class="fas fa-paper-plane"></i>
            </div>

            <h3 id="inspectionSubmitTitle">Submit Inspection Report?</h3>

            <p>
                Once submitted, this inspection report will be sent to Admin
                and will no longer be editable.
            </p>

            <div class="inspection-submit-summary">
                <div>
                    <span>Report</span>
                    <strong>{{ $inspectionReport?->report_no ?? 'Draft Report' }}</strong>
                </div>

                <div>
                    <span>Estimated Total</span>
                    <strong>
                        ₱{{ number_format($inspectionReport?->estimated_total_cost ?? 0, 2) }}
                    </strong>
                </div>
            </div>

            <div class="inspection-submit-actions">
                <button
                    type="button"
                    class="inspection-submit-btn secondary"
                    data-close-submit-modal
                >
                    Cancel
                </button>

                <button
                    type="button"
                    class="inspection-submit-btn primary"
                    id="confirmSubmitReport"
                >
                    <i class="fas fa-paper-plane"></i>
                    Submit Report
                </button>
            </div>
        </div>
    </div>

    <section class="workspace-panel" data-panel="timeline">
        <article class="workspace-card">
            <header>
                <h2><i class="fas fa-clock-rotate-left"></i>Full Inspection Timeline</h2>
            </header>

            <div class="workspace-card-body">
                <div class="full-timeline">
                    @forelse ($quotation->inspectionStatusLogs as $log)
                        <div class="full-timeline-item">
                            <span><i class="fas fa-circle-check"></i></span>
                            <div>
                                <strong>{{ ucwords(str_replace('_', ' ', $log->status)) }}</strong>
                                <p>{{ $log->notes ?: 'Inspection status updated.' }}</p>
                                <small>by {{ $log->updatedBy?->name ?? 'Inspector' }}</small>
                            </div>
                            <time>{{ optional($log->recorded_at)->format('F d, Y h:i A') }}</time>
                        </div>
                    @empty
                        <div class="empty-panel">No inspection activity recorded yet.</div>
                    @endforelse
                </div>
            </div>
        </article>
    </section>

    <section class="workspace-panel" data-panel="estimates">
        <article class="workspace-card">
            <header>
                <h2><i class="fas fa-calculator"></i>Estimate Details</h2>
            </header>

            <div class="workspace-card-body">
                <div class="estimate-large-grid">
                    <div><span>Materials</span><strong>₱{{ number_format($inspectionReport?->estimated_material_cost ?? 0, 2) }}</strong></div>
                    <div><span>Labor</span><strong>₱{{ number_format($inspectionReport?->estimated_labor_cost ?? 0, 2) }}</strong></div>
                    <div><span>Miscellaneous</span><strong>₱{{ number_format($inspectionReport?->estimated_miscellaneous_cost ?? 0, 2) }}</strong></div>
                    <div class="total"><span>Estimated Total</span><strong>₱{{ number_format($inspectionReport?->estimated_total_cost ?? 0, 2) }}</strong></div>
                </div>

                <div class="summary-note wide">
                    <i class="fas fa-circle-info"></i>
                    This is a preliminary estimate only. Final quotation will be prepared by the HR team.
                </div>
            </div>
        </article>
    </section>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('[data-tab]');
    const panels = document.querySelectorAll('[data-panel]');
    const quickLinks = document.querySelectorAll('[data-open-tab]');

    function activateTab(name, updateHash = true) {
        tabs.forEach((tab) => {
            tab.classList.toggle('active', tab.dataset.tab === name);
        });

        panels.forEach((panel) => {
            panel.classList.toggle('active', panel.dataset.panel === name);
        });

        if (updateHash) {
            history.replaceState(null, '', '#' + name);
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    tabs.forEach((tab) => {
        tab.addEventListener('click', function () {
            activateTab(this.dataset.tab);
        });
    });

    quickLinks.forEach((button) => {
        button.addEventListener('click', function () {
            activateTab(this.dataset.openTab);
        });
    });

    const hashTab = window.location.hash.replace('#', '');
    const validTabs = Array.from(tabs).map((tab) => tab.dataset.tab);

    if (validTabs.includes(hashTab)) {
        activateTab(hashTab, false);
    }

    const photoInput = document.getElementById('inspectionPhotosInput');
    const preview = document.getElementById('inspectionPhotoPreview');

    if (photoInput && preview) {
        photoInput.addEventListener('change', function () {
            preview.innerHTML = '';

            Array.from(photoInput.files).forEach(function (file) {
                if (!file.type.startsWith('image/')) {
                    return;
                }

                const reader = new FileReader();

                reader.onload = function (event) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'photo-preview-item';

                    const image = document.createElement('img');
                    image.src = event.target.result;
                    image.alt = file.name;

                    wrapper.appendChild(image);
                    preview.appendChild(wrapper);
                };

                reader.readAsDataURL(file);
            });
        });
    }
    const addMaterialButton = document.getElementById('addMaterialButton');
    const materialItemsBody = document.getElementById('materialItemsBody');
    const materialsTotalDisplay = document.getElementById('materialsTotalDisplay');
    const estimatedTotalDisplay = document.getElementById('estimatedTotalDisplay');
    const laborCostInput = document.getElementById('laborCostInput');
    const miscellaneousCostInput = document.getElementById('miscellaneousCostInput');

    let materialIndex = materialItemsBody
        ? materialItemsBody.querySelectorAll('.material-row').length
        : 0;

    function formatCurrency(value) {
        return new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP'
        }).format(value);
    }

    function calculateEstimate() {
        if (!materialItemsBody) {
            return;
        }

        let materialsTotal = 0;

        materialItemsBody.querySelectorAll('.material-row').forEach((row) => {
            const quantity = parseFloat(
                row.querySelector('.material-quantity')?.value || 0
            );

            const unitCost = parseFloat(
                row.querySelector('.material-unit-cost')?.value || 0
            );

            const subtotal = quantity * unitCost;
            materialsTotal += subtotal;

            const subtotalDisplay = row.querySelector('.material-subtotal');

            if (subtotalDisplay) {
                subtotalDisplay.textContent = formatCurrency(subtotal);
            }
        });

        const laborCost = parseFloat(laborCostInput?.value || 0);
        const miscellaneousCost = parseFloat(
            miscellaneousCostInput?.value || 0
        );

        const estimatedTotal =
            materialsTotal +
            laborCost +
            miscellaneousCost;

        if (materialsTotalDisplay) {
            materialsTotalDisplay.textContent =
                formatCurrency(materialsTotal);
        }

        if (estimatedTotalDisplay) {
            estimatedTotalDisplay.textContent =
                formatCurrency(estimatedTotal);
        }
    }

    function createMaterialRow(index) {
        const row = document.createElement('tr');

        row.className = 'material-row';

        row.innerHTML = `
            <td>
                <input
                    type="text"
                    name="materials[${index}][item_name]"
                    placeholder="PVC Pipe"
                    required
                >
            </td>

            <td>
                <input
                    type="number"
                    name="materials[${index}][quantity]"
                    min="0.01"
                    step="0.01"
                    value="1"
                    class="material-quantity"
                    required
                >
            </td>

            <td>
                <input
                    type="text"
                    name="materials[${index}][unit]"
                    placeholder="pcs"
                    required
                >
            </td>

            <td>
                <input
                    type="number"
                    name="materials[${index}][unit_cost]"
                    min="0"
                    step="0.01"
                    value="0"
                    class="material-unit-cost"
                    required
                >
            </td>

            <td>
                <span class="material-subtotal">₱0.00</span>
            </td>

            <td>
                <button
                    type="button"
                    class="remove-material-button"
                    aria-label="Remove material"
                >
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;

        return row;
    }

    if (addMaterialButton && materialItemsBody) {
        addMaterialButton.addEventListener('click', function () {
            const emptyRow = materialItemsBody.querySelector(
                '.material-empty-row'
            );

            if (emptyRow) {
                emptyRow.remove();
            }

            materialItemsBody.appendChild(
                createMaterialRow(materialIndex)
            );

            materialIndex++;
            calculateEstimate();
        });

        materialItemsBody.addEventListener('click', function (event) {
            const removeButton = event.target.closest(
                '.remove-material-button'
            );

            if (!removeButton) {
                return;
            }

            removeButton.closest('.material-row').remove();

            if (!materialItemsBody.querySelector('.material-row')) {
                materialItemsBody.innerHTML = `
                    <tr class="material-empty-row">
                        <td colspan="6">
                            No materials added yet.
                        </td>
                    </tr>
                `;
            }

            calculateEstimate();
        });

        materialItemsBody.addEventListener(
            'input',
            calculateEstimate
        );
    }

    laborCostInput?.addEventListener('input', calculateEstimate);
    miscellaneousCostInput?.addEventListener(
        'input',
        calculateEstimate
    );

    calculateEstimate();

    const submitReportModal = document.getElementById('inspectionSubmitModal');
    const openSubmitReportModalButton = document.getElementById('openSubmitReportModal');
    const confirmSubmitReportButton = document.getElementById('confirmSubmitReport');
    const inspectionReportForm = document.getElementById('inspectionReportForm');
    const inspectionReportAction = document.getElementById('inspectionReportAction');

    function openInspectionSubmitModal() {
        if (!submitReportModal) return;

        submitReportModal.hidden = false;
        document.body.classList.add('inspection-modal-open');
    }

    function closeInspectionSubmitModal() {
        if (!submitReportModal) return;

        submitReportModal.hidden = true;
        document.body.classList.remove('inspection-modal-open');
    }

    openSubmitReportModalButton?.addEventListener('click', function () {
        openInspectionSubmitModal();
    });

    document.querySelectorAll('[data-close-submit-modal]').forEach(function (element) {
        element.addEventListener('click', function () {
            closeInspectionSubmitModal();
        });
    });

    confirmSubmitReportButton?.addEventListener('click', function () {
        if (!inspectionReportForm || !inspectionReportAction) return;

        inspectionReportAction.value = 'submit';
        confirmSubmitReportButton.disabled = true;
        inspectionReportForm.requestSubmit();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && submitReportModal && !submitReportModal.hidden) {
            closeInspectionSubmitModal();
        }
    });

});
</script>
@endpush
@endsection