@extends('hr.layouts.app')

@section('title', 'HR Alerts - WRPlumb')
@section('topbar_title', 'HR Alerts')
@section('topbar_subtitle', 'Track quotation, billing, payment, contract, and support updates.')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/hr/alerts.css') }}?v=hr-alerts-01">
@endpush

@section('content')
@php
    $filter = $filter ?? request('filter', 'all');

    $filterTabs = [
        'all' => ['label' => 'All', 'count' => $stats['total'] ?? 0],
        'unread' => ['label' => 'Unread', 'count' => $stats['unread'] ?? 0],
        'quotation' => ['label' => 'Quotations', 'count' => $stats['quotation'] ?? 0],
        'payment' => ['label' => 'Payments', 'count' => $stats['payment'] ?? 0],
        'contract' => ['label' => 'Contracts', 'count' => $stats['contract'] ?? 0],
        'billing' => ['label' => 'Billing', 'count' => $stats['billing'] ?? 0],
        'support' => ['label' => 'Support', 'count' => $stats['support'] ?? 0],
    ];

    $typeMeta = [
        'quotation' => [
            'label' => 'Quotation',
            'icon' => 'fa-file-invoice-dollar',
            'class' => 'quotation',
        ],
        'payment' => [
            'label' => 'Payment',
            'icon' => 'fa-credit-card',
            'class' => 'payment',
        ],
        'contract' => [
            'label' => 'Contract',
            'icon' => 'fa-file-contract',
            'class' => 'contract',
        ],
        'billing' => [
            'label' => 'Billing',
            'icon' => 'fa-calendar-check',
            'class' => 'billing',
        ],
        'support' => [
            'label' => 'Support',
            'icon' => 'fa-headset',
            'class' => 'support',
        ],
        'default' => [
            'label' => 'Alert',
            'icon' => 'fa-bell',
            'class' => 'default',
        ],
    ];
@endphp

