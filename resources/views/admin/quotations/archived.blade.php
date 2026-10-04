@extends('admin.layouts.app')

@section('title', 'Archived Records - WRPlumb')
@section('topbar_title', 'Archived Records')
@section('topbar_subtitle', 'Review, filter, and restore closed system records across service workflows.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/quotations.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/quotations-archive.css') }}">
@endpush

@section('content')
@php
    $items = method_exists($archives, 'getCollection') ? $archives->getCollection() : collect($archives);
    $totalCount = method_exists($archives, 'total') ? $archives->total() : $items->count();
    $displayedCount = $items->count();
    $selectedType = request('record_type', 'all');
    $latestArchived = !empty($summary['latest_archived'])
        ? \Carbon\Carbon::createFromTimestamp($summary['latest_archived'])->format('M d, Y h:i A')
        : '—';
    $activeFilterCount = collect(['record_type', 'search', 'status', 'archive_source', 'archived_from', 'archived_to'])
        ->filter(function ($key) {
            if ($key === 'record_type') {
                return request('record_type') && request('record_type') !== 'all';
            }
            return request()->filled($key);
        })
        ->count();
    $typeIcons = [
        'all' => 'fa-layer-group',
        'requests' => 'fa-file-signature',
        'quotations' => 'fa-file-invoice-dollar',
        'job_orders' => 'fa-clipboard-check',
        'warranty_claims' => 'fa-shield-halved',
        'back_jobs' => 'fa-rotate-left',
    ];
@endphp

<div class="archive-records-page">
    <section class="archive-page-head">
    <div class="archive-page-head-main">
        <div>
            <h2>Archived Records</h2>
            <p>Review and restore closed records across service workflows.</p>
        </div>
        <div class="archive-head-meta">
            <span><strong>{{ $summary['total_records'] ?? $totalCount }}</strong> archived</span>
            <span><i class="fas fa-clock"></i> {{ $latestArchived }}</span>
        </div>
    </div>
    <div class="archive-head-actions">
        <button type="button" class="btn btn-primary archive-add-btn" data-bs-toggle="modal" data-bs-target="#addArchiveRecordModal">
            <i class="fas fa-plus"></i> Add to Archived
        </button>
        <a href="{{ route('admin.quotations.index') }}" class="btn btn-outline-primary archive-back-btn">
            <i class="fas fa-arrow-left"></i> Active Requests
        </a>
    </div>
