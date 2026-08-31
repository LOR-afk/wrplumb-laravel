@extends('hr.layouts.app')

@section('title', 'Quotations')
@section('topbar_title', 'HR Panel')
@section('topbar_subtitle', 'Handle routed support concerns and client communication.')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/hr/quotations.css') }}?v=20260609b">
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

            <section class="quotation-stat-grid">
                <article class="quotation-stat-card">
                    <div class="quotation-stat-icon bg-blue"><i class="fas fa-file-invoice"></i></div>
                    <div>
                        <div class="quotation-stat-label">Total Quotations</div>
                        <div class="quotation-stat-value">{{ $summary['total_quotations'] ?? 0 }}</div>
                        <small>All time</small>
                    </div>
                </article>

                <article class="quotation-stat-card">
                    <div class="quotation-stat-icon bg-green"><i class="fas fa-paper-plane"></i></div>
                    <div>
                        <div class="quotation-stat-label">Sent Quotations</div>
                        <div class="quotation-stat-value">{{ $summary['sent_quotations'] ?? 0 }}</div>
                        <small>This year</small>
                    </div>
                </article>

                <article class="quotation-stat-card">
                    <div class="quotation-stat-icon bg-gold"><i class="fas fa-peso-sign"></i></div>
                    <div>
                        <div class="quotation-stat-label">Total Quoted</div>
                        <div class="quotation-stat-value small-money">PHP {{ number_format((float) ($summary['total_amount'] ?? 0), 2) }}</div>
                        <small>Active quotations</small>
                    </div>
                </article>

                <article class="quotation-stat-card">
                    <div class="quotation-stat-icon bg-slate"><i class="fas fa-clock"></i></div>
                    <div>
                        <div class="quotation-stat-label">Latest Quotation</div>
                        <div class="quotation-stat-value latest-date">
                            {{ !empty($summary['latest_created']) ? \Carbon\Carbon::parse($summary['latest_created'])->format('M d, Y') : '—' }}
                        </div>
                        <small>Most recent</small>
                    </div>
                </article>
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
                        <h5 class="mb-1"><i class="fas fa-file-contract text-primary me-2"></i>Quotation Records</h5>
                        <p class="text-muted mb-0">Track quotation status, totals, client details, and billing readiness.</p>
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
                                <th>Quotation No.</th>
                                <th>Client</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Prepared By</th>
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

                                    $preparedBy = $quotation->preparedBy->name
                                        ?? trim(($quotation->preparedBy->first_name ?? '') . ' ' . ($quotation->preparedBy->last_name ?? ''))
                                        ?: '—';
                                @endphp

                                <tr>
                                    <td>
                                        <a href="{{ route('hr.quotations.show', $quotation) }}" class="quotation-no">{{ $quotation->quotation_no }}</a>
                                        <div class="small text-muted">{{ $quotation->invoice ? 'Invoice ready' : 'No invoice yet' }}</div>
                                    </td>

                                    <td>
                                        <div class="fw-bold text-dark">{{ $clientName }}</div>
                                        <div class="small text-muted">
                                            {{ $quotation->request->email ?? '' }}
                                            @if(!empty($quotation->request->phone))
                                                • {{ $quotation->request->phone }}
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        <div class="fw-semibold">{{ $quotation->request->service_type ?? '—' }}</div>
                                        <div class="small text-muted text-capitalize">{{ $quotation->request->service_category ?? '—' }}</div>
                                    </td>

                                    <td>
                                        <span class="quotation-status {{ $statusClass }}">{{ strtoupper(str_replace('_', ' ', $quotation->status)) }}</span>
                                    </td>

                                    <td>
                                        <div class="quotation-total">PHP {{ number_format((float) $quotation->grand_total, 2) }}</div>
                                        <div class="small text-muted">{{ $quotation->items_count ?? $quotation->items->count() }} item(s)</div>
                                    </td>

                                    <td>{{ $preparedBy }}</td>
                                    <td>{{ optional($quotation->created_at)->format('M d, Y h:i A') }}</td>

                                    <td class="text-end">
                                        <div class="quotation-row-actions compact">
                                            <a href="{{ route('hr.quotations.show', $quotation) }}" class="btn btn-sm btn-outline-primary quotation-icon-btn" title="View details">
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
                                    <td colspan="8">
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

        <aside class="quotation-index-side">
            <section class="quotation-overview-card">
                <div class="quotation-overview-head">
                    <h5><i class="fas fa-calendar-days me-2"></i>{{ now()->format('M Y') }} Overview</h5>
                </div>

                <div class="quotation-overview-total">
                    <span>Total Quoted</span>
                    <strong>PHP {{ number_format((float) ($summary['total_amount'] ?? 0), 2) }}</strong>
                    <small>Across active quotations</small>
                </div>

                <div class="quotation-mini-chart">
                    <span></span><span></span><span></span><span></span>
                </div>
            </section>

            <section class="quotation-overview-card">
                <div class="quotation-overview-head">
                    <h5>Pending Actions</h5>
                </div>

                <div class="quotation-pending-list">
                    <div class="quotation-pending-row blue">
                        <span><i class="fas fa-peso-sign"></i></span>
                        <div>
                            <strong>{{ $statusCounts['draft'] ?? 0 }}</strong>
                            <small>Draft quotations</small>
                        </div>
                    </div>

                    <div class="quotation-pending-row gold">
                        <span><i class="fas fa-clock"></i></span>
                        <div>
                            <strong>{{ $statusCounts['sent'] ?? 0 }}</strong>
                            <small>Waiting for acceptance</small>
                        </div>
                    </div>

                    <div class="quotation-pending-row red">
                        <span><i class="fas fa-circle-xmark"></i></span>
                        <div>
                            <strong>{{ $statusCounts['rejected'] ?? 0 }}</strong>
                            <small>Rejected quotations</small>
                        </div>
                    </div>
                </div>
            </section>

            <section class="quotation-template-callout">
                <span><i class="fas fa-copy"></i></span>
                <div>
                    <strong>Create faster with templates</strong>
                    <small>Save time by using quotation templates.</small>
                    <a href="#">Manage Templates <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </section>
        </aside>
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