<div class="hr-alerts-page">
    <section class="hr-alerts-summary">
        <article class="hr-alert-summary-card primary">
            <span class="hr-alert-summary-icon">
                <i class="fas fa-bell"></i>
            </span>
            <div>
                <small>Unread Alerts</small>
                <strong>{{ $stats['unread'] ?? 0 }}</strong>
                <p>Needs your attention</p>
            </div>
        </article>

        <article class="hr-alert-summary-card quotation">
            <span class="hr-alert-summary-icon">
                <i class="fas fa-file-invoice-dollar"></i>
            </span>
            <div>
                <small>Quotations</small>
                <strong>{{ $stats['quotation'] ?? 0 }}</strong>
                <p>Quotation workflow updates</p>
            </div>
        </article>

        <article class="hr-alert-summary-card payment">
            <span class="hr-alert-summary-icon">
                <i class="fas fa-credit-card"></i>
            </span>
            <div>
                <small>Payments</small>
                <strong>{{ $stats['payment'] ?? 0 }}</strong>
                <p>Payment and receipt activity</p>
            </div>
        </article>

        <article class="hr-alert-summary-card billing">
            <span class="hr-alert-summary-icon">
                <i class="fas fa-calendar-check"></i>
            </span>
            <div>
                <small>Billing</small>
                <strong>{{ $stats['billing'] ?? 0 }}</strong>
                <p>Invoice and due-date notices</p>
            </div>
        </article>
    </section>

    <section class="hr-alert-toolbar">
        <div class="hr-alert-tabs">
            @foreach ($filterTabs as $key => $tab)
                <a href="{{ route('hr.alerts.index', ['filter' => $key]) }}"
                   class="hr-alert-tab {{ $filter === $key ? 'active' : '' }}">
                    <span>{{ $tab['label'] }}</span>
                    <em>{{ $tab['count'] }}</em>
                </a>
            @endforeach
        </div>

        <div class="hr-alert-bulk-actions">
            <form method="POST" action="{{ route('hr.alerts.mark-all-read') }}">
                @csrf
                @method('PATCH')

                <button type="submit" class="btn btn-outline-primary">
                    <i class="fas fa-check-double me-1"></i>
                    Mark all read
                </button>
            </form>

            <button type="button"
                    class="btn btn-outline-danger"
                    data-bs-toggle="modal"
                    data-bs-target="#clearHrReadAlertsModal">
                <i class="fas fa-trash me-1"></i>
                Clear read
            </button>
        </div>
    </section>

    <section class="hr-alert-list-panel">
        <header class="hr-alert-list-header">
            <div>
                <h2>
                    <i class="fas fa-list-check"></i>
                    Notifications
                </h2>
                <p>
                    Showing {{ $alerts->count() }}
                    of {{ $alerts->total() }} alert(s).
                </p>
            </div>

            <span>
                <i class="fas fa-arrow-down-wide-short"></i>
                Newest first
            </span>
        </header>

        <div class="hr-alert-list">
            @forelse ($alerts as $alert)
                @php
                    $title = strtolower($alert->title ?? '');
                    $message = strtolower($alert->message ?? '');
                    $detectedType = 'default';

                    if (
                        str_contains($title, 'quotation') ||
                        str_contains($message, 'quotation') ||
                        str_contains($message, 'ready for quotation')
                    ) {
                        $detectedType = 'quotation';
                    } elseif (
                        str_contains($title, 'payment') ||
                        str_contains($message, 'payment') ||
                        str_contains($message, 'receipt')
                    ) {
                        $detectedType = 'payment';
                    } elseif (
                        str_contains($title, 'contract') ||
                        str_contains($message, 'contract')
                    ) {
                        $detectedType = 'contract';
                    } elseif (
                        str_contains($title, 'invoice') ||
                        str_contains($title, 'billing') ||
                        str_contains($title, 'due') ||
                        str_contains($message, 'invoice') ||
                        str_contains($message, 'billing') ||
                        str_contains($message, 'due date') ||
                        str_contains($message, 'overdue')
                    ) {
                        $detectedType = 'billing';
                    } elseif (
                        str_contains($title, 'support') ||
                        str_contains($message, 'support') ||
                        str_contains($message, 'escalat')
                    ) {
                        $detectedType = 'support';
                    }

                    $meta = $typeMeta[$detectedType] ?? $typeMeta['default'];
                @endphp

                <article class="hr-alert-card {{ $meta['class'] }} {{ $alert->is_read ? 'read' : 'unread' }}">
                    <span class="hr-alert-status-dot"></span>

                    <div class="hr-alert-icon">
                        <i class="fas {{ $meta['icon'] }}"></i>
                    </div>

                    <div class="hr-alert-content">
                        <div class="hr-alert-title-row">
                            <h3>{{ $alert->title }}</h3>

                            <span class="hr-alert-read-badge {{ $alert->is_read ? 'read' : 'unread' }}">
                                {{ $alert->is_read ? 'Read' : 'Unread' }}
                            </span>
                        </div>

                        <p>{{ $alert->message }}</p>

                        <div class="hr-alert-meta">
                            <span>
                                <i class="fas fa-calendar-day"></i>
                                {{ $alert->created_at->format('M d, Y') }}
                            </span>

                            <span>
                                <i class="fas fa-clock"></i>
                                {{ $alert->created_at->format('h:i A') }}
                            </span>

                            <span class="hr-alert-type {{ $meta['class'] }}">
                                {{ $meta['label'] }}
                            </span>
                        </div>
                    </div>

                    <div class="hr-alert-actions">
                        @if ($alert->link)
                            @if (!$alert->is_read)
                                <form method="POST"
                                      action="{{ route('hr.alerts.mark-read', $alert) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-arrow-up-right-from-square me-1"></i>
                                        Open
                                    </button>
                                </form>
                            @else
                                <a href="{{ $alert->link }}" class="btn btn-primary">
                                    <i class="fas fa-arrow-up-right-from-square me-1"></i>
                                    Open
                                </a>
                            @endif
                        @endif

                        @if (!$alert->is_read && !$alert->link)
                            <form method="POST"
                                  action="{{ route('hr.alerts.mark-read', $alert) }}">
                                @csrf
                                @method('PATCH')

                                <button type="submit" class="btn btn-outline-secondary">
                                    <i class="fas fa-check me-1"></i>
                                    Mark read
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <div class="hr-alert-empty-state">
                    <span><i class="fas fa-bell-slash"></i></span>
                    <strong>No alerts found</strong>
                    <p>New HR notifications will appear here.</p>
                </div>
            @endforelse
        </div>

        @if ($alerts->hasPages())
            <div class="hr-alert-pagination">
                {{ $alerts->links() }}
            </div>
        @endif
    </section>
</div>

<div class="modal fade"
     id="clearHrReadAlertsModal"
     tabindex="-1"
     aria-labelledby="clearHrReadAlertsModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0">
                <div>
                    <h5 class="modal-title" id="clearHrReadAlertsModalLabel">
                        Clear Read Alerts
                    </h5>
                    <p class="text-muted mb-0 small">
                        This action cannot be undone.
                    </p>
                </div>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>
            </div>

            <div class="modal-body">
                Permanently delete all alerts already marked as read?
            </div>

            <div class="modal-footer border-0">
                <button type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal">
                    Cancel
                </button>

                <form method="POST" action="{{ route('hr.alerts.clear-read') }}">
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>
                        Delete read alerts
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection