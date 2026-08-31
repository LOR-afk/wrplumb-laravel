@extends('client.layouts.app')

@section('title', 'My Receipts')
@section('topbar_title', 'My Receipts')
@section('topbar_subtitle', 'View receipts issued for your confirmed payments.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/client/receipts-index.css') }}?v=20260818a">
@endpush

@section('content')
@php
    $receiptCollection = collect($receipts->items() ?? $receipts);

    $totalReceipts = method_exists($receipts, 'total')
        ? $receipts->total()
        : $receiptCollection->count();

    $visibleAmount = $receiptCollection
        ->sum(fn ($receipt) => (float) ($receipt->amount_received ?? 0));
@endphp

<div class="client-receipts-page">
    <section class="receipt-stats">
        <article>
            <span class="receipt-stat-icon blue"><i class="fas fa-receipt"></i></span>
            <div>
                <small>Total Receipts</small>
                <strong>{{ $totalReceipts }}</strong>
            </div>
        </article>

        <article>
            <span class="receipt-stat-icon green"><i class="fas fa-circle-check"></i></span>
            <div>
                <small>Issued Receipts</small>
                <strong>{{ $receiptCollection->count() }}</strong>
            </div>
        </article>

        <article class="wide">
            <span class="receipt-stat-icon violet"><i class="fas fa-peso-sign"></i></span>
            <div>
                <small>Visible Amount Received</small>
                <strong class="money">PHP {{ number_format($visibleAmount, 2) }}</strong>
            </div>
        </article>
    </section>

    <section class="receipt-list-card">
        <div class="receipt-list-head">
            <div>
                <span>Payment Proof</span>
                <h3>Receipt History</h3>
            </div>

            <small>
                {{ $totalReceipts }}
                {{ $totalReceipts === 1 ? 'receipt' : 'receipts' }}
            </small>
        </div>

        @if ($receipts->count())
            <div class="receipt-table-wrap">
                <table class="receipt-table">
                    <thead>
                        <tr>
                            <th>Receipt</th>
                            <th>Payment</th>
                            <th>Invoice</th>
                            <th>Amount Received</th>
                            <th>Receipt Date</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($receipts as $receipt)
                            <tr>
                                <td>
                                    <a
                                        href="{{ route('client.receipts.show', $receipt) }}"
                                        class="receipt-number"
                                    >
                                        {{ $receipt->receipt_no }}
                                    </a>
                                </td>

                                <td>
                                    <div class="receipt-reference">
                                        <strong>{{ $receipt->payment->payment_no ?? '—' }}</strong>
                                        <span>Payment record</span>
                                    </div>
                                </td>

                                <td>
                                    <div class="receipt-reference">
                                        <strong>{{ $receipt->payment->invoice->invoice_no ?? '—' }}</strong>
                                        <span>Linked invoice</span>
                                    </div>
                                </td>

                                <td>
                                    <strong class="receipt-amount">
                                        PHP {{ number_format((float) $receipt->amount_received, 2) }}
                                    </strong>
                                </td>

                                <td>
                                    <div class="receipt-date">
                                        <strong>{{ optional($receipt->receipt_date)->format('M d, Y') ?? '—' }}</strong>
                                    </div>
                                </td>

                                <td class="text-end">
                                    <a
                                        href="{{ route('client.receipts.show', $receipt) }}"
                                        class="receipt-view-btn"
                                    >
                                        <i class="fas fa-eye"></i>
                                        View Receipt
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(method_exists($receipts, 'links'))
                <div class="receipt-pagination">
                    {{ $receipts->links() }}
                </div>
            @endif
        @else
            <div class="receipt-empty">
                <div class="receipt-empty-icon">
                    <i class="fas fa-receipt"></i>
                </div>

                <strong>No receipts available yet</strong>
                <p>Receipts will appear here after confirmed payments are recorded and a receipt is issued.</p>
            </div>
        @endif
    </section>
</div>
@endsection