@extends('hr.layouts.app')

@section('title', 'Quotations')
@section('topbar_title', 'HR Panel')
@section('topbar_subtitle', 'Create and manage client quotations.')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/hr/quotations.css') }}?v=20260922a">
@endpush

@section('content')
@php
    $activeStatus = request('status', 'all');

    $statusTabs = [
        'all' => ['label' => 'All', 'count' => $statusCounts['all'] ?? 0],
        'draft' => ['label' => 'Draft', 'count' => $statusCounts['draft'] ?? 0],
        'sent' => ['label' => 'Sent', 'count' => $statusCounts['sent'] ?? 0],
        'accepted' => ['label' => 'Accepted', 'count' => $statusCounts['accepted'] ?? 0],
        'rejected' => ['label' => 'Rejected', 'count' => $statusCounts['rejected'] ?? 0],
    ];
@endphp

<div class="quotation-index-page">
    @if (session('success'))
        <div class="alert alert-success mb-3">{{ session('success') }}</div>
    @endif

    @if (session('info'))
        <div class="alert alert-info mb-3">{{ session('info') }}</div>
    @endif

    <div class="quotation-index-layout">
        <main class="quotation-index-main">
            <section class="quotation-hero-card">
                <div>
                    <div class="quotation-breadcrumb">
                        <span>HR Panel</span>
                        <i class="fas fa-chevron-right"></i>
                        <strong>Quotations</strong>
                    </div>

                    <h2>Quotations</h2>
                    <p>Create, send, and manage quotations for client service requests.</p>
                </div>

                <div class="quotation-hero-actions">
                    <div class="dropdown">
                        <button class="btn btn-primary quotation-new-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-plus me-2"></i>New Quotation
                        </button>

                        <div class="dropdown-menu dropdown-menu-end quotation-new-menu">
                            <div class="quotation-new-menu-head">
                                <strong>Create from request</strong>
                                <span>Select a reviewed request without quotation yet.</span>
                            </div>

                            @forelse (($quotationRequestsForCreate ?? collect()) as $requestItem)
                                @php
                                    $requestClient = $requestItem->full_name
                                        ?? trim(($requestItem->first_name ?? '') . ' ' . ($requestItem->last_name ?? ''))
                                        ?: ($requestItem->email ?? 'Client');
                                @endphp

                                <a class="dropdown-item quotation-new-item" href="{{ route('hr.quotations.create', $requestItem) }}">
                                    <span>
                                        <strong>{{ $requestClient }}</strong>
                                        <small>{{ $requestItem->service_type ?? 'Service request' }}</small>
                                    </span>
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            @empty
                                <div class="quotation-new-empty">
                                    <i class="fas fa-circle-info"></i>
                                    <span>No request available for quotation creation.</span>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <a href="{{ route('hr.quotations.archived') }}" class="btn btn-outline-secondary quotation-archive-top-btn">
                        <i class="fas fa-box-archive me-1"></i>Archived
                    </a>
                </div>
            </section>

            <section class="quotation-workspace-card">
                <nav class="quotation-status-tabs">
                    @foreach ($statusTabs as $statusKey => $tab)
                        @php
                            $tabParams = request()->except(['page']);
                            if ($statusKey === 'all') {
                                unset($tabParams['status']);
                            } else {
                                $tabParams['status'] = $statusKey;
                            }
                        @endphp

                        <a href="{{ route('hr.quotations.index', $tabParams) }}"
                           class="quotation-status-tab {{ ($activeStatus === $statusKey || ($activeStatus === 'all' && $statusKey === 'all')) ? 'active' : '' }}">
                            <span>{{ $tab['label'] }}</span>
                            <em>{{ $tab['count'] }}</em>
                        </a>
                    @endforeach
                </nav>

                <form method="GET" action="{{ route('hr.quotations.index') }}" class="quotation-compact-filter">
                    <div class="quotation-search-field">
                        <i class="fas fa-search"></i>
                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            value="{{ request('search') }}"
                            placeholder="Search by quotation no., client, email, phone, or service..."
                        >
                    </div>

                    <select name="status" class="form-select quotation-status-select">
                        <option value="">All Status</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                        <option value="sent" @selected(request('status') === 'sent')>Sent</option>
                        <option value="accepted" @selected(request('status') === 'accepted')>Accepted</option>
                        <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                    </select>

                    <button class="btn btn-primary quotation-filter-btn">
                        <i class="fas fa-filter me-1"></i>Apply Filter
                    </button>

                    @if (request()->hasAny(['search', 'status']))
                        <a href="{{ route('hr.quotations.index') }}" class="quotation-clear-link">Clear</a>
                    @else
                        <span class="quotation-clear-link disabled">Clear</span>
                    @endif
                </form>
            </section>

            <section class="quotation-table-card quotation-records-card">
                <div class="quotation-table-header">
                    <div>
                        <h5 class="mb-0"><i class="fas fa-file-contract text-primary me-2"></i>Quotation Records</h5>
                    </div>

                    <strong class="quotation-table-count">
                        Showing {{ method_exists($quotations, 'firstItem') ? ($quotations->firstItem() ?? 0) : $quotations->count() }}
                        to {{ method_exists($quotations, 'lastItem') ? ($quotations->lastItem() ?? $quotations->count()) : $quotations->count() }}
                        of {{ method_exists($quotations, 'total') ? $quotations->total() : $quotations->count() }}
                    </strong>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0 quotation-table">
                        <thead>
                            <tr>
                                <th>Quotation</th>
                                <th>Client</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th>Amount</th>
                                <th>Created</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($quotations as $quotation)
                                @php
                                    $status = strtolower((string) $quotation->status);
                                    $statusClass = match ($status) {
                                        'sent' => 'status-sent',
                                        'draft' => 'status-draft',
                                        'accepted', 'approved' => 'status-accepted',
                                        'rejected', 'cancelled' => 'status-rejected',
                                        default => 'status-default',
                                    };

                                    $clientName = $quotation->request->full_name
                                        ?? trim(($quotation->request->first_name ?? '') . ' ' . ($quotation->request->last_name ?? ''))
                                        ?: ($quotation->request->email ?? '—');
                                @endphp

                                <tr>
                                    <td>
                                        <a href="{{ route('hr.quotations.show', $quotation) }}" class="quotation-no">
                                            {{ $quotation->quotation_no }}
                                        </a>
                                        <div class="small text-muted">
                                            {{ $quotation->invoice ? 'Invoice ready' : 'No invoice yet' }}
                                        </div>
                                    </td>

                                    <td>
                                        <div class="fw-bold text-dark">{{ $clientName }}</div>
                                        @if(!empty($quotation->request->email))
                                            <div class="small text-muted quotation-client-email">
                                                {{ $quotation->request->email }}
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="fw-semibold">{{ $quotation->request->service_type ?? '—' }}</div>
                                        <div class="small text-muted text-capitalize">
                                            {{ $quotation->request->service_category ?? '—' }}
                                        </div>
                                    </td>

                                    <td>
                                        <span class="quotation-status {{ $statusClass }}">
                                            {{ strtoupper(str_replace('_', ' ', $quotation->status)) }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="quotation-total">
                                            PHP {{ number_format((float) $quotation->grand_total, 2) }}
                                        </div>
                                    </td>

                                    <td>
                                        <div class="quotation-created-date">
                                            {{ optional($quotation->created_at)->format('M d, Y') }}
                                        </div>
                                        <div class="small text-muted">
                                            {{ optional($quotation->created_at)->format('h:i A') }}
                                        </div>
                                    </td>

                                    <td class="text-end">
                                        <div class="quotation-row-actions compact">
                                            <a href="{{ route('hr.quotations.show', $quotation) }}"
                                               class="btn btn-sm btn-outline-primary quotation-icon-btn"
                                               title="View details">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary quotation-icon-btn js-archive-quotation"
                                                    title="Archive"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#archiveQuotationModal"
                                                    data-archive-url="{{ route('hr.quotations.archive', $quotation->id) }}"
                                                    data-quotation-no="{{ $quotation->quotation_no }}">
                                                <i class="fas fa-box-archive"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="quotation-empty">
                                            <div class="quotation-empty-icon"><i class="fas fa-file-invoice"></i></div>
                                            <h5 class="fw-bold text-dark mb-1">No quotations found</h5>
                                            <p class="mb-0">Prepared quotations will appear here once service requests are reviewed.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(method_exists($quotations, 'links'))
                    <div class="quotation-pagination-wrap">
                        {{ $quotations->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </section>
        </main>
    </div>
</div>

<div class="modal fade" id="archiveQuotationModal" tabindex="-1" aria-labelledby="archiveQuotationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content archive-modal-content border-0">
            <div class="modal-header archive-modal-header border-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="archive-modal-icon">
                        <i class="fas fa-box-archive"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-1" id="archiveQuotationModalLabel">Archive Quotation</h5>
                        <p class="text-muted small mb-0">Move this quotation out of the active HR quotation list.</p>
                    </div>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="archiveQuotationForm" method="POST" action="">
                @csrf
                @method('PATCH')

                <div class="modal-body pt-0">
                    <div class="archive-warning-box">
                        <div class="fw-bold text-dark mb-1">
                            Archive <span id="archiveQuotationNo">this quotation</span>?
                        </div>
                        <div class="small text-muted">
                            This record will be hidden from active quotations, but it can still be restored from Archived Quotations.
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary archive-modal-btn" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-secondary archive-modal-btn">
                        <i class="fas fa-box-archive me-1"></i>Archive
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
    const archiveModal = document.getElementById('archiveQuotationModal');
    const archiveForm = document.getElementById('archiveQuotationForm');
    const archiveQuotationNo = document.getElementById('archiveQuotationNo');

    if (archiveModal && archiveForm && archiveQuotationNo) {
        archiveModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;

            if (!button) return;

            const archiveUrl = button.getAttribute('data-archive-url');
            const quotationNo = button.getAttribute('data-quotation-no');

            archiveForm.setAttribute('action', archiveUrl);
            archiveQuotationNo.textContent = quotationNo || 'this quotation';
        });
    }
});
</script>
@endpush
