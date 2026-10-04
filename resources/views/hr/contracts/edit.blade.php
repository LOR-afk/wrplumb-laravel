@extends('hr.layouts.app')

@section('title', 'Edit Contract')

@section('content')
@php
    $quotationScopeItems = $contract->quotation?->scopeItems ?? collect();

    $quotationScopeText = $quotationScopeItems
        ->pluck('description')
        ->map(fn ($description) => trim((string) $description))
        ->filter()
        ->values()
        ->map(fn ($description, $index) => ($index + 1) . '. ' . $description)
        ->implode("\n");

    $currentScope = old('scope_of_work', $contract->scope_of_work);
@endphp

<div class="page-header-card mb-4">
    <h2 class="mb-1">Edit Contract</h2>
    <p class="text-muted mb-0">
        {{ $contract->contract_no }} · Revision {{ $contract->revision_no ?? 1 }}
    </p>
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

@if (in_array($contract->status, ['sent', 'accepted']))
    <div class="alert alert-warning">
        Saving changes will create a new revision and reset this contract to GENERATED.
        Any previous client acceptance will be cleared so the revised contract can be reviewed again.
    </div>
@endif

<form
    method="POST"
    action="{{ route('hr.contracts.update', $contract) }}"
>
    @csrf
    @method('PUT')

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="mb-3">Contract Details</h5>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Contract Title</label>
                            <input
                                type="text"
                                name="title"
                                class="form-control"
                                value="{{ old('title', $contract->title) }}"
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Contract Date</label>
                            <input
                                type="date"
                                name="contract_date"
                                class="form-control"
                                value="{{ old('contract_date', optional($contract->contract_date)->format('Y-m-d')) }}"
                                required
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Start Date</label>
                            <input
                                type="date"
                                name="start_date"
                                class="form-control"
                                value="{{ old('start_date', optional($contract->start_date)->format('Y-m-d')) }}"
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">End Date</label>
                            <input
                                type="date"
                                name="end_date"
                                class="form-control"
                                value="{{ old('end_date', optional($contract->end_date)->format('Y-m-d')) }}"
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Client Name</label>
                            <input
                                type="text"
                                name="client_name"
                                class="form-control"
                                value="{{ old('client_name', $contract->client_name) }}"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Total Contract Price</label>
                            <input
                                type="number"
                                name="total_contract_price"
                                class="form-control"
                                min="0"
                                step="0.01"
                                value="{{ old('total_contract_price', $contract->total_contract_price) }}"
                                required
                            >
                            <div class="form-text">
                                Payment phase amounts will be recalculated using the existing phase percentages.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Client Address</label>
                            <textarea
                                name="client_address"
                                class="form-control"
                                rows="3"
                            >{{ old('client_address', $contract->client_address) }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Project Address</label>
                            <textarea
                                name="project_address"
                                class="form-control"
                                rows="3"
                            >{{ old('project_address', $contract->project_address) }}</textarea>
                        </div>

                        <div class="col-12">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                <label class="form-label mb-0">Scope of Work</label>

                                @if ($quotationScopeItems->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-2">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            id="loadQuotationScopeBtn"
                                        >
                                            Load from Quotation
                                        </button>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-secondary"
                                            id="restoreContractScopeBtn"
                                        >
                                            Restore Current Contract
                                        </button>
                                    </div>
                                @endif
                            </div>

                            @if ($quotationScopeItems->isNotEmpty())
                                <div class="border rounded-3 p-3 mb-3 bg-light">
                                    <div class="small fw-semibold mb-2">
                                        Scope from Accepted Quotation
                                    </div>

                                    <ol class="mb-0 ps-3">
                                        @foreach ($quotationScopeItems as $scopeItem)
                                            <li class="mb-1">{{ $scopeItem->description }}</li>
                                        @endforeach
                                    </ol>
                                </div>
                            @else
                                <div class="alert alert-light border py-2">
                                    No structured Scope of Works was recorded on the linked quotation.
                                </div>
                            @endif

                            <textarea
                                id="scope_of_work"
                                name="scope_of_work"
                                class="form-control"
                                rows="9"
                                required
                            >{{ $currentScope }}</textarea>

                            <div class="form-text">
                                You may edit the scope manually, or load the Scope of Works from the linked quotation.
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Special Terms</label>
                            <textarea
                                name="special_terms"
                                class="form-control"
                                rows="5"
                            >{{ old('special_terms', $contract->special_terms) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="mb-3">Revision Summary</h5>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Current Revision</span>
                        <strong>{{ $contract->revision_no ?? 1 }}</strong>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Current Status</span>
                        <strong class="text-uppercase">{{ $contract->status }}</strong>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span>New Revision</span>
                        <strong>{{ ($contract->revision_no ?? 1) + 1 }}</strong>
                    </div>
                </div>
            </div>

            @if ($quotationScopeItems->isNotEmpty())
                <div class="card border-0 shadow-sm rounded-4 mt-3">
                    <div class="card-body">
                        <h6 class="mb-2">Linked Quotation</h6>
                        <div class="small text-muted mb-1">Quotation No.</div>
                        <div class="fw-semibold mb-3">
                            {{ $contract->quotation?->quotation_no ?? '—' }}
                        </div>

                        <div class="small text-muted mb-1">Scope Items</div>
                        <div class="fw-semibold">
                            {{ $quotationScopeItems->count() }}
                        </div>
                    </div>
                </div>
            @endif

            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    Save Revision
                </button>

                <a
                    href="{{ route('hr.contracts.show', $contract) }}"
                    class="btn btn-outline-secondary"
                >
                    Cancel
                </a>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const scopeField = document.getElementById('scope_of_work');
    const loadQuotationScopeBtn = document.getElementById('loadQuotationScopeBtn');
    const restoreContractScopeBtn = document.getElementById('restoreContractScopeBtn');

    const quotationScope = @json($quotationScopeText);
    const contractScope = @json($contract->scope_of_work ?? '');

    if (loadQuotationScopeBtn) {
        loadQuotationScopeBtn.addEventListener('click', function () {
            scopeField.value = quotationScope;
            scopeField.focus();
        });
    }

    if (restoreContractScopeBtn) {
        restoreContractScopeBtn.addEventListener('click', function () {
            scopeField.value = contractScope;
            scopeField.focus();
        });
    }
});
</script>
@endpush