</section>

    <section class="archive-filter-card">
        <div class="archive-filter-head">
            <div>
                <h5><i class="fas fa-filter"></i> Find Archived Records</h5>
                <p>Search and narrow archived records by type, status, source, or archive date.</p>
            </div>
            @if ($activeFilterCount)
                <span class="archive-filter-count">{{ $activeFilterCount }} active</span>
            @endif
        </div>
        <form method="GET" action="{{ route('admin.archives.index') }}" class="archive-filter-grid">
            <div class="archive-search-field">
                <label class="form-label">Search</label>
                <div class="archive-search-control">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="text" name="search" class="form-control" placeholder="Reference, client, service, reason..." value="{{ request('search') }}">
                </div>
            </div>
            <div>
                <label class="form-label">Record Type</label>
                <select name="record_type" class="form-select">
                    @foreach ($recordTypes as $typeKey => $typeLabel)
                        <option value="{{ $typeKey }}" @selected($selectedType === $typeKey)>{{ $typeLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All statuses</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="assigned" @selected(request('status') === 'assigned')>Assigned</option>
                    <option value="scheduled" @selected(request('status') === 'scheduled')>Scheduled</option>
                    <option value="in_progress" @selected(request('status') === 'in_progress')>In Progress</option>
                    <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                    <option value="resolved" @selected(request('status') === 'resolved')>Resolved</option>
                    <option value="closed" @selected(request('status') === 'closed')>Closed</option>
                </select>
            </div>
            <div>
                <label class="form-label">Source</label>
                <select name="archive_source" class="form-select">
                    <option value="">All sources</option>
                    <option value="manual" @selected(request('archive_source') === 'manual')>Manual</option>
                    <option value="system" @selected(request('archive_source') === 'system')>System</option>
                </select>
            </div>
            <div>
                <label class="form-label">From</label>
                <input type="date" name="archived_from" class="form-control" value="{{ request('archived_from') }}">
            </div>
            <div>
                <label class="form-label">To</label>
                <input type="date" name="archived_to" class="form-control" value="{{ request('archived_to') }}">
            </div>
            <div class="archive-filter-actions">
                <button class="btn btn-primary" type="submit"><i class="fas fa-filter"></i> Apply</button>
                <a href="{{ route('admin.archives.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </section>

    <section class="archive-records-card">
        <div class="archive-section-head">
            <div>
                <h5><i class="fas fa-box-archive text-primary"></i> Archived Records</h5>
                <p>Showing {{ $displayedCount }} of {{ $totalCount }} archived record(s).</p>
            </div>
            @if ($activeFilterCount)
                <a href="{{ route('admin.archives.index') }}" class="archive-clear-filters">Clear filters</a>
            @endif
        </div>

        @if ($archives->count())
            <div class="archive-table-wrap">
                <table class="table align-middle archive-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Reference</th>
                            <th>Client</th>
                            <th>Service / Subject</th>
                            <th>Status</th>
                            <th>Archived At</th>
                            <th>Reason</th>
                            <th>Source</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($archives as $record)
                            <tr>
                                <td>
                                    <span class="archive-type-badge">
                                        <i class="fas {{ $typeIcons[$record['type'] ?? ''] ?? 'fa-box-archive' }}"></i>
                                        {{ $record['type_label'] }}
                                    </span>
                                </td>
                                <td><div class="archive-reference">{{ $record['reference'] }}</div></td>
                                <td>
                                    <div class="archive-client-name">{{ $record['client'] }}</div>
                                    <div class="archive-muted-text">{{ $record['contact'] }}</div>
                                </td>
                                <td>
                                    <div class="archive-subject">{{ $record['service'] }}</div>
                                    <div class="archive-muted-text text-capitalize">{{ $record['category'] }}</div>
                                </td>
                                <td><span class="archive-status-badge">{{ $record['status'] }}</span></td>
                                <td><span class="archive-date-text">{{ $record['archived_at_display'] }}</span></td>
                                <td><div class="archive-reason-text" title="{{ $record['reason'] }}">{{ $record['reason'] }}</div></td>
                                <td>
                                    <span class="archive-source-badge {{ $record['source_key'] === 'system' ? 'system' : 'manual' }}">
                                        {{ $record['source'] }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="archive-row-actions">
                                        @if ($record['view_url'])
                                            <a href="{{ $record['view_url'] }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i><span>View</span>
                                            </a>
                                        @endif
                                        <button type="button"
                                            class="btn btn-sm btn-success js-admin-restore-record"
                                            data-bs-toggle="modal"
                                            data-bs-target="#restoreRecordModal"
                                            data-restore-url="{{ $record['restore_url'] }}"
                                            data-record-reference="{{ $record['reference'] }}"
                                            data-record-type="{{ $record['type_label'] }}"
                                            data-record-service="{{ $record['service'] }}">
                                            <i class="fas fa-undo"></i><span>Restore</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="archive-pagination">{{ $archives->links() }}</div>
        @else
            <div class="archive-empty-state">
                <div class="archive-empty-icon"><i class="fas fa-box-open"></i></div>
                <div class="fw-bold text-dark mb-1">No archived records found</div>
                <div class="text-muted">Try changing the filters or archive an eligible closed record.</div>
            </div>
        @endif
    </section>
</div>

<div class="modal fade wr-admin-modal" id="addArchiveRecordModal" tabindex="-1" aria-labelledby="addArchiveRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content admin-archive-modal-content archive-add-modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="addArchiveRecordModalLabel">Add Old Records to Archived</h5>
                    <p class="modal-subtitle mb-0">Choose old eligible records and move multiple items into archived records.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulkArchiveRecordForm" method="POST" action="{{ route('admin.archives.bulk-archive') }}">
                @csrf
                <div class="modal-body">
                    <div class="archive-add-helper">
                        <div class="archive-add-helper-icon"><i class="fas fa-circle-info"></i></div>
                        <div>
                            <strong>Bulk archive old records</strong>
                            <p>Select a module and age filter. Only eligible old records will be shown for selection.</p>
                        </div>
                    </div>
                    <div class="archive-add-grid archive-bulk-filter-grid">
                        <div>
                            <label class="form-label">Record Type</label>
                            <select name="record_type" id="archiveRecordType" class="form-select" required>
                                @foreach ($recordTypes as $typeKey => $typeLabel)
                                    @continue($typeKey === 'all')
                                    <option value="{{ $typeKey }}" @selected($typeKey === 'requests')>{{ $typeLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Show Records</label>
                            <select name="age_days" id="archiveAgeDays" class="form-select" required>
                                <option value="30" selected>Older than 30 days</option>
                                <option value="60">Older than 60 days</option>
                                <option value="90">Older than 90 days</option>
                                <option value="180">Older than 180 days</option>
                            </select>
                        </div>
                    </div>
                    <div class="archive-eligibility-note">
                        <i class="fas fa-shield-halved"></i>
                        <span>Only completed, cancelled, resolved, closed, expired, or inactive records are listed. Active work stays protected.</span>
                    </div>
                    <div class="archive-bulk-record-panel">
                        <div class="archive-bulk-record-head">
                            <div>
                                <strong>Eligible Records</strong>
                                <span id="archiveEligibleSummary">Loading eligible records...</span>
                            </div>
                            <label class="archive-select-all-wrap">
                                <input type="checkbox" id="archiveSelectAll">
                                <span>Select all</span>
                            </label>
                        </div>
                        <div id="archiveEligibleRecords" class="archive-eligible-records">
                            <div class="archive-eligible-loading">
                                <i class="fas fa-spinner fa-spin"></i>
                                <span>Loading records...</span>
                            </div>
                        </div>
                    </div>
                    <div class="archive-selected-summary" id="archiveSelectedSummary">
                        <i class="fas fa-check-circle"></i>
                        <span><strong>0</strong> record(s) selected for archiving.</span>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Archive Reason</label>
                        <textarea name="archive_reason" class="form-control" rows="3" placeholder="Example: Manual cleanup for old completed records" required>Manual cleanup for old eligible records</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="bulkArchiveSubmit" class="btn btn-primary" disabled>
                        <i class="fas fa-box-archive me-1"></i> Archive Selected
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade wr-admin-modal" id="restoreRecordModal" tabindex="-1" aria-labelledby="restoreRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content admin-archive-modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="restoreRecordModalLabel">Restore Archived Record</h5>
                    <p class="modal-subtitle mb-0">Return this record to its active module.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="restoreRecordForm" method="POST" action="">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="archive-confirm-box">
                        <div class="archive-confirm-icon restore"><i class="fas fa-undo"></i></div>
                        <div>
                            <div class="archive-confirm-title">Restore <span id="restoreRecordReference">this record</span>?</div>
                            <p class="archive-confirm-text mb-0">Type: <strong id="restoreRecordType">—</strong></p>
                            <p class="archive-confirm-text mb-0">Subject: <strong id="restoreRecordService">—</strong></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-undo me-1"></i> Restore</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const archiveTypeSelect = document.getElementById('archiveRecordType');
    const archiveAgeSelect = document.getElementById('archiveAgeDays');
    const archiveList = document.getElementById('archiveEligibleRecords');
    const archiveSummary = document.getElementById('archiveEligibleSummary');
    const selectAll = document.getElementById('archiveSelectAll');
    const selectedSummary = document.getElementById('archiveSelectedSummary');
    const bulkSubmit = document.getElementById('bulkArchiveSubmit');
    const eligibleUrl = @json(route('admin.archives.eligible-records'));
    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }
    function updateSelectedCount() {
        if (!archiveList || !selectedSummary || !bulkSubmit) return;
        const checkboxes = archiveList.querySelectorAll('input[name="record_ids[]"]');
        const selected = archiveList.querySelectorAll('input[name="record_ids[]"]:checked');
        const count = selected.length;
        selectedSummary.innerHTML = `<i class="fas fa-check-circle"></i><span><strong>${count}</strong> record(s) selected for archiving.</span>`;
        bulkSubmit.disabled = count === 0;
        if (selectAll) {
            selectAll.checked = checkboxes.length > 0 && count === checkboxes.length;
            selectAll.indeterminate = count > 0 && count < checkboxes.length;
        }
    }
    function renderEligibleRecords(records) {
        if (!archiveList) return;
        if (!records.length) {
            archiveList.innerHTML = `
                <div class="archive-eligible-empty">
                    <i class="fas fa-box-open"></i>
                    <strong>No eligible old records found</strong>
                    <span>Try another record type or age filter.</span>
                </div>
            `;
            updateSelectedCount();
            return;
        }
        archiveList.innerHTML = records.map(function (record) {
            return `
                <label class="archive-eligible-item">
                    <input type="checkbox" name="record_ids[]" value="${escapeHtml(record.id)}">
                    <div class="archive-eligible-copy">
                        <strong>${escapeHtml(record.reference)} — ${escapeHtml(record.client)}</strong>
                        <span>${escapeHtml(record.service)} • ${escapeHtml(record.status)} • ${escapeHtml(record.age_label)}</span>
                    </div>
                    <span class="archive-eligible-date">${escapeHtml(record.basis_date_display)}</span>
                </label>
            `;
        }).join('');
        archiveList.querySelectorAll('input[name="record_ids[]"]').forEach(function (checkbox) {
            checkbox.addEventListener('change', updateSelectedCount);
        });
        updateSelectedCount();
    }
    async function loadEligibleRecords() {
        if (!archiveTypeSelect || !archiveAgeSelect || !archiveList || !archiveSummary) return;
        const type = archiveTypeSelect.value;
        const ageDays = archiveAgeSelect.value || 30;
        if (!type) {
            archiveList.innerHTML = '<div class="archive-eligible-empty"><i class="fas fa-layer-group"></i><strong>Choose a record type first</strong></div>';
            archiveSummary.textContent = 'Select a type to show eligible records.';
            updateSelectedCount();
            return;
        }
        archiveList.innerHTML = '<div class="archive-eligible-loading"><i class="fas fa-spinner fa-spin"></i><span>Loading records...</span></div>';
        archiveSummary.textContent = 'Loading eligible records...';
        if (selectAll) selectAll.checked = false;
        if (bulkSubmit) bulkSubmit.disabled = true;
        try {
            const url = `${eligibleUrl}?record_type=${encodeURIComponent(type)}&age_days=${encodeURIComponent(ageDays)}`;
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'Unable to load eligible records.');
            }
            archiveSummary.textContent = `${data.count} eligible record(s) older than ${data.age_days} days.`;
            renderEligibleRecords(data.records || []);
        } catch (error) {
            archiveSummary.textContent = 'Unable to load records.';
            archiveList.innerHTML = `
                <div class="archive-eligible-empty danger">
                    <i class="fas fa-triangle-exclamation"></i>
                    <strong>${escapeHtml(error.message || 'Unable to load eligible records.')}</strong>
                </div>
            `;
            updateSelectedCount();
        }
    }
    if (archiveTypeSelect && archiveAgeSelect) {
        archiveTypeSelect.addEventListener('change', loadEligibleRecords);
        archiveAgeSelect.addEventListener('change', loadEligibleRecords);
        const addArchiveModal = document.getElementById('addArchiveRecordModal');
        if (addArchiveModal) {
            addArchiveModal.addEventListener('shown.bs.modal', loadEligibleRecords);
        }
    }
    if (selectAll && archiveList) {
        selectAll.addEventListener('change', function () {
            archiveList.querySelectorAll('input[name="record_ids[]"]').forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
            });
            updateSelectedCount();
        });
    }
    const restoreModal = document.getElementById('restoreRecordModal');
    const restoreForm = document.getElementById('restoreRecordForm');
    const restoreReference = document.getElementById('restoreRecordReference');
    const restoreType = document.getElementById('restoreRecordType');
    const restoreService = document.getElementById('restoreRecordService');
    if (restoreModal && restoreForm) {
        restoreModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;
            restoreForm.setAttribute('action', button.getAttribute('data-restore-url'));
            restoreReference.textContent = button.getAttribute('data-record-reference') || 'this record';
            restoreType.textContent = button.getAttribute('data-record-type') || 'Archived Record';
            restoreService.textContent = button.getAttribute('data-record-service') || '—';
        });
    }
});
</script>
@endpush