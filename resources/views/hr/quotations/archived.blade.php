@extends('hr.layouts.app')

@section('title', 'Archived Quotations')
@section('topbar_title', 'Archived Quotations')
@section('topbar_subtitle', 'View and restore quotations that were removed from active HR records.')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1 class="mb-1">Archived Quotations</h1>
            <p class="mb-0">View quotations that were archived from the active HR quotation list.</p>
        </div>

        <a href="{{ route('hr.quotations.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i> Back to Active Quotations
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-label text-uppercase">Archived Quotations</div>
                    <div class="stat-value">{{ $summary['archived_quotations'] ?? 0 }}</div>
                </div>
                <div class="stat-icon blue">
                    <i class="fas fa-box-archive"></i>
                </div>
            </div>
            <div class="stat-helper">Total quotations currently stored in archive.</div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="stat-card">
            <div class="stat-top">
                <div>
                    <div class="stat-label text-uppercase">Latest Archived</div>
                    <div class="h5 fw-bold mb-0 text-dark">
                        {{ !empty($summary['latest_archived']) ? \Carbon\Carbon::parse($summary['latest_archived'])->format('M d, Y h:i A') : '—' }}
                    </div>
                </div>
                <div class="stat-icon orange">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="stat-helper">Most recent quotation archive timestamp.</div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h4><i class="fas fa-box-archive text-primary me-2"></i>Archived Quotation Records</h4>
                <p class="text-muted mb-0 small">Search archived quotations and restore records when needed.</p>
            </div>
        </div>
    </div>

    <div class="panel-body">
        <form method="GET" action="{{ route('hr.quotations.archived') }}" class="row g-3 mb-4">
            <div class="col-lg-8">
                <label class="form-label fw-bold">Search</label>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       class="form-control"
                       placeholder="Search quotation no., client, email, or service...">
            </div>

            <div class="col-lg-3">
                <label class="form-label fw-bold">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                    <option value="sent" @selected(request('status') === 'sent')>Sent</option>
                    <option value="accepted" @selected(request('status') === 'accepted')>Accepted</option>
                    <option value="declined" @selected(request('status') === 'declined')>Declined</option>
                </select>
            </div>

            <div class="col-lg-1 d-grid align-self-end">
                <button type="submit" class="btn btn-primary" title="Search">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>

        @if ($quotations->count())
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Quotation No.</th>
                            <th>Client</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Archived At</th>
                            <th>Reason</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($quotations as $quotation)
                            <tr>
                                <td class="fw-bold text-primary">{{ $quotation->quotation_no }}</td>
                                <td>
                                    <div class="fw-bold">{{ $quotation->request?->full_name ?? 'No linked request' }}</div>
                                    <div class="small text-muted">{{ $quotation->request?->email ?? '—' }}</div>
                                </td>
                                <td>{{ $quotation->request?->service_type ?? '—' }}</td>
                                <td>
                                    <span class="badge-soft gray text-uppercase">
                                        {{ $quotation->status }}
                                    </span>
                                </td>
                                <td>
                                    {{ $quotation->archived_at ? \Carbon\Carbon::parse($quotation->archived_at)->format('M d, Y h:i A') : '—' }}
                                </td>
                                <td>{{ $quotation->archive_reason ?? '—' }}</td>
                                <td class="text-end">
                                    <button type="button"
                                            class="btn btn-sm btn-success restore-quotation-btn"
                                            data-restore-url="{{ route('hr.quotations.restore', $quotation->id) }}"
                                            data-quotation-no="{{ $quotation->quotation_no }}">
                                        <i class="fas fa-undo me-1"></i> Restore
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $quotations->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-light" style="width: 64px; height: 64px;">
                    <i class="fas fa-box-archive fa-2x text-muted"></i>
                </div>
                <h5 class="fw-bold">No archived quotations found</h5>
                <p class="text-muted mb-0">Archived quotations will appear here after records are archived.</p>
            </div>
        @endif
    </div>
</div>

<div class="modal fade" id="restoreQuotationModal" tabindex="-1" aria-labelledby="restoreQuotationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold" id="restoreQuotationModalLabel">Restore Quotation</h5>
                    <p class="text-muted small mb-0">Return this quotation to the active HR quotation list.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="restoreQuotationForm" method="POST" action="">
                @csrf
                @method('PATCH')

                <div class="modal-body">
                    <div class="p-3 rounded-4 bg-light border">
                        Restore <strong id="restoreQuotationNo">this quotation</strong> to active records?
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-success rounded-3">
                        <i class="fas fa-undo me-1"></i> Restore
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
