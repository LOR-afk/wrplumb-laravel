@extends('client.layouts.app')

@section('title', 'My Invoices')
@section('topbar_title', 'My Invoices')
@section('topbar_subtitle', 'Review billing records and payment status for your services.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/client/invoices-index.css') }}?v=20260818a">
@endpush

@section('content')
@php
    $invoiceCollection = collect($invoices->items() ?? $invoices);

    $totalInvoices = method_exists($invoices, 'total')
        ? $invoices->total()
        : $invoiceCollection->count();

    $paidCount = $invoiceCollection
        ->filter(fn ($invoice) => strtolower((string) $invoice->status) === 'paid')
        ->count();

    $partialCount = $invoiceCollection
        ->filter(fn ($invoice) => in_array(strtolower((string) $invoice->status), ['partially_paid', 'partial']))
        ->count();

    $outstandingVisible = $invoiceCollection
        ->filter(fn ($invoice) => strtolower((string) $invoice->status) !== 'paid')
        ->sum(fn ($invoice) => (float) ($invoice->total_amount ?? 0));
@endphp

<div class="client-invoices-page">
    <section class="invoice-stats">
        <article>
            <span class="invoice-stat-icon blue"><i class="fas fa-file-invoice-dollar"></i></span>
            <div>
                <small>Total Invoices</small>
                <strong>{{ $totalInvoices }}</strong>
            </div>
        </article>

        <article>
            <span class="invoice-stat-icon green"><i class="fas fa-circle-check"></i></span>
            <div>
                <small>Fully Paid</small>
                <strong>{{ $paidCount }}</strong>
            </div>
        </article>

        <article>
            <span class="invoice-stat-icon orange"><i class="fas fa-chart-pie"></i></span>
            <div>
                <small>Partially Paid</small>
                <strong>{{ $partialCount }}</strong>
            </div>
        </article>

        <article>
            <span class="invoice-stat-icon red"><i class="fas fa-wallet"></i></span>
            <div>
                <small>Visible Outstanding</small>
                <strong class="money">PHP {{ number_format($outstandingVisible, 2) }}</strong>
            </div>
        </article>
    </section>

    <section class="invoice-list-card">
        <div class="invoice-list-head">
            <div>
                <span>Billing Records</span>
                <h3>Invoice History</h3>
            </div>

            <small>
                {{ $totalInvoices }}
                {{ $totalInvoices === 1 ? 'invoice' : 'invoices' }}
            </small>
        </div>

        @if ($invoices->count())
            <div class="invoice-table-wrap">
                <table class="invoice-table">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Invoice Date</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($invoices as $invoice)
                            @php
                                $statusKey = strtolower((string) ($invoice->status ?? 'pending'));

                                $statusClass = match ($statusKey) {
                                    'paid' => 'green',
                                    'partially_paid', 'partial' => 'orange',
                                    'unpaid', 'pending' => 'blue',
                                    'overdue' => 'red',
                                    'cancelled', 'canceled', 'void' => 'gray',
                                    default => 'gray',
                                };

                                $statusLabel = match ($statusKey) {
                                    'partially_paid' => 'Partially Paid',
                                    'cancelled', 'canceled' => 'Cancelled',
                                    default => ucfirst(str_replace('_', ' ', $statusKey)),
                                };
                            @endphp

                            <tr>
                                <td>
                                    <a
                                        href="{{ route('client.invoices.show', $invoice) }}"
                                        class="invoice-number"
                                    >
                                        {{ $invoice->invoice_no }}
                                    </a>
                                </td>

                                <td>
                                    <div class="invoice-service">
                                        <strong>{{ $invoice->quotation->request->service_type ?? 'Service' }}</strong>

                                        @if (!empty($invoice->quotation?->request?->project_type))
                                            <span>{{ $invoice->quotation->request->project_type }}</span>
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    <span class="invoice-status {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <td>
                                    <strong class="invoice-total">
                                        PHP {{ number_format((float) $invoice->total_amount, 2) }}
                                    </strong>
                                </td>

                                <td>
                                    <div class="invoice-date">
                                        <strong>{{ optional($invoice->invoice_date)->format('M d, Y') ?? '—' }}</strong>
                                    </div>
                                </td>

                                <td class="text-end">
                                    <a
                                        href="{{ route('client.invoices.show', $invoice) }}"
                                        class="invoice-view-btn"
                                    >
                                        <i class="fas fa-eye"></i>
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(method_exists($invoices, 'links'))
                <div class="invoice-pagination">
                    {{ $invoices->links() }}
                </div>
            @endif
        @else
            <div class="invoice-empty">
                <div class="invoice-empty-icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>

                <strong>No invoices available yet</strong>
                <p>Your invoices will appear here once billing is generated for your accepted service quotation.</p>
            </div>
        @endif
    </section>
</div>
@endsection