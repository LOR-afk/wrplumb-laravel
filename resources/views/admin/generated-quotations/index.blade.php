@extends('admin.layouts.app')

@section('title', 'Generated Quotations - WRPlumb')
@section('topbar_title', 'Generated Quotations')
@section('topbar_subtitle', 'Review quotations prepared by HR and manage editable quotation drafts.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/generated-quotations.css') }}?v=generated-quotations-01">
@endpush

@section('content')
<div class="generated-quotation-page">
    <section class="gq-stats">
        <article><span class="gq-stat-icon blue"><i class="fas fa-file-invoice-dollar"></i></span><div><small>Total</small><strong>{{ $summary['total'] }}</strong></div></article>
        <article><span class="gq-stat-icon gray"><i class="fas fa-pen"></i></span><div><small>Draft</small><strong>{{ $summary['draft'] }}</strong></div></article>
        <article><span class="gq-stat-icon orange"><i class="fas fa-paper-plane"></i></span><div><small>Sent</small><strong>{{ $summary['sent'] }}</strong></div></article>
        <article><span class="gq-stat-icon green"><i class="fas fa-circle-check"></i></span><div><small>Accepted</small><strong>{{ $summary['accepted'] }}</strong></div></article>
    </section>

    <section class="gq-list-card">
        <div class="gq-list-head">
            <div>
                <span>COMMERCIAL RECORDS</span>
                <h3>Quotation History</h3>
            </div>

            <form method="GET" class="gq-filters">
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Quotation no., client, service...">

                <select name="status">
                    <option value="">All Statuses</option>
                    @foreach (['draft', 'sent', 'accepted', 'rejected', 'declined'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>

                <button type="submit"><i class="fas fa-filter"></i>Filter</button>
            </form>
        </div>

        @if ($quotations->count())
            <div class="gq-table-wrap">
                <table class="gq-table">
                    <thead>
                        <tr>
                            <th>Quotation</th>
                            <th>Client / Service</th>
                            <th>Prepared By</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($quotations as $quotation)
                            @php
                                $statusKey = strtolower((string) $quotation->status);
                                $statusClass = match ($statusKey) {
                                    'accepted' => 'green',
                                    'sent' => 'blue',
                                    'draft' => 'gray',
                                    'rejected', 'declined' => 'red',
                                    default => 'orange',
                                };

                                $canEdit = in_array($statusKey, ['draft', 'sent'], true);
                            @endphp

                            <tr>
                                <td>
                                    <a href="{{ route('admin.generated-quotations.show', $quotation) }}" class="gq-number">
                                        {{ $quotation->quotation_no }}
                                    </a>
                                    <small>{{ $quotation->items_count }} item(s)</small>
                                </td>

                                <td>
                                    <strong>{{ $quotation->request?->full_name ?? 'Client' }}</strong>
                                    <span>{{ $quotation->request?->service_type ?? '—' }}</span>
                                </td>

                                <td>
                                    <strong>{{ $quotation->preparedBy?->name ?? 'HR' }}</strong>
                                </td>

                                <td>
                                    <span class="gq-status {{ $statusClass }}">{{ ucfirst($statusKey) }}</span>
                                </td>

                                <td>
                                    <strong>PHP {{ number_format((float) $quotation->grand_total, 2) }}</strong>
                                </td>

                                <td>{{ optional($quotation->created_at)->format('M d, Y') }}</td>

                                <td class="text-end">
                                    <div class="gq-actions">
                                        <a href="{{ route('admin.generated-quotations.show', $quotation) }}" class="gq-btn">
                                            <i class="fas fa-eye"></i>View
                                        </a>

                                        @if ($canEdit)
                                            <a href="{{ route('admin.generated-quotations.edit', $quotation) }}" class="gq-btn primary">
                                                <i class="fas fa-pen"></i>Edit
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="gq-pagination">{{ $quotations->links() }}</div>
        @else
            <div class="gq-empty">
                <span><i class="fas fa-file-invoice-dollar"></i></span>
                <strong>No generated quotations found</strong>
                <p>Quotations prepared by HR will appear here.</p>
            </div>
        @endif
    </section>
</div>
@endsection