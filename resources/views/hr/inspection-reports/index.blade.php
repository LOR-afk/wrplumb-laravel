@extends('hr.layouts.app')

@section('title', 'Inspection Reports')
@section('topbar_title', 'Inspection Reports')
@section('topbar_subtitle', 'Review submitted field inspection reports and prepare quotations.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/hr/inspection-reports.css') }}?v=inspection-reports-01">
@endpush

@section('content')
<div class="hr-inspection-page">
    <section class="inspection-page-hero">
        <div>
            <span>FIELD INSPECTION</span>
            <h2>Submitted Inspection Reports</h2>
            <p>Review inspector findings, preliminary estimates, photos, and quotation readiness.</p>
        </div>
    </section>

    <section class="inspection-stat-grid">
        <article>
            <span class="inspection-stat-icon blue"><i class="fas fa-clipboard-list"></i></span>
            <div><small>Total Submitted</small><strong>{{ $summary['total'] }}</strong></div>
        </article>

        <article>
            <span class="inspection-stat-icon orange"><i class="fas fa-clock"></i></span>
            <div><small>Pending Review</small><strong>{{ $summary['pending_review'] }}</strong></div>
        </article>

        <article>
            <span class="inspection-stat-icon green"><i class="fas fa-circle-check"></i></span>
            <div><small>Reviewed</small><strong>{{ $summary['reviewed'] }}</strong></div>
        </article>

        <article>
            <span class="inspection-stat-icon violet"><i class="fas fa-file-invoice-dollar"></i></span>
            <div><small>Ready for Quotation</small><strong>{{ $summary['ready_for_quotation'] }}</strong></div>
        </article>
    </section>

    <section class="inspection-list-card">
        <div class="inspection-list-head">
            <div>
                <span>REPORT QUEUE</span>
                <h3>Inspection Report History</h3>
            </div>

            <form method="GET" class="inspection-filters">
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Report no., client, service, inspector..."
                >

                <select name="review">
                    <option value="">All Reports</option>
                    <option value="pending" @selected(request('review') === 'pending')>Pending Review</option>
                    <option value="reviewed" @selected(request('review') === 'reviewed')>Reviewed</option>
                </select>

                <button type="submit">
                    <i class="fas fa-filter"></i>
                    Filter
                </button>
            </form>
        </div>

        @if ($reports->count())
            <div class="inspection-table-wrap">
                <table class="inspection-table">
                    <thead>
                        <tr>
                            <th>Report</th>
                            <th>Client / Service</th>
                            <th>Inspector</th>
                            <th>Estimate</th>
                            <th>Review</th>
                            <th>Quotation</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($reports as $report)
                            @php
                                $requestRecord = $report->quotationRequest;
                                $quotation = $requestRecord?->quotation;

                                $inspectorName = $report->inspector->name
                                    ?? trim(($report->inspector->first_name ?? '') . ' ' . ($report->inspector->last_name ?? ''))
                                    ?: 'Inspector';
                            @endphp

                            <tr>
                                <td>
                                    <a href="{{ route('hr.inspection-reports.show', $report) }}" class="inspection-report-number">
                                        {{ $report->report_no }}
                                    </a>
                                    <small>{{ optional($report->submitted_at)->format('M d, Y h:i A') }}</small>
                                </td>

                                <td>
                                    <strong>{{ $requestRecord?->full_name ?? 'Client' }}</strong>
                                    <span>{{ $requestRecord?->service_type ?? '—' }}</span>
                                </td>

                                <td>
                                    <strong>{{ $inspectorName }}</strong>
                                </td>

                                <td>
                                    <strong>PHP {{ number_format((float) $report->estimated_total_cost, 2) }}</strong>
                                </td>

                                <td>
                                    @if ($report->reviewed_at)
                                        <span class="inspection-status green">Reviewed</span>
                                    @else
                                        <span class="inspection-status orange">Pending</span>
                                    @endif
                                </td>

                                <td>
                                    @if ($quotation)
                                        <span class="inspection-status blue">
                                            {{ ucfirst(str_replace('_', ' ', $quotation->status)) }}
                                        </span>
                                    @else
                                        <span class="inspection-status gray">Not Created</span>
                                    @endif
                                </td>

                                <td class="text-end">
                                    <a href="{{ route('hr.inspection-reports.show', $report) }}" class="inspection-action-btn">
                                        <i class="fas fa-eye"></i>
                                        View Report
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="inspection-pagination">
                {{ $reports->links() }}
            </div>
        @else
            <div class="inspection-empty">
                <span><i class="fas fa-clipboard-check"></i></span>
                <strong>No submitted inspection reports</strong>
                <p>Reports submitted by inspectors will appear here.</p>
            </div>
        @endif
    </section>
</div>
@endsection