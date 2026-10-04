@extends('admin.layouts.app')

@section('title', 'Support Ticket - WRPlumb')
@section('topbar_title', 'Support Center')
@section('topbar_subtitle', 'Review the concern and respond as Admin.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/support.css') }}?v=admin-support-ref-01">
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

    $ticketNo = 'SR-' .
        optional($conversation->created_at)->format('Ymd') .
        '-' .
        str_pad((string) $conversation->id, 4, '0', STR_PAD_LEFT);

    $statusClass = match ($conversation->status) {
        'open' => 'open',
        'routed' => 'waiting',
        'resolved' => 'resolved',
        default => 'default',
    };

    $priority = match (true) {
        ($conversation->status ?? null) === 'routed' && ($conversation->current_queue ?? null) === 'admin'
            => ['Urgent', 'urgent'],
        ($conversation->status ?? null) === 'resolved'
            => ['Low', 'low'],
        default
            => ['Normal', 'normal'],
    };

    $latestClientMessage = $conversation->messages->where('sender_type', 'client')->last();

    $ticketSubject = $latestClientMessage
        ? \Illuminate\Support\Str::limit(
            preg_replace('/\s+/', ' ', trim($latestClientMessage->message)),
            78
        )
        : 'Client support concern';
@endphp

<div class="support-ticket-page">
    <div class="support-ticket-detail-head">
        <div>
            <a href="{{ route('admin.support.index') }}" class="support-back-link">
                <i class="fas fa-arrow-left"></i> Back to Support Center
            </a>

            <div class="support-ticket-heading-meta">
                <span>#{{ $ticketNo }}</span>
                <span class="ticket-priority {{ $priority[1] }}">{{ $priority[0] }}</span>
                <span class="ticket-status {{ $statusClass }}">
                    {{ ucfirst($conversation->status ?? 'open') }}
                </span>
            </div>

            <h2>{{ $ticketSubject }}</h2>
        </div>

        <div class="support-ticket-actions">
            @if ($conversation->status !== 'resolved')
                <form method="POST" action="{{ route('admin.support.resolve', $conversation) }}">
                    @csrf
                    <button type="submit" class="support-status-action">
                        <i class="far fa-circle-check"></i> Mark as Resolved
                    </button>
                </form>
            @else
                <span class="support-resolved-pill">
                    <i class="fas fa-circle-check"></i> Resolved
                </span>
            @endif
        </div>
    </div>

    <section class="support-ticket-detail-grid">
        <main class="support-message-panel">
            <div class="support-message-thread" id="adminSupportChatBody">
                @forelse ($conversation->messages as $message)
                    @php
                        $sender = $message->sender_type;

                        $senderLabel = match ($sender) {
                            'admin' => 'Admin',
                            'client' => $clientName,
                            'hr' => 'HR Team',
                            'system' => 'System',
                            default => 'Support Bot',
                        };

                        $senderClass = match ($sender) {
                            'admin' => 'admin',
                            'client' => 'client',
                            'hr' => 'hr',
                            'system' => 'system',
                            default => 'bot',
                        };
                    @endphp

                    @if ($sender === 'system')
                        <div class="support-system-message">
                            <span>{{ $message->message }}</span>
                            <small>{{ $message->created_at->format('M d, Y h:i A') }}</small>
                        </div>
                    @else
                        <article class="support-message-card {{ $senderClass }}">
                            <header>
                                <span class="support-message-avatar {{ $senderClass }}">
                                    {{ strtoupper(substr($senderLabel, 0, 1)) }}
                                </span>
                                <div>
                                    <strong>{{ $senderLabel }}</strong>
                                    <small>{{ $message->created_at->diffForHumans() }}</small>
                                </div>
                            </header>

                            <div class="support-message-copy">{!! nl2br(e($message->message)) !!}</div>

                            @if (!empty($message->attachment_path))
                                <a href="{{ asset('storage/' . $message->attachment_path) }}"
                                   target="_blank"
                                   rel="noopener"
                                   class="support-attachment">
                                    <i class="far fa-image"></i>
                                    <span>
                                        <strong>{{ $message->attachment_name ?? 'Attachment' }}</strong>
                                        @if(!empty($message->attachment_size))
                                            <small>{{ number_format($message->attachment_size / 1024, 0) }} KB</small>
                                        @endif
                                    </span>
                                    <i class="fas fa-arrow-up-right-from-square"></i>
                                </a>
                            @endif
                        </article>
                    @endif
                @empty
                    <div class="support-empty-state compact">
                        <span><i class="fas fa-comments"></i></span>
                        <strong>No messages yet</strong>
                        <p>This ticket has no recorded conversation.</p>
                    </div>
                @endforelse
            </div>

            @if ($conversation->current_queue === 'admin' && $conversation->status !== 'resolved')
                <form method="POST"
                      action="{{ route('admin.support.reply', $conversation) }}"
                      class="support-reply-composer">
                    @csrf

                    <label for="adminSupportReply">Reply to Client</label>
                    <textarea id="adminSupportReply"
                              name="message"
                              rows="4"
                              placeholder="Type your reply here..."
                              required></textarea>

                    <div class="support-composer-actions">
                        <span>Reply will be sent as Admin.</span>
                        <button type="submit">
                            <i class="fas fa-paper-plane"></i> Send Reply
                        </button>
                    </div>
                </form>
            @elseif($conversation->status === 'resolved')
                <div class="support-closed-note">
                    <i class="fas fa-circle-check"></i>
                    This support ticket has already been resolved.
                </div>
            @endif
        </main>

        <aside class="support-ticket-sidebar">
            <section class="support-requester-card">
                <span class="support-sidebar-label">Requester</span>

                <div class="support-requester-head">
                    <span class="support-requester-avatar">{{ $initials }}</span>
                    <div>
                        <strong>{{ $clientName }}</strong>
                        <span>{{ $conversation->client?->email ?? 'No email listed' }}</span>
                    </div>
                </div>

                <div class="support-sidebar-divider"></div>

                <span class="support-sidebar-label">Details</span>

                <dl class="support-ticket-details">
                    <div>
                        <dt>Ticket ID</dt>
                        <dd>{{ $ticketNo }}</dd>
                    </div>
                    <div>
                        <dt>Username</dt>
                        <dd>{{ $conversation->client?->username ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>Queue</dt>
                        <dd>{{ ucfirst($conversation->current_queue ?? 'admin') }}</dd>
                    </div>
                    <div>
                        <dt>Status</dt>
                        <dd>
                            <span class="ticket-status {{ $statusClass }}">
                                {{ ucfirst($conversation->status ?? 'open') }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt>Created</dt>
                        <dd>{{ $conversation->created_at->format('M d, Y h:i A') }}</dd>
                    </div>
                    <div>
                        <dt>Routed At</dt>
                        <dd>{{ optional($conversation->routed_at)->format('M d, Y h:i A') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>Resolved At</dt>
                        <dd>{{ optional($conversation->resolved_at)->format('M d, Y h:i A') ?? '—' }}</dd>
                    </div>
                </dl>
            </section>
        </aside>
    </section>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/support.js') }}?v=admin-support-ref-01"></script>
@endpush
