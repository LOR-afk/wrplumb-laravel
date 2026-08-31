@extends('admin.layouts.app')

@section('title', 'Admin Alerts - WRPlumb')
@section('topbar_title', 'Admin Alerts')
@section('topbar_subtitle', 'Recent system updates and assigned notifications.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/alerts.css') }}">
@endpush

@section('content')
@php
    $filter = $filter ?? request('filter', 'all');

    $typeMeta = [
        'request' => [
            'label' => 'Request',
            'icon' => 'fa-file-circle-plus',
            'class' => 'request',
            'action' => 'Open Request',
        ],
        'support' => [
            'label' => 'Support',
            'icon' => 'fa-headset',
            'class' => 'support',
            'action' => 'View Ticket',
        ],
        'system' => [
            'label' => 'System',
            'icon' => 'fa-gear',
            'class' => 'system',
            'action' => 'View Details',
        ],
        'payment' => [
            'label' => 'Payment',
            'icon' => 'fa-money-bill-wave',
            'class' => 'payment',
            'action' => 'View Record',
        ],
        'default' => [
            'label' => 'Alert',
            'icon' => 'fa-bell',
            'class' => 'default',
            'action' => 'Open',
        ],
    ];

    $filterTabs = [
        'all' => ['label' => 'All', 'count' => $stats['total'] ?? 0],
        'unread' => ['label' => 'Unread', 'count' => $stats['unread'] ?? 0],
        'request' => ['label' => 'Requests', 'count' => $stats['requests'] ?? 0],
        'support' => ['label' => 'Support', 'count' => $stats['support'] ?? 0],
        'system' => ['label' => 'System', 'count' => $stats['system'] ?? 0],
    ];
@endphp

<div class="alerts-page">
    <section class="alerts-summary-grid">
        <article class="alerts-summary-card blue">
            <div class="alerts-summary-icon"><i class="fas fa-bell"></i></div>
            <div>
                <span>Unread Alerts</span>
                <strong>{{ $stats['unread'] ?? 0 }}</strong>
                <p>Needs your attention</p>
            </div>
        </article>

        <article class="alerts-summary-card indigo">
            <div class="alerts-summary-icon"><i class="fas fa-clipboard-list"></i></div>
            <div>
                <span>Service Requests</span>
                <strong>{{ $stats['requests'] ?? 0 }}</strong>
                <p>Request notifications</p>
            </div>
        </article>

        <article class="alerts-summary-card orange">
            <div class="alerts-summary-icon"><i class="fas fa-headset"></i></div>
            <div>
                <span>Support Escalations</span>
                <strong>{{ $stats['support'] ?? 0 }}</strong>
                <p>Support updates</p>
            </div>
        </article>

        <article class="alerts-summary-card green">
            <div class="alerts-summary-icon"><i class="fas fa-gear"></i></div>
            <div>
                <span>System Actions</span>
                <strong>{{ $stats['system'] ?? 0 }}</strong>
                <p>Recent system updates</p>
            </div>
        </article>
    </section>

    <section class="alerts-filter-card">
        <div class="alerts-tabs">
            @foreach ($filterTabs as $key => $tab)
                <a href="{{ route('admin.alerts.index', ['filter' => $key]) }}"
                   class="alerts-tab {{ $filter === $key ? 'active' : '' }}">
                    <span>{{ $tab['label'] }}</span>
                    <em>{{ $tab['count'] }}</em>
                </a>
            @endforeach
        </div>

        <div class="alerts-bulk-actions">
            <form method="POST" action="{{ route('admin.alerts.mark-all-read') }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-outline-primary">
                    <i class="fas fa-check me-1"></i> Mark all as read
                </button>
            </form>

<button
    type="button"
    class="btn btn-outline-secondary"
    data-bs-toggle="modal"
    data-bs-target="#clearReadAlertsModal">

    <i class="fas fa-trash me-1"></i>
    Clear Read

