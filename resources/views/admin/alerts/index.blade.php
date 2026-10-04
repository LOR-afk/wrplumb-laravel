@extends('admin.layouts.app')

@section('title', 'Notification Center - WRPlumb')
@section('topbar_title', 'Notification Center')
@section('topbar_subtitle', 'Review recent system updates, service activity, and support notifications.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/alerts.css') }}?v=admin-notifications-01">
@endpush

@section('content')
@php
    $filter = $filter ?? request('filter', 'all');

    $typeMeta = [
        'request' => [
            'label' => 'Requests',
            'icon' => 'fa-clipboard-list',
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
            'label' => 'Payments',
            'icon' => 'fa-money-bill-wave',
            'class' => 'payment',
            'action' => 'View Record',
        ],
        'default' => [
            'label' => 'General',
            'icon' => 'fa-bell',
            'class' => 'default',
            'action' => 'Open',
        ],
    ];

    $categoryItems = [
        'all' => [
            'label' => 'All Notifications',
            'icon' => 'fa-inbox',
            'count' => $stats['total'] ?? 0,
        ],
        'unread' => [
            'label' => 'Unread',
            'icon' => 'fa-envelope',
            'count' => $stats['unread'] ?? 0,
        ],
        'request' => [
            'label' => 'Requests',
            'icon' => 'fa-clipboard-list',
            'count' => $stats['requests'] ?? 0,
        ],
        'support' => [
            'label' => 'Support',
            'icon' => 'fa-headset',
            'count' => $stats['support'] ?? 0,
        ],
        'system' => [
            'label' => 'System',
            'icon' => 'fa-gear',
            'count' => $stats['system'] ?? 0,
        ],
    ];
@endphp

