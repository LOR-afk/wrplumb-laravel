@extends('hr.layouts.app')

@section('title', 'Contract Details')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/contracts/legal-contract.css') }}">
@endpush

@section('content')
<div class="legal-contract-toolbar no-print">
    <div>
        <h1>Contract Details</h1>
        <p>
            {{ $contract->contract_no }} ·
            {{ strtoupper($contract->status) }}
        </p>
    </div>

    <div class="legal-toolbar-actions">
        <a
            href="{{ route('hr.contracts.index') }}"
            class="btn btn-outline-secondary"
        >
            Back to Contracts
        </a>

        <a
            href="{{ route('hr.quotations.show', $contract->quotation) }}"
            class="btn btn-outline-primary"
        >
            View Quotation
        </a>

        @if ($contract->status === 'accepted')
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

        <button
            type="button"
            class="btn btn-dark"
            onclick="window.print()"
        >
            Print Contract
        </button>
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

<div class="legal-contract-preview">
    @include('contracts.legal-document', ['contract' => $contract])
</div>
@endsection