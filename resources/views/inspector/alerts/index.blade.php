@extends('inspector.layouts.app')

@section('title', 'Alerts - WRPlumb')
@section('topbar_title', 'Alerts')
@section('topbar_subtitle', 'Recent updates about your assigned work.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/inspector/alerts.css') }}?v=inspector-alerts-01">
@endpush

@section('content')
<div class="inspector-alerts-page">
    <section class="alerts-toolbar-card">
        <div>
            <span class="alerts-kicker">Notifications</span>
            <h2>Assigned work updates</h2>
            <p>Keep track of new assignments, appointment changes, and field updates.</p>
        </div>

        <div class="alerts-toolbar-meta">
            <span class="alerts-count">
                <i class="fas fa-bell"></i>
                {{ $alerts->total() }} total
            </span>
        </div>
    </section>

    <section class="alerts-list-card">
        <div class="alerts-list">
            @forelse ($alerts as $alert)
                @php
                    $title = strtolower($alert->title ?? '');

                    $alertType = str_contains($title, 'appointment')
                        ? 'calendar'
                        : (str_contains($title, 'assigned') || str_contains($title, 'request')
                            ? 'request'
                            : (str_contains($title, 'complete')
                                ? 'success'
                                : 'info'));

                    $alertIcon = match ($alertType) {
                        'calendar' => 'fa-calendar-check',
                        'request' => 'fa-clipboard-list',
                        'success' => 'fa-circle-check',
                        default => 'fa-bell',
                    };
                @endphp

                <article
                    class="alert-row {{ !$alert->is_read ? 'is-unread' : '' }}"
                    data-inspector-search="{{ strtolower(
                        ($alert->title ?? '') . ' ' .
                        ($alert->message ?? '')
                    ) }}"
                >
                    <span class="alert-row-icon {{ $alertType }}">
                        <i class="fas {{ $alertIcon }}"></i>
                    </span>

                    <div class="alert-row-content">
                        <div class="alert-row-heading">
                            <h3>{{ $alert->title }}</h3>

                            @if (!$alert->is_read)
                                <span class="alert-new-badge">New</span>
                            @endif
                        </div>

                        <p>{{ $alert->message }}</p>

                        <div class="alert-row-meta">
                            <span>
                                <i class="far fa-clock"></i>
                                {{ $alert->created_at->format('M d, Y · h:i A') }}
                            </span>

                            <span>
                                {{ $alert->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>

                    <div class="alert-row-action">
                        @if ($alert->link)
                            <a
                                href="{{ $alert->link }}"
                                class="alert-open-btn"
                                aria-label="Open {{ $alert->title }}"
                                title="Open alert"
                            >
                                <span>Open</span>
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        @else
                            <span class="alert-no-action">No action needed</span>
                        @endif
                    </div>
                </article>
            @empty
                <div class="alerts-empty-state">
                    <span>
                        <i class="far fa-bell"></i>
                    </span>

                    <h3>You're all caught up</h3>
                    <p>New assignments and schedule updates will appear here.</p>
                </div>
            @endforelse
        </div>

        @if ($alerts->hasPages())
            <div class="alerts-pagination">
                {{ $alerts->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </section>
</div>
@endsection