<div class="notification-center-page">
    <aside class="notification-sidebar">
        <section class="notification-stats-card">
            <div class="notification-stat">
                <strong>{{ $stats['unread'] ?? 0 }}</strong>
                <span>Unread</span>
            </div>

            <div class="notification-stat">
                <strong>{{ $stats['total'] ?? 0 }}</strong>
                <span>Total</span>
            </div>

            <div class="notification-stat">
                <strong>{{ $stats['requests'] ?? 0 }}</strong>
                <span>Requests</span>
            </div>

            <div class="notification-stat alert">
                <strong>{{ $stats['support'] ?? 0 }}</strong>
                <span>Support</span>
            </div>
        </section>

        <section class="notification-categories-card">
            <div class="notification-card-title">Categories</div>

            <nav class="notification-category-list">
                @foreach ($categoryItems as $key => $item)
                    <a
                        href="{{ route('admin.alerts.index', ['filter' => $key]) }}"
                        class="{{ $filter === $key ? 'active' : '' }}"
                    >
                        <span>
                            <i class="fas {{ $item['icon'] }}"></i>
                            {{ $item['label'] }}
                        </span>

                        <b>{{ $item['count'] }}</b>
                    </a>
                @endforeach
            </nav>
        </section>
    </aside>

    <section class="notification-main-panel">
        <div class="notification-toolbar">
            <div class="notification-toolbar-left">
                <label class="notification-select-box">
                    <input type="checkbox" id="notificationSelectAll">
                    <span></span>
                </label>

                <div class="notification-search">
                    <i class="fas fa-magnifying-glass"></i>
                    <input
                        type="search"
                        id="notificationSearchInput"
                        placeholder="Search notifications..."
                        autocomplete="off"
                    >
                </div>

                <select id="notificationSortSelect" class="notification-sort-select">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="unread">Unread First</option>
                </select>
            </div>

            <div class="notification-toolbar-actions">
                <form method="POST" action="{{ route('admin.alerts.mark-all-read') }}">
                    @csrf
                    @method('PATCH')

                    <button type="submit" class="notification-action-btn primary">
                        <i class="fas fa-check-double"></i>
                        Mark All Read
                    </button>
                </form>

                <button
                    type="button"
                    class="notification-action-btn danger"
                    data-bs-toggle="modal"
                    data-bs-target="#clearReadAlertsModal"
                >
                    <i class="fas fa-trash"></i>
                    Clear Read
                </button>
            </div>
        </div>

        <div class="notification-section-head">
            <strong>
                @if($filter === 'unread')
                    Unread Notifications
                @elseif($filter === 'request')
                    Request Notifications
                @elseif($filter === 'support')
                    Support Notifications
                @elseif($filter === 'system')
                    System Notifications
                @else
                    Recent Notifications
                @endif
            </strong>

            <span>{{ $alerts->total() }} notification{{ $alerts->total() === 1 ? '' : 's' }}</span>
        </div>

        <div class="notification-list" id="notificationList">
            @forelse ($alerts as $alert)
                @php
                    $alertTitle = strtolower($alert->title ?? '');
                    $alertMessage = strtolower($alert->message ?? '');

                    $detectedType = 'default';

                    if (str_contains($alertTitle, 'request') || str_contains($alertMessage, 'service request')) {
                        $detectedType = 'request';
                    } elseif (str_contains($alertTitle, 'support') || str_contains($alertMessage, 'support')) {
                        $detectedType = 'support';
                    } elseif (
                        str_contains($alertTitle, 'payment') ||
                        str_contains($alertMessage, 'payment') ||
                        str_contains($alertMessage, 'receipt')
                    ) {
                        $detectedType = 'payment';
                    } elseif (
                        str_contains($alertTitle, 'system') ||
                        str_contains($alertTitle, 'archiv') ||
                        str_contains($alertMessage, 'archiv')
                    ) {
                        $detectedType = 'system';
                    }

                    $meta = $typeMeta[$detectedType] ?? $typeMeta['default'];
                @endphp

                <article
                    class="notification-row {{ !$alert->is_read ? 'unread' : 'read' }}"
                    data-created="{{ $alert->created_at->timestamp }}"
                    data-unread="{{ !$alert->is_read ? 1 : 0 }}"
                    data-notification-search="{{ strtolower(
                        ($alert->title ?? '') . ' ' .
                        ($alert->message ?? '') . ' ' .
                        ($meta['label'] ?? '')
                    ) }}"
                >
                    <label class="notification-row-check">
                        <input type="checkbox" class="notification-item-check">
                        <span></span>
                    </label>

                    <div class="notification-type-icon {{ $meta['class'] }}">
                        <i class="fas {{ $meta['icon'] }}"></i>
                    </div>

                    <div class="notification-content">
                        <div class="notification-title-line">
                            <h3>{{ $alert->title }}</h3>

                            <span class="notification-type-badge {{ $meta['class'] }}">
                                {{ $meta['label'] }}
                            </span>

                            @unless($alert->is_read)
                                <span class="notification-unread-dot" title="Unread"></span>
                            @endunless
                        </div>

                        <p>{{ $alert->message }}</p>

                        <div class="notification-meta">
                            <span>
                                <i class="far fa-clock"></i>
                                {{ $alert->created_at->diffForHumans() }}
                            </span>

                            <span>
                                <i class="far fa-calendar"></i>
                                {{ $alert->created_at->format('M d, Y') }}
                            </span>
                        </div>
                    </div>

                    <div class="notification-row-actions">
                        @if ($alert->link)
                            <a href="{{ $alert->link }}" class="notification-open-btn">
                                {{ $meta['action'] }}
                            </a>
                        @endif

                        @unless ($alert->is_read)
                            <form method="POST" action="{{ route('admin.alerts.mark-read', $alert) }}">
                                @csrf
                                @method('PATCH')

                                <button type="submit" class="notification-mark-read-btn" title="Mark as read">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                        @endunless
                    </div>
                </article>
            @empty
                <div class="notification-empty-state">
                    <span><i class="fas fa-bell-slash"></i></span>
                    <strong>No notifications found</strong>
                    <p>You’re all caught up.</p>
                </div>
            @endforelse
        </div>

        @if(method_exists($alerts, 'links'))
            <div class="notification-pagination">
                {{ $alerts->links() }}
            </div>
        @endif
    </section>
</div>

<div
    class="modal fade"
    id="clearReadAlertsModal"
    tabindex="-1"
    aria-labelledby="clearReadAlertsModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content notification-modal">
            <div class="modal-header">
                <div class="notification-modal-head">
                    <span><i class="fas fa-trash"></i></span>

                    <div>
                        <h5 id="clearReadAlertsModalLabel">Clear Read Notifications</h5>
                        <p>This action permanently removes read notifications.</p>
                    </div>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p>
                    Are you sure you want to delete all notifications that have already been marked as read?
                </p>

                <div class="notification-modal-warning">
                    <i class="fas fa-triangle-exclamation"></i>
                    <span>Deleted notifications cannot be recovered.</span>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    Cancel
                </button>

                <form method="POST" action="{{ route('admin.alerts.clear-read') }}">
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>
                        Delete Read Notifications
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/alerts.js') }}?v=admin-notifications-01"></script>
@endpush
