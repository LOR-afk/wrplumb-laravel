@extends('client.layouts.app')

@section('title', 'Contract Details')

@section('topbar_title', 'Contract Details')
@section('topbar_subtitle', 'Review your formal service contract.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/contracts/legal-contract.css') }}">
@endpush

@section('content')
<div class="legal-contract-toolbar no-print">
    <div>
        <h1>Service Contract</h1>
        <p>
            {{ $contract->contract_no }} ·
            {{ strtoupper($contract->status) }}
        </p>
    </div>

    <div class="legal-toolbar-actions">
        <a
            href="{{ route('client.contracts.index') }}"
            class="btn btn-outline-secondary"
        >
            Back to My Contracts
        </a>

        <a
            href="{{ route('client.quotations.show', $contract->quotation) }}"
            class="btn btn-outline-primary"
        >
            View Quotation
        </a>

        @if (!in_array($contract->status, ['accepted', 'finalized', 'cancelled']))
            <form
                method="POST"
                action="{{ route('client.contracts.accept', $contract) }}"
                onsubmit="return confirm('Do you confirm that you have reviewed and accept this contract?');"
            >
                @csrf

                <button type="submit" class="btn btn-success">
                    Accept Contract
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

@if ($errors->any())
    <div class="alert alert-danger no-print">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if ($contract->status === 'accepted')
    <div class="alert alert-success no-print">
        You accepted this contract on
        {{ optional($contract->client_accepted_at)->format('F d, Y h:i A') }}.
    </div>
@endif

<div class="legal-contract-preview">
    @include('contracts.legal-document', ['contract' => $contract])
</div>
@endsection