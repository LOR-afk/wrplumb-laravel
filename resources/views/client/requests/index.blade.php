@extends('client.layouts.app')

@section('title', 'My Requests - WRPlumb')
@section('topbar_title', 'My Requests')
@section('topbar_subtitle', 'Track your quotation requests and service progress.')

@section('content')
<style>
    .request-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .request-item {
        border: 1px solid var(--wr-border);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
        padding: 18px 20px;
    }

    .request-top {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr 120px;
        gap: 14px;
        align-items: center;
    }

    .request-title {
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 4px;
    }

    .request-sub {
        color: #6b7280;
        font-size: 0.92rem;
    }

    .meta-label {
        font-size: 0.76rem;
        font-weight: 800;
        text-transform: uppercase;
        color: #6b7280;
        margin-bottom: 4px;
    }

    .meta-value {
        font-weight: 700;
    }

    @media (max-width: 991.98px) {
        .request-top {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .request-top {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="page-header">
    <h1>My Requests</h1>
    <p>Track your requests, assigned personnel/inspector, and current progress status.</p>
</div>

<div class="panel">
    <div class="panel-header">
        <h5><i class="fas fa-file-signature me-2 text-primary"></i>Submitted Requests</h5>
    </div>
    <div class="panel-body">
        @if ($requests->count())
            <div class="request-list">
                @foreach ($requests as $request)
                    @php
                        $hasJobOrder = (bool) ($request->has_job_order ?? $request->jobOrder);
                        $displayStatus = $request->display_status ?? ($hasJobOrder ? $request->jobOrder->status : $request->status);
                        $displayStatusKey = strtolower((string) ($displayStatus ?? 'pending'));
                        $displayStatusLabel = $request->display_status_label ?? ($hasJobOrder ? 'Job Order Status' : 'Request Status');
                        $assignedLabel = ($request->service_flow ?? null) === 'direct_service' ? 'Personnel' : 'Inspector';

                        $displayStatusText = $hasJobOrder
                            ? match ($displayStatusKey) {
                                'scheduled' => 'Job Scheduled',
                                'in_progress' => 'Job In Progress',
                                'completed' => 'Job Completed',
                                'cancelled', 'canceled' => 'Job Cancelled',
                                default => ucfirst(str_replace('_', ' ', $displayStatusKey)),
                            }
                            : match ($displayStatusKey) {
                                'pending' => 'Pending',
                                'assigned' => 'Assigned',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'cancelled', 'canceled' => 'Cancelled',
                                default => ucfirst(str_replace('_', ' ', $displayStatusKey)),
                            };

                        $displayStatusClass = match ($displayStatusKey) {
                            'pending' => 'orange',
                            'approved', 'accepted', 'assigned', 'scheduled', 'rescheduled' => 'blue',
                            'ongoing', 'in_progress', 'in-progress' => 'green',
                            'completed', 'done', 'cancelled', 'canceled', 'rejected', 'declined' => 'gray',
                            default => 'gray',
                        };
                    @endphp

                    <div class="request-item">
                        <div class="request-top">
                            <div>
                                <div class="request-title">{{ $request->service_type }}</div>
                                <div class="request-sub">{{ $request->address }}</div>
                            </div>

                            <div>
                                <div class="meta-label">Preferred Date</div>
                                <div class="meta-value">{{ optional($request->preferred_date)->format('Y-m-d') ?? '—' }}</div>
                            </div>

                            <div>
                                <div class="meta-label">{{ $displayStatusLabel }}</div>
                                <div class="meta-value">
                                    <span class="badge-soft {{ $displayStatusClass }}">{{ $displayStatusText }}</span>
                                </div>
                            </div>

                            <div>
                                <div class="meta-label">Assigned {{ $assignedLabel }}</div>
                                <div class="meta-value">{{ $request->worker?->name ?? 'Not assigned yet' }}</div>
                            </div>

                            <div class="d-grid gap-2">
                                <a href="{{ route('client.requests.show', $request) }}" class="btn btn-primary w-100">Open</a>

                                @if ($request->jobOrder)
                                    <a href="{{ route('client.job-orders.show', $request->jobOrder) }}" class="btn btn-outline-dark w-100">
                                        Job Order
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-3">
                {{ $requests->links() }}
            </div>
        @else
            <div class="text-center py-5 text-muted">
                You do not have any requests yet.
            </div>
        @endif
    </div>
</div>
@endsection
