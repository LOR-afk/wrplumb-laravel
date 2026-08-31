@extends('admin.layouts.app')

@section('title', 'Support Conversation - WRPlumb')
@section('topbar_title', 'Support Conversation')
@section('topbar_subtitle', 'Review the concern and respond as Admin.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/support.css') }}">
@endpush

@section('content')
@php
    $clientName = $conversation->client?->name
        ?: trim(($conversation->client?->first_name ?? '') . ' ' . ($conversation->client?->last_name ?? ''))
        ?: 'Unknown Client';

    $initials = collect(explode(' ', $clientName))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('') ?: 'C';

    $ticketNo = 'SR-' . optional($conversation->created_at)->format('Ymd') . '-' . str_pad((string) $conversation->id, 4, '0', STR_PAD_LEFT);

    $statusClass = match ($conversation->status) {
        'open' => 'open',
        'routed' => 'waiting',
        'resolved' => 'resolved',
        default => 'default',
    };
@endphp

<div class="support-command-page">
    <section class="support-show-layout">
        <main class="support-conversation-panel standalone">
            <div class="support-conversation-head">
                <div>
                    <a href="{{ route('admin.support.index', ['conversation' => $conversation->id]) }}" class="support-back-link">
                        <i class="fas fa-arrow-left me-1"></i> Back to Support Queue
                    </a>
                    <div class="support-ticket-id">Ticket #{{ $ticketNo }}</div>
                    <h4>{{ $clientName }}</h4>
                    <p>Admin-level support conversation and escalation handling.</p>
                </div>

                <div class="support-head-badges">
                    <span class="support-status {{ $statusClass }}">{{ ucfirst($conversation->status ?? 'open') }}</span>
                    <span class="support-status {{ $conversation->current_queue === 'admin' ? 'open' : 'resolved' }}">
                        {{ ucfirst($conversation->current_queue ?? 'admin') }} Queue
                    </span>
                </div>
            </div>

            <div class="support-thread show-thread" id="adminSupportChatBody">
                @foreach ($conversation->messages as $message)
                    @php
                        $sender = $message->sender_type;
                        $senderLabel = match ($sender) {
                            'admin' => 'Admin',
                            'client' => $clientName,
                            'hr' => 'HR Team',
                            'system' => 'System',
                            default => 'Support Bot',
                        };
                        $bubbleClass = match ($sender) {
                            'admin' => 'admin',
                            'client' => 'client',
                            'hr' => 'hr',
                            'system' => 'system',
                            default => 'bot',
                        };
                    @endphp

                    @if ($sender === 'system')
                        <div class="support-system-note">
                            <span>{{ $message->message }}</span>
                            <small>{{ $message->created_at->format('M d, h:i A') }}</small>
                        </div>
                    @else
                        <div class="support-message-row {{ $sender === 'admin' ? 'right' : 'left' }}">
                            <div class="support-message-avatar {{ $bubbleClass }}">
                                {{ strtoupper(substr($senderLabel, 0, 1)) }}
                            </div>
                            <div class="support-message-wrap">
                                <div class="support-sender-label">{{ $senderLabel }}</div>
                                <div class="support-bubble {{ $bubbleClass }}">{{ $message->message }}</div>
                                <small>{{ $message->created_at->format('M d, h:i A') }}</small>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            @if ($conversation->current_queue === 'admin' && $conversation->status !== 'resolved')
                <form method="POST" action="{{ route('admin.support.reply', $conversation) }}" class="support-reply-box">
                    @csrf
                    <div class="support-reply-tabs">
                        <span class="active">Reply to Client</span>
                    </div>

                    <div class="support-reply-control">
                        <textarea name="message" rows="3" placeholder="Type your reply here..." required></textarea>
                        <button class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i> Send Reply
                        </button>
                    </div>
                </form>
            @endif
        </main>

        <aside class="support-action-panel">
            <div class="support-details-card">
                <div class="support-selected-client">
                    <div class="support-ticket-avatar lg">{{ $initials }}</div>
                    <div>
                        <h5>{{ $clientName }}</h5>
                        <p>{{ $conversation->client?->email ?? 'No email listed' }}</p>
                    </div>
                </div>

                <div class="support-detail-row">
                    <span>Ticket ID</span>
                    <strong>{{ $ticketNo }}</strong>
                </div>
                <div class="support-detail-row">
                    <span>Username</span>
                    <strong>{{ $conversation->client?->username ?? '—' }}</strong>
                </div>
                <div class="support-detail-row">
                    <span>Created</span>
                    <strong>{{ $conversation->created_at->format('M d, Y h:i A') }}</strong>
                </div>
                <div class="support-detail-row">
                    <span>Routed At</span>
                    <strong>{{ optional($conversation->routed_at)->format('M d, Y h:i A') ?? '—' }}</strong>
                </div>
                <div class="support-detail-row">
                    <span>Resolved At</span>
                    <strong>{{ optional($conversation->resolved_at)->format('M d, Y h:i A') ?? '—' }}</strong>
                </div>
            </div>

            <div class="support-actions-card">
                <h5>Conversation Actions</h5>

                @if ($conversation->status !== 'resolved')
                    <form method="POST" action="{{ route('admin.support.resolve', $conversation) }}">
                        @csrf
                        <button class="btn btn-outline-success w-100">
                            <i class="fas fa-circle-check me-1"></i> Mark as Resolved
                        </button>
                    </form>
                @else
                    <div class="support-resolved-note compact">
                        <i class="fas fa-circle-check"></i>
                        This conversation has already been resolved.
                    </div>
                @endif
            </div>
        </aside>
    </section>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const chatBody = document.getElementById('adminSupportChatBody');
        if (chatBody) {
            chatBody.scrollTop = chatBody.scrollHeight;
        }
    });
</script>
@endpush
@endsection
