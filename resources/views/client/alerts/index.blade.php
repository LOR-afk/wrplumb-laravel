@extends('client.layouts.app')

@section('title', 'Notifications - WRPlumb')
@section('topbar_title', 'Notifications')
@section('topbar_subtitle', 'View your payment reminders and account updates.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/client/alerts.css') }}">
@endpush

@section('content')
<div class="client-alerts-page">
    <div class="alerts-page-header">
        <div>
            <span class="alerts-eyebrow">Client updates</span>
            <h1>Notifications</h1>
            <p>
                Review your payment reminders, service updates, and account activity.
            </p>
        </div>

        @if ($alerts->where('is_read', false)->count() > 0)
            <form
                method="POST"
                action="{{ route('client.alerts.mark-all-read') }}"
            >
                @csrf
                @method('PATCH')

                <button type="submit" class="mark-all-button">
                    <i class="fas fa-check-double"></i>
                    Mark all as read
                </button>
            </form>
        @endif
    </div>

    <div class="alerts-card">
        @forelse ($alerts as $alert)
            @php
                $alertIcon = match ($alert->type) {
                    'payment_due' => 'fa-file-invoice-dollar',
                    'payment' => 'fa-circle-check',
                    'quotation' => 'fa-file-lines',
                    'contract' => 'fa-file-signature',
                    'job_order' => 'fa-clipboard-check',
                    'request' => 'fa-screwdriver-wrench',
                    'support' => 'fa-headset',
                    default => 'fa-bell',
                };
            @endphp

            <form
                method="POST"
                action="{{ route('client.alerts.mark-read', $alert) }}"
                class="alert-row-form"
            >
                @csrf
                @method('PATCH')

                <button
                    type="submit"
                    class="alert-row {{ $alert->is_read ? '' : 'unread' }}"
                >
                    <span class="alert-row-icon alert-type-{{ $alert->type }}">
                        <i class="fas {{ $alertIcon }}"></i>
                    </span>

                    <span class="alert-row-content">
                        <span class="alert-row-top">
                            <strong>{{ $alert->title }}</strong>

                            <time>
                                {{ $alert->created_at->format('M d, Y h:i A') }}
                            </time>
                        </span>

                        <span class="alert-row-message">
                            {{ $alert->message }}
                        </span>

                        <span class="alert-row-footer">
                            {{ $alert->created_at->diffForHumans() }}

                            @unless ($alert->is_read)
                                <span class="unread-label">Unread</span>
                            @endunless
                        </span>
                    </span>

                    <span class="alert-row-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                </button>
            </form>
        @empty
            <div class="alerts-empty-state">
                <span>
                    <i class="fas fa-bell-slash"></i>
                </span>

                <h2>No notifications yet</h2>

                <p>
                    Payment reminders and service updates will appear here.
                </p>
            </div>
        @endforelse
    </div>

    @if ($alerts->hasPages())
        <div class="alerts-pagination">
            {{ $alerts->links() }}
        </div>
    @endif
</div>
@endsection