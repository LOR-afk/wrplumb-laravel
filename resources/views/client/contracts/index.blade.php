@extends('client.layouts.app')

@section('title', 'My Contracts')
@section('topbar_title', 'My Contracts')
@section('topbar_subtitle', 'Review generated service contracts and their current status.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/client/contracts-index.css') }}?v=20260818a">
@endpush

@section('content')
@php
    $contractCollection = collect($contracts->items() ?? $contracts);

    $totalContracts = method_exists($contracts, 'total')
        ? $contracts->total()
        : $contractCollection->count();

    $acceptedCount = $contractCollection
        ->filter(fn ($contract) => strtolower((string) $contract->status) === 'accepted')
        ->count();

    $finalizedCount = $contractCollection
        ->filter(fn ($contract) => strtolower((string) $contract->status) === 'finalized')
        ->count();

    $visibleTotal = $contractCollection
        ->sum(fn ($contract) => (float) ($contract->total_contract_price ?? 0));
@endphp

<div class="client-contracts-page">
    <section class="contract-stats">
        <article>
            <span class="contract-stat-icon blue"><i class="fas fa-file-signature"></i></span>
            <div>
                <small>Total Contracts</small>
                <strong>{{ $totalContracts }}</strong>
            </div>
        </article>

        <article>
            <span class="contract-stat-icon green"><i class="fas fa-circle-check"></i></span>
            <div>
                <small>Accepted</small>
                <strong>{{ $acceptedCount }}</strong>
            </div>
        </article>

        <article>
            <span class="contract-stat-icon violet"><i class="fas fa-shield-check"></i></span>
            <div>
                <small>Finalized</small>
                <strong>{{ $finalizedCount }}</strong>
            </div>
        </article>

        <article>
            <span class="contract-stat-icon orange"><i class="fas fa-peso-sign"></i></span>
            <div>
                <small>Visible Contract Value</small>
                <strong class="money">PHP {{ number_format($visibleTotal, 2) }}</strong>
            </div>
        </article>
    </section>

    <section class="contract-list-card">
        <div class="contract-list-head">
            <div>
                <span>Service Agreements</span>
                <h3>Contract History</h3>
            </div>

            <small>
                {{ $totalContracts }}
                {{ $totalContracts === 1 ? 'contract' : 'contracts' }}
            </small>
        </div>

        @if ($contracts->count())
            <div class="contract-table-wrap">
                <table class="contract-table">
                    <thead>
                        <tr>
                            <th>Contract</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Contract Date</th>
                            <th>Total Price</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($contracts as $contract)
                            @php
                                $statusKey = strtolower((string) ($contract->status ?? 'generated'));

                                $statusClass = match ($statusKey) {
                                    'accepted' => 'green',
                                    'finalized' => 'violet',
                                    'sent' => 'blue',
                                    'generated', 'draft' => 'gray',
                                    'cancelled', 'canceled' => 'red',
                                    default => 'gray',
                                };

                                $statusLabel = match ($statusKey) {
                                    'cancelled', 'canceled' => 'Cancelled',
                                    default => ucfirst(str_replace('_', ' ', $statusKey)),
                                };
                            @endphp

                            <tr>
                                <td>
                                    <a
                                        href="{{ route('client.contracts.show', $contract) }}"
                                        class="contract-number"
                                    >
                                        {{ $contract->contract_no }}
                                    </a>
                                </td>

                                <td>
                                    <div class="contract-service">
                                        <strong>{{ $contract->quotation->request->service_type ?? 'Service' }}</strong>

                                        @if (!empty($contract->quotation?->request?->project_type))
                                            <span>{{ $contract->quotation->request->project_type }}</span>
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    <span class="contract-status {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <td>
                                    <div class="contract-date">
                                        <strong>{{ optional($contract->contract_date)->format('M d, Y') ?? '—' }}</strong>
                                    </div>
                                </td>

                                <td>
                                    <strong class="contract-total">
                                        PHP {{ number_format((float) $contract->total_contract_price, 2) }}
                                    </strong>
                                </td>

                                <td class="text-end">
                                    <a
                                        href="{{ route('client.contracts.show', $contract) }}"
                                        class="contract-view-btn"
                                    >
                                        <i class="fas fa-eye"></i>
                                        View Contract
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(method_exists($contracts, 'links'))
                <div class="contract-pagination">
                    {{ $contracts->links() }}
                </div>
            @endif
        @else
            <div class="contract-empty">
                <div class="contract-empty-icon">
                    <i class="fas fa-file-signature"></i>
                </div>

                <strong>No contracts available yet</strong>
                <p>Contracts will appear here once an approved quotation is converted into a formal service agreement.</p>
            </div>
        @endif
    </section>
</div>
@endsection