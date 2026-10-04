@extends('hr.layouts.app')

@section('title', 'Archived Quotations')
@section('topbar_title', 'Archived Quotations')
@section('topbar_subtitle', 'View and restore quotations that were removed from active HR records.')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/hr/archived-quotations.css') }}?v=archived-quotations-02">
@endpush

@section('content')
<div class="archived-quotation-page">
    <section class="aq-hero">
        <div>
            <div class="aq-breadcrumb">
                <span>HR Panel</span>
                <i class="fas fa-chevron-right"></i>
                <span>Quotations</span>
                <i class="fas fa-chevron-right"></i>
                <strong>Archived</strong>
            </div>

            <h1>Archived Quotations</h1>
            <p>Review quotations removed from the active list and restore records when needed.</p>
        </div>

        <a href="{{ route('hr.quotations.index') }}" class="aq-back-btn">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Active Quotations</span>
        </a>
    </section>

    <section class="aq-summary-grid">
        <article class="aq-summary-card">
            <div class="aq-summary-copy">
                <span class="aq-summary-label">Archived Quotations</span>
                <strong>{{ number_format($summary['archived_quotations'] ?? 0) }}</strong>
                <small>Total quotation records currently stored in the archive.</small>
            </div>

            <span class="aq-summary-icon blue">
                <i class="fas fa-box-archive"></i>
            </span>
        </article>

        <article class="aq-summary-card">
            <div class="aq-summary-copy">
                <span class="aq-summary-label">Latest Archived</span>
                <strong class="aq-date-value">
                    {{ !empty($summary['latest_archived']) ? \Carbon\Carbon::parse($summary['latest_archived'])->format('M d, Y') : '—' }}
                </strong>
                <small>
                    {{ !empty($summary['latest_archived']) ? \Carbon\Carbon::parse($summary['latest_archived'])->format('h:i A') : 'No archived record yet' }}
                </small>
            </div>

            <span class="aq-summary-icon orange">
                <i class="fas fa-clock"></i>
            </span>
        </article>
    </section>

    <section class="aq-panel">
        <header class="aq-panel-header">
            <div>
                <span class="aq-panel-kicker">Archive Management</span>
                <h2>Archived Quotation Records</h2>
                <p>Search archived quotations, review details, and restore records to the active list.</p>
            </div>

            <span class="aq-record-count">
                {{ number_format($quotations->total()) }}
                {{ \Illuminate\Support\Str::plural('record', $quotations->total()) }}
            </span>
        </header>

        <div class="aq-panel-body">
            <form method="GET" action="{{ route('hr.quotations.archived') }}" class="aq-filter-bar">
                <div class="aq-filter-field aq-search-field">
                    <label for="archived-search">Search</label>

                    <div class="aq-input-wrap">
                        <i class="fas fa-search"></i>
                        <input
                            type="search"
                            id="archived-search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Quotation no., client, email, or service..."
                        >
                    </div>
                </div>

                <div class="aq-filter-field">
                    <label for="archived-status">Status</label>
                    <select id="archived-status" name="status">
                        <option value="">All Statuses</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                        <option value="sent" @selected(request('status') === 'sent')>Sent</option>
                        <option value="accepted" @selected(request('status') === 'accepted')>Accepted</option>
                        <option value="declined" @selected(request('status') === 'declined')>Declined</option>
                    </select>
                </div>

                <div class="aq-filter-actions">
                    <button type="submit" class="aq-filter-btn">
                        <i class="fas fa-filter"></i>
                        <span>Apply Filter</span>
                    </button>

                    @if (request()->filled('search') || request()->filled('status'))
                        <a href="{{ route('hr.quotations.archived') }}" class="aq-clear-btn">Clear</a>
                    @endif
                </div>
            </form>

            @if ($quotations->count())
                <div class="aq-table-wrap">
                    <table class="aq-table">
                        <thead>
                            <tr>
                                <th>Quotation</th>
                                <th>Client</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th>Archived At</th>
                                <th>Reason</th>
                                <th class="aq-action-head">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($quotations as $quotation)
                                @php
                                    $statusKey = strtolower((string) $quotation->status);
                                @endphp

                                <tr>
                                    <td>
                                        <div class="aq-quotation-cell">
                                            <strong>{{ $quotation->quotation_no }}</strong>
                                            <span>Archived record</span>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="aq-client-cell">
                                            <strong>{{ $quotation->request?->full_name ?? 'No linked request' }}</strong>
                                            <span>{{ $quotation->request?->email ?? '—' }}</span>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="aq-service-cell">
                                            <strong>{{ $quotation->request?->service_type ?? '—' }}</strong>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="aq-status aq-status-{{ $statusKey }}">
                                            <i class="fas fa-circle"></i>
                                            {{ ucwords(str_replace('_', ' ', $quotation->status)) }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="aq-date-cell">
                                            <strong>
                                                {{ $quotation->archived_at ? \Carbon\Carbon::parse($quotation->archived_at)->format('M d, Y') : '—' }}
                                            </strong>
                                            <span>
                                                {{ $quotation->archived_at ? \Carbon\Carbon::parse($quotation->archived_at)->format('h:i A') : '' }}
                                            </span>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="aq-reason">
                                            {{ $quotation->archive_reason ?? '—' }}
                                        </span>
                                    </td>

                                    <td class="aq-action-cell">
                                        <button
                                            type="button"
                                            class="aq-restore-btn restore-quotation-btn"
                                            data-restore-url="{{ route('hr.quotations.restore', $quotation->id) }}"
                                            data-quotation-no="{{ $quotation->quotation_no }}"
                                        >
                                            <i class="fas fa-rotate-left"></i>
                                            <span>Restore</span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="aq-pagination">
                    <div class="aq-pagination-copy">
                        Showing {{ $quotations->firstItem() }} to {{ $quotations->lastItem() }} of {{ $quotations->total() }}
                    </div>

                    <div>
                        {{ $quotations->links() }}
                    </div>
                </div>
            @else
                <div class="aq-empty-state">
                    <span class="aq-empty-icon">
                        <i class="fas fa-box-archive"></i>
                    </span>

                    <h3>No archived quotations found</h3>
                    <p>Archived quotations will appear here after records are moved from the active quotation list.</p>

                    @if (request()->filled('search') || request()->filled('status'))
                        <a href="{{ route('hr.quotations.archived') }}">Clear filters</a>
                    @endif
                </div>
            @endif
        </div>
    </section>
</div>

<div class="modal fade" id="restoreQuotationModal" tabindex="-1" aria-labelledby="restoreQuotationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content aq-restore-modal">
            <div class="modal-header">
                <div class="aq-modal-heading">
                    <span><i class="fas fa-rotate-left"></i></span>

                    <div>
                        <h5 class="modal-title" id="restoreQuotationModalLabel">Restore Quotation</h5>
                        <p>Return this quotation to the active HR quotation list.</p>
                    </div>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="restoreQuotationForm" method="POST" action="">
                @csrf
                @method('PATCH')

                <div class="modal-body">
                    <div class="aq-restore-confirmation">
                        <span>Quotation</span>
                        <strong id="restoreQuotationNo">this quotation</strong>
                        <p>This record will become available again in the active quotation list.</p>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="aq-modal-cancel" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="aq-modal-restore">
                        <i class="fas fa-rotate-left"></i>
                        Restore Quotation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const restoreModalElement = document.getElementById('restoreQuotationModal');
    const restoreForm = document.getElementById('restoreQuotationForm');
    const restoreQuotationNo = document.getElementById('restoreQuotationNo');

    if (!restoreModalElement || !restoreForm || !restoreQuotationNo || typeof bootstrap === 'undefined') {
        return;
    }

    const restoreModal = new bootstrap.Modal(restoreModalElement);

    document.querySelectorAll('.restore-quotation-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            const restoreUrl = button.getAttribute('data-restore-url');
            const quotationNo = button.getAttribute('data-quotation-no');

            restoreForm.setAttribute('action', restoreUrl);
            restoreQuotationNo.textContent = quotationNo || 'this quotation';

            restoreModal.show();
        });
    });
});
</script>
@endpush
