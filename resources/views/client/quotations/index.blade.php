@extends('client.layouts.app')

@section('title', 'My Quotations')
@section('topbar_title', 'My Quotations')
@section('topbar_subtitle', 'Review quotations prepared for your service requests.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/client/quotations-index.css') }}?v=20260818a">
@endpush

@section('content')
@php
    $quotationCollection = collect($quotations->items() ?? $quotations);

    $totalCount = method_exists($quotations, 'total')
        ? $quotations->total()
        : $quotationCollection->count();

    $acceptedCount = $quotationCollection
        ->filter(fn ($quotation) => strtolower((string) $quotation->status) === 'accepted')
        ->count();

    $pendingCount = $quotationCollection
        ->filter(fn ($quotation) => in_array(strtolower((string) $quotation->status), ['draft', 'sent', 'pending']))
        ->count();

    $pageTotalValue = $quotationCollection
        ->sum(fn ($quotation) => (float) ($quotation->grand_total ?? 0));
@endphp

<div class="client-quotations-page">
    @if (session('success'))
        <div class="quotation-notice success">
            <span><i class="fas fa-circle-check"></i></span>
            <div>
                <strong>Quotation updated</strong>
                <p>{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session('info'))
        <div class="quotation-notice info">
            <span><i class="fas fa-circle-info"></i></span>
            <div>
                <strong>Quotation information</strong>
                <p>{{ session('info') }}</p>
            </div>
        </div>
    @endif

    <section class="quotation-overview">
        <div class="quotation-overview-copy">
            <span>Quotation Center</span>
            <h2>Review your service quotations.</h2>
            <p>Check quotation amounts, service details, and current approval status before opening a record.</p>
        </div>

        <a href="{{ route('client.requests.create') }}" class="quotation-overview-action">
            <i class="fas fa-plus"></i>
            Book a Service
        </a>
    </section>

    <section class="quotation-stats">
        <article>
            <span class="quotation-stat-icon blue"><i class="fas fa-file-invoice-dollar"></i></span>
            <div>
                <small>Total Quotations</small>
                <strong>{{ $totalCount }}</strong>
            </div>
        </article>

        <article>
            <span class="quotation-stat-icon green"><i class="fas fa-circle-check"></i></span>
            <div>
                <small>Accepted</small>
                <strong>{{ $acceptedCount }}</strong>
            </div>
        </article>

        <article>
            <span class="quotation-stat-icon orange"><i class="fas fa-clock"></i></span>
            <div>
                <small>Pending</small>
                <strong>{{ $pendingCount }}</strong>
            </div>
        </article>

        <article>
            <span class="quotation-stat-icon violet"><i class="fas fa-peso-sign"></i></span>
            <div>
                <small>Visible Total</small>
                <strong class="money">PHP {{ number_format($pageTotalValue, 2) }}</strong>
            </div>
        </article>
    </section>

    <section class="quotation-list-card">
        <div class="quotation-list-head">
            <div>
                <span>Records</span>
                <h3>Quotation History</h3>
            </div>

            <small>
                {{ $totalCount }}
                {{ $totalCount === 1 ? 'quotation' : 'quotations' }}
            </small>
        </div>

        @if ($quotations->count())
            <div class="quotation-table-wrap">
                <table class="quotation-table">
                    <thead>
                        <tr>
                            <th>Quotation</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Created</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($quotations as $quotation)
                            @php
                                $statusKey = strtolower((string) ($quotation->status ?? 'pending'));

                                $statusClass = match ($statusKey) {
                                    'accepted' => 'accepted',
                                    'declined', 'rejected' => 'declined',
                                    'sent' => 'sent',
                                    'draft' => 'draft',
                                    default => 'pending',
                                };

                                $statusLabel = ucfirst(str_replace('_', ' ', $statusKey));
                            @endphp

                            <tr>
                                <td>
                                    <a
                                        href="{{ route('client.quotations.show', $quotation) }}"
                                        class="quotation-number"
                                    >
                                        {{ $quotation->quotation_no }}
                                    </a>
                                </td>

                                <td>
                                    <div class="quotation-service">
                                        <strong>{{ $quotation->request->service_type ?? 'Service' }}</strong>

                                        @if (!empty($quotation->request?->project_type))
                                            <span>{{ $quotation->request->project_type }}</span>
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    <span class="quotation-status {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <td>
                                    <strong class="quotation-total">
                                        PHP {{ number_format((float) $quotation->grand_total, 2) }}
                                    </strong>
                                </td>

                                <td>
                                    <div class="quotation-date">
                                        <strong>{{ optional($quotation->created_at)->format('M d, Y') ?? '—' }}</strong>
                                        <span>{{ optional($quotation->created_at)->format('h:i A') ?? '' }}</span>
                                    </div>
                                </td>

                                <td class="text-end">
                                    <a
                                        href="{{ route('client.quotations.show', $quotation) }}"
                                        class="quotation-view-btn"
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

            @if(method_exists($quotations, 'links'))
                <div class="quotation-pagination">
                    {{ $quotations->links() }}
                </div>
            @endif
        @else
            <div class="quotation-empty">
                <div class="quotation-empty-icon">
                    <i class="fas fa-file-circle-xmark"></i>
                </div>

                <strong>No quotations available yet</strong>
                <p>Quotations will appear here after WRPlumb prepares pricing for your submitted requests.</p>

                <a href="{{ route('client.requests.create') }}">
                    Book a Service
                </a>
            </div>
        @endif
    </section>
</div>
@endsection