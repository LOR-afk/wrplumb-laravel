@extends('hr.layouts.app')

@section('title', 'Contract Details')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/contracts/legal-contract.css') }}?v=contract-revision-void-01">
@endpush

@section('content')
<div class="legal-contract-toolbar no-print">
    <div>
        <h1>Contract Details</h1>
        <p>
            {{ $contract->contract_no }}
            · Revision {{ $contract->revision_no ?? 1 }}
            · {{ strtoupper($contract->status) }}
        </p>
    </div>

    <div class="legal-toolbar-actions">
        <a
            href="{{ route('hr.contracts.index') }}"
            class="btn btn-outline-secondary"
        >
            Back
        </a>

        @if (in_array($contract->status, ['generated', 'sent']))
            <a
                href="{{ route('hr.contracts.edit', $contract) }}"
                class="btn btn-primary"
            >
                Edit Contract
            </a>
        @elseif ($contract->status === 'accepted')
            <form
                method="POST"
                action="{{ route('hr.contracts.finalize', $contract) }}"
            >
                @csrf

                <button type="submit" class="btn btn-success">
                    Finalize Contract
                </button>
            </form>
        @endif

        <div class="dropdown">
            <button
                type="button"
                class="btn btn-outline-dark dropdown-toggle"
                data-bs-toggle="dropdown"
                aria-expanded="false"
            >
                More
            </button>

            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li>
                    <a
                        href="{{ route('hr.quotations.show', $contract->quotation) }}"
                        class="dropdown-item"
                    >
                        View Quotation
                    </a>
                </li>

                @if ($contract->status === 'accepted')
                    <li>
                        <a
                            href="{{ route('hr.contracts.edit', $contract) }}"
                            class="dropdown-item"
                        >
                            Edit Contract
                        </a>
                    </li>
                @endif

                <li>
                    <button
                        type="button"
                        class="dropdown-item"
                        onclick="window.print()"
                    >
                        Print Contract
                    </button>
                </li>

                @if ($contract->status !== 'voided')
                    <li><hr class="dropdown-divider"></li>

                    <li>
                        <button
                            type="button"
                            class="dropdown-item text-danger"
                            data-bs-toggle="modal"
                            data-bs-target="#voidContractModal"
                        >
                            Void Contract
                        </button>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success no-print">
        {{ session('success') }}
    </div>
@endif

@if (session('info'))
    <div class="alert alert-info no-print">
        {{ session('info') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger no-print">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if ($contract->status === 'voided')
    <div class="contract-void-notice no-print">
        <strong>This contract is voided.</strong>

        <span>
            {{ $contract->void_reason }}
        </span>

        @if ($contract->voided_at)
            <small>
                Voided {{ $contract->voided_at->format('M d, Y h:i A') }}
                @if ($contract->voider)
                    by {{ $contract->voider->name ?? $contract->voider->first_name }}
                @endif
            </small>
        @endif
    </div>
@endif

@if (($contract->revision_no ?? 1) > 1 && $contract->status !== 'voided')
    <div class="contract-revision-notice no-print">
        This is revision {{ $contract->revision_no }} of the contract.
        If an earlier version was already sent or accepted, the revised terms require client acceptance again.
    </div>
@endif

<div class="legal-contract-preview">
    @include('contracts.legal-document', ['contract' => $contract])
</div>

@if ($contract->status !== 'voided')
    <div
        class="modal fade"
        id="voidContractModal"
        tabindex="-1"
        aria-labelledby="voidContractModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content contract-action-modal">
                <form
                    method="POST"
                    action="{{ route('hr.contracts.void', $contract) }}"
                >
                    @csrf
                    @method('PATCH')

                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title" id="voidContractModalLabel">
                                Void Contract
                            </h5>
                            <p class="mb-0 text-muted">
                                The contract will remain in the system for audit purposes.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-warning">
                            Voiding does not automatically delete or change related quotation, invoice, payment, or receipt records.
                        </div>

                        <label for="void_reason" class="form-label">
                            Reason for voiding
                        </label>

                        <textarea
                            id="void_reason"
                            name="void_reason"
                            class="form-control"
                            rows="4"
                            maxlength="1000"
                            required
                        >{{ old('void_reason') }}</textarea>
                    </div>

                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            data-bs-dismiss="modal"
                        >
                            Cancel
                        </button>

                        <button type="submit" class="btn btn-danger">
                            Confirm Void
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection
