@extends('hr.layouts.app')

@section('title', 'Generate Contract')

@section('content')
<div class="page-header-card mb-4">
    <h2 class="mb-1">Generate Contract</h2>
    <p class="text-muted mb-0">Create a service contract from an approved quotation.</p>
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

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
        <h5 class="mb-3">Quotation Summary</h5>

        <div class="row g-3">
            <div class="col-md-3">
                <div class="small text-muted">Quotation No.</div>
                <div class="fw-semibold">{{ $quotation->quotation_no }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Client</div>
                <div class="fw-semibold">{{ $quotation->request->full_name ?? trim(($quotation->request->first_name ?? '') . ' ' . ($quotation->request->last_name ?? '')) }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Service Type</div>
                <div class="fw-semibold">{{ $quotation->request->service_type }}</div>
            </div>
            <div class="col-md-3">
                <div class="small text-muted">Grand Total</div>
                <div class="fw-semibold">PHP {{ number_format((float) $quotation->grand_total, 2) }}</div>
            </div>

            <div class="col-md-6">
                <div class="small text-muted">Address</div>
                <div class="fw-semibold">{{ $quotation->request->address }}</div>
            </div>

            <div class="col-md-6">
                <div class="small text-muted">Quotation Status</div>
                <div class="fw-semibold text-uppercase">{{ $quotation->status }}</div>
            </div>

            <div class="col-12">
                <div class="small text-muted">Scope Basis / Request Details</div>
                <div class="fw-semibold">{{ $quotation->request->details }}</div>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('hr.contracts.store') }}">
    @csrf
    <input type="hidden" name="quotation_id" value="{{ $quotation->id }}">

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="mb-3">Contract Details</h5>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Contract Title</label>
                            <input
                                type="text"
                                name="title"
                                class="form-control"
                                value="{{ old('title', 'Service Contract for ' . ($quotation->request->service_type ?? 'Project')) }}"
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Contract Date</label>
                            <input
                                type="date"
                                name="contract_date"
                                class="form-control"
                                value="{{ old('contract_date', now()->format('Y-m-d')) }}"
                                required
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Start Date</label>
                            <input
                                type="date"
                                name="start_date"
                                class="form-control"
                                value="{{ old('start_date') }}"
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">End Date</label>
                            <input
                                type="date"
                                name="end_date"
                                class="form-control"
                                value="{{ old('end_date') }}"
                            >
                        </div>

                        <div class="col-12">
                            <label class="form-label">Scope of Work</label>
                            <textarea
                                name="scope_of_work"
                                class="form-control"
                                rows="5"
                                required
                            >{{ old('scope_of_work', $quotation->request->details) }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Special Terms</label>
                            <textarea
                                name="special_terms"
                                class="form-control"
                                rows="4"
                                placeholder="Optional contract terms, conditions, warranty clauses, etc."
                            >{{ old('special_terms') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mt-4">
                <div class="card-body">
                    <h5 class="mb-3">Payment Terms Preview</h5>

                    @if (!empty($quotation->payment_terms_json['phases']))
                        @foreach ($quotation->payment_terms_json['phases'] as $phase)
                            <div class="d-flex justify-content-between border rounded-3 p-3 mb-2">
                                <div>
                                    <div class="fw-semibold">{{ $phase['label'] }}</div>
                                    <div class="small text-muted">{{ $phase['percent'] }}%</div>
                                </div>
                                <div class="fw-semibold">
                                    PHP {{ number_format((float) $phase['amount'], 2) }}
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-muted">No payment terms available.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body">
                    <h5 class="mb-3">Contract Summary</h5>

                    <div class="d-flex justify-content-between mb-2">
                        <span>Status</span>
                        <strong>GENERATED</strong>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span>Total Contract Price</span>
                        <strong>PHP {{ number_format((float) $quotation->grand_total, 2) }}</strong>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Generate Contract</button>
                <a href="{{ route('hr.quotations.show', $quotation) }}" class="btn btn-outline-secondary">Back to Quotation</a>
            </div>
        </div>
    </div>
</form>
@endsection