</button>
        </div>
    </section>

    <section class="alerts-list-panel">
        <div class="alerts-list-head">
            <div>
                <h5><i class="fas fa-list-check me-2 text-primary"></i>Alerts</h5>
                <p>Showing {{ $alerts->count() }} of {{ $alerts->total() }} alert(s).</p>
            </div>

            <span class="alerts-sort-chip">
                <i class="fas fa-arrow-down-wide-short me-1"></i> Newest first
            </span>
        </div>

        @forelse ($alerts as $alert)
            @php
                $alertTitle = strtolower($alert->title ?? '');
                $alertMessage = strtolower($alert->message ?? '');

                $detectedType = 'default';

                if (str_contains($alertTitle, 'request') || str_contains($alertMessage, 'service request')) {
                    $detectedType = 'request';
                } elseif (str_contains($alertTitle, 'support') || str_contains($alertMessage, 'support')) {
                    $detectedType = 'support';
                } elseif (str_contains($alertTitle, 'payment') || str_contains($alertMessage, 'payment') || str_contains($alertMessage, 'receipt')) {
                    $detectedType = 'payment';
                } elseif (str_contains($alertTitle, 'system') || str_contains($alertTitle, 'archiv') || str_contains($alertMessage, 'archiv')) {
                    $detectedType = 'system';
                }

                $meta = $typeMeta[$detectedType] ?? $typeMeta['default'];
            @endphp

            <article class="alert-card {{ $meta['class'] }} {{ !$alert->is_read ? 'unread' : 'read' }}">
                <div class="alert-status-dot"></div>

                <div class="alert-icon">
                    <i class="fas {{ $meta['icon'] }}"></i>
                </div>

                <div class="alert-content">
                    <div class="alert-title-row">
                        <h5>{{ $alert->title }}</h5>
                        <span class="alert-read-badge {{ !$alert->is_read ? 'unread' : 'read' }}">
                            {{ !$alert->is_read ? 'Unread' : 'Read' }}
                        </span>
                    </div>

                    <p>{{ $alert->message }}</p>

                    <div class="alert-meta-row">
                        <span><i class="fas fa-calendar-days me-1"></i>{{ $alert->created_at->format('M d, Y') }}</span>
                        <span><i class="fas fa-clock me-1"></i>{{ $alert->created_at->format('h:i A') }}</span>
                        <span class="alert-type-chip {{ $meta['class'] }}">{{ $meta['label'] }}</span>
                    </div>
                </div>

                <div class="alert-actions">
                    @if ($alert->link)
                        <a href="{{ $alert->link }}" class="btn btn-primary">
                            <i class="fas fa-eye me-1"></i>{{ $meta['action'] }}
                        </a>
                    @endif

                    @unless ($alert->is_read)
                        <form method="POST" action="{{ route('admin.alerts.mark-read', $alert) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-outline-secondary">
                                <i class="fas fa-check me-1"></i> Mark read
                            </button>
                        </form>
                    @endunless
                </div>
            </article>
        @empty
            <div class="alerts-empty-state">
                <div class="alerts-empty-icon"><i class="fas fa-bell-slash"></i></div>
                <strong>No alerts right now</strong>
                <p>You’re all caught up. New alerts and notifications will appear here.</p>
            </div>
        @endforelse

        @if(method_exists($alerts, 'links'))
            <div class="alerts-pagination">
                {{ $alerts->links() }}
            </div>
        @endif
    </section>
</div>
<!-- Clear Read Notifications Modal -->
<div class="modal fade"
     id="clearReadAlertsModal"
     tabindex="-1"
     aria-labelledby="clearReadAlertsModalLabel"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header border-0">

                <div class="d-flex align-items-center">

                    <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center me-3"
                         style="width:60px;height:60px;">

                        <i class="fas fa-trash text-danger fa-lg"></i>

                    </div>

                    <div>

                        <h5 class="modal-title mb-1"
                            id="clearReadAlertsModalLabel">

                            Clear Read Notifications

                        </h5>

                        <small class="text-muted">
                            This action is permanent.
                        </small>

                    </div>

                </div>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

                <p class="mb-3">

                    Are you sure you want to permanently delete
                    all <strong>read notifications</strong>?

                </p>

                <div class="alert alert-warning d-flex">

                    <i class="fas fa-triangle-exclamation me-2 mt-1"></i>

                    <div>

                        Once deleted, these notifications
                        cannot be recovered.

                    </div>

                </div>

            </div>

            <div class="modal-footer border-0">

                <button
                    type="button"
                    class="btn btn-light"
                    data-bs-dismiss="modal">

                    Cancel

                </button>

                <form method="POST"
                      action="{{ route('admin.alerts.clear-read') }}">

                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="btn btn-danger">

                        <i class="fas fa-trash me-1"></i>

                        Delete Notifications

                    </button>

                </form>

            </div>

        </div>

    </div>

</div>
@endsection
