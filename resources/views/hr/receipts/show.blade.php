@extends('hr.layouts.app')

@section('title', 'Receipt Details')

@push('styles')
    <link
        rel="stylesheet"
        href="{{ asset('css/receipts/official-receipt.css') }}"
    >
@endpush

@section('content')
<div class="receipt-page-toolbar no-print">
    <div>
        <h1>Receipt Details</h1>
        <p>
            {{ $receipt->receipt_no }} · Official Payment Receipt
        </p>
    </div>

    <div class="receipt-page-actions">
        <a
            href="{{ route('hr.receipts.index') }}"
            class="btn btn-outline-secondary"
        >
            Back to Receipts
        </a>

        <a
            href="{{ route('hr.payments.show', $receipt->payment) }}"
            class="btn btn-outline-primary"
        >
            View Payment
        </a>

        @if ($receipt->payment?->invoice)
            <a
                href="{{ route(
                    'hr.invoices.show',
                    $receipt->payment->invoice
                ) }}"
                class="btn btn-outline-dark"
            >
                View Invoice
            </a>
        @endif

        <button
            type="button"
            class="btn btn-dark"
            onclick="window.print()"
        >
            Print Receipt
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

<div class="receipt-preview">
    @include('receipts.document', ['receipt' => $receipt])
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function updatePrintedAt() {
            const formatted = new Date().toLocaleString('en-US', {
                month: 'long',
                day: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });

            document.querySelectorAll('.receipt-printed-at')
                .forEach(function (element) {
                    element.textContent = formatted;
                });
        }

        updatePrintedAt();
        setInterval(updatePrintedAt, 1000);
    });
</script>
@endsection