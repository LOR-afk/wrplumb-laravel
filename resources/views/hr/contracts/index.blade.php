@extends('hr.layouts.app')

@section('title', 'Contracts')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/hr/contracts-index.css') }}?v=contracts-index-01">
@endpush

@section('content')
<div class="contracts-index-page">
    <section class="contracts-header">
        <div>
            <span class="contracts-eyebrow">Contract Management</span>
            <h1>Contracts</h1>
            <p>Manage generated service contracts and review contract status.</p>
        </div>

        <div class="contracts-header-meta">
            <span class="contracts-count">
                {{ method_exists($contracts, 'total') ? number_format($contracts->total()) : number_format($contracts->count()) }}
                {{ \Illuminate\Support\Str::plural('contract', method_exists($contracts, 'total') ? $contracts->total() : $contracts->count()) }}
            </span>
        </div>
    </section>

    @if (session('success'))
        <div class="alert alert-success rounded-3 mb-0">{{ session('success') }}</div>
    @endif

    @if (session('info'))
        <div class="alert alert-info rounded-3 mb-0">{{ session('info') }}</div>
    @endif

    <section class="contracts-panel">
        <header class="contracts-panel-header">
            <div>
                <h2>
                    <i class="fas fa-file-contract"></i>
                    Contract Records
                </h2>
                <p>View contract revisions, client details, service scope, and current status.</p>
            </div>
        </header>

        @if ($contracts->count())
            <div class="contracts-table-wrap">
                <table class="contracts-table">
                    <thead>
                        <tr>
                            <th>Contract</th>
                            <th>Client</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Contract Date</th>
                            <th>Total Price</th>
                            <th class="contracts-action-head">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($contracts as $contract)
                            @php
                                $statusKey = strtolower((string) $contract->status);
                            @endphp

                            <tr>
                                <td>
                                    <div class="contract-number-cell">
                                        <strong>{{ $contract->contract_no }}</strong>
                                        <span>Revision {{ $contract->revision_no ?? 1 }}</span>
                                    </div>
                                </td>

                                <td>
                                    <div class="contract-client-cell">
                                        <strong>
                                            {{ $contract->client_name ?: ($contract->quotation->request->full_name ?? '—') }}
                                        </strong>
                                    </div>
                                </td>

                                <td>
                                    <div class="contract-service-cell">
                                        <strong>{{ $contract->quotation->request->service_type ?? '—' }}</strong>
                                    </div>
                                </td>

                                <td>
                                    <span class="contract-status status-{{ $statusKey }}">
                                        <i class="fas fa-circle"></i>
                                        {{ ucwords(str_replace('_', ' ', $contract->status)) }}
                                    </span>
                                </td>

                                <td>
                                    <div class="contract-date-cell">
                                        <strong>{{ optional($contract->contract_date)->format('M d, Y') ?? '—' }}</strong>
                                    </div>
                                </td>

                                <td>
                                    <div class="contract-price-cell">
                                        <strong>PHP {{ number_format((float) $contract->total_contract_price, 2) }}</strong>
                                    </div>
                                </td>

                                <td class="contracts-action-cell">
                                    <a
                                        href="{{ route('hr.contracts.show', $contract) }}"
                                        class="contract-view-btn"
                                    >
                                        <i class="fas fa-eye"></i>
                                        <span>View</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(method_exists($contracts, 'links'))
                <div class="contracts-pagination">
                    <div class="contracts-pagination-copy">
                        @if(method_exists($contracts, 'firstItem') && $contracts->firstItem())
                            Showing {{ $contracts->firstItem() }} to {{ $contracts->lastItem() }} of {{ $contracts->total() }}
                        @endif
                    </div>

                    <div>
                        {{ $contracts->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        @else
            <div class="contracts-empty">
                <span>
                    <i class="fas fa-file-contract"></i>
                </span>
                <h3>No contracts found</h3>
                <p>Generated service contracts will appear here.</p>
            </div>
        @endif
    </section>
</div>
@endsection
