@extends('client.layouts.app')

@section('title', 'My Requests - WRPlumb')
@section('topbar_title', 'My Requests')
@section('topbar_subtitle', 'Track your quotation requests and service progress.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/client/requests-index.css') }}?v=20260818a">
@endpush

@section('content')
<div class="client-requests-page">
    <section class="requests-overview">
        <div class="requests-overview-copy">
            <span>Service Tracking</span>
            <h2>Your service requests</h2>
            <p>Review request details, assigned personnel, current status, and available job orders.</p>
        </div>

        <a href="{{ route('client.requests.create') }}" class="requests-overview-action">
            <i class="fas fa-plus"></i>
            Book a Service
        </a>
    </section>

    <section class="requests-panel">
        <div class="requests-panel-head">
            <h3>
                <i class="fas fa-clipboard-list text-primary me-2"></i>
                Submitted Requests
            </h3>

            <span>
                {{ method_exists($requests, 'total') ? $requests->total() : $requests->count() }}
                {{ (method_exists($requests, 'total') ? $requests->total() : $requests->count()) === 1 ? 'request' : 'requests' }}
            </span>
        </div>

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
                            'pending' => 'gray',
                            'approved', 'accepted', 'assigned', 'scheduled', 'rescheduled' => 'blue',
                            'ongoing', 'in_progress', 'in-progress' => 'orange',
                            'completed', 'done' => 'green',
                            'cancelled', 'canceled', 'rejected', 'declined' => 'red',
                            default => 'gray',
                        };
                    @endphp

                    <article class="request-card status-{{ $displayStatusClass }}">
                        <div class="request-main">
                            <div class="request-service">{{ $request->service_type }}</div>
                            <div class="request-address">
                                <i class="fas fa-location-dot"></i>
                                <span>{{ $request->address ?: 'No address provided' }}</span>
                            </div>
                        </div>

                        <div>
                            <span class="request-meta-label">Preferred Date</span>
                            <div class="request-meta-value">
                                {{ optional($request->preferred_date)->format('M d, Y') ?? '—' }}
                            </div>
                        </div>

                        <div>
                            <span class="request-meta-label">{{ $displayStatusLabel }}</span>
                            <span class="request-status {{ $displayStatusClass }}">
                                {{ $displayStatusText }}
                            </span>
                        </div>

                        <div>
                            <span class="request-meta-label">Assigned {{ $assignedLabel }}</span>

                            <div class="request-assignee">
                                <span class="request-assignee-icon">
                                    <i class="fas fa-user"></i>
                                </span>

                                <span class="request-assignee-copy">
                                    <strong>{{ $request->worker?->name ?? 'Not assigned yet' }}</strong>
                                    <small>{{ $assignedLabel }}</small>
                                </span>
                            </div>
                        </div>

                        <div class="request-actions">
                            <a href="{{ route('client.requests.show', $request) }}" class="request-action primary">
                                <i class="fas fa-eye"></i>
                                View Details
                            </a>

                            @if ($request->jobOrder)
                                <a href="{{ route('client.job-orders.show', $request->jobOrder) }}" class="request-action secondary">
                                    <i class="fas fa-clipboard-check"></i>
                                    Job Order
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="request-pagination">
                {{ $requests->links() }}
            </div>
        @else
            <div class="requests-empty">
                <div class="requests-empty-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <strong>No service requests yet</strong>
                <p>Book a service to create your first request.</p>
            </div>
        @endif
    </section>
</div>
@endsection