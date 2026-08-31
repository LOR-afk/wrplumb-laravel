@extends('admin.layouts.app')

@section('title', 'Support Requests - WRPlumb')
@section('topbar_title', 'Support Queue')
@section('topbar_subtitle', 'Manage client inquiries, escalations, and admin support actions.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/support.css') }}">
@endpush

@section('content')
@php
    $selected = $selectedConversation ?? null;

    $statusClass = function ($status) {
        return match ($status) {
            'open' => 'open',
            'routed' => 'waiting',
            'resolved' => 'resolved',
            default => 'default',
        };
    };

    $priorityFor = function ($conversation) {
        if (($conversation->status ?? null) === 'routed' && ($conversation->current_queue ?? null) === 'admin') {
            return ['High', 'high'];
        }

        if (($conversation->status ?? null) === 'open') {
            return ['Normal', 'normal'];
        }

        if (($conversation->status ?? null) === 'resolved') {
            return ['Low', 'low'];
        }

        return ['Normal', 'normal'];
    };

    $clientName = fn ($conversation) => $conversation?->client?->name
        ?: trim(($conversation?->client?->first_name ?? '') . ' ' . ($conversation?->client?->last_name ?? ''))
        ?: 'Unknown Client';

    $clientInitials = function ($name) {
        return collect(explode(' ', $name))
            ->filter()
            ->take(2)
            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
            ->implode('') ?: 'C';
    };

    $messagePreview = function ($conversation) {
        $latest = $conversation->messages->last() ?? null;

        if (!$latest) {
            return 'No conversation messages yet.';
        }

        return \Illuminate\Support\Str::limit($latest->message, 90);
    };

    $ticketNo = fn ($conversation) => 'SR-' . optional($conversation?->created_at)->format('Ymd') . '-' . str_pad((string) ($conversation?->id ?? 0), 4, '0', STR_PAD_LEFT);
@endphp

<div class="support-command-page">
    <section class="support-hero-card">
        <div class="support-hero-icon">
            <i class="fas fa-headset"></i>
        </div>
        <div class="support-hero-copy">
            <div class="support-kicker">Admin Support Command Center</div>
            <h2>Centralize client concerns and escalations.</h2>
            <p>Review routed conversations, respond to clients, and close resolved support requests from one focused workspace.</p>
        </div>
        <div class="support-hero-art">
            <div class="support-van">
                <i class="fas fa-truck-fast"></i>
                <span>WR</span>
            </div>
        </div>
    </section>

    <section class="support-stats-grid">
        <div class="support-stat-card">
            <div class="support-stat-icon blue"><i class="fas fa-inbox"></i></div>
            <div>
                <span>Open Tickets</span>
                <strong>{{ $summary['open'] ?? 0 }}</strong>
                <small>Active admin concerns</small>
            </div>
        </div>

        <div class="support-stat-card">
            <div class="support-stat-icon red"><i class="fas fa-bell"></i></div>
            <div>
                <span>Escalated</span>
                <strong>{{ $summary['escalated'] ?? 0 }}</strong>
                <small>Needs admin attention</small>
            </div>
        </div>

        <div class="support-stat-card">
            <div class="support-stat-icon amber"><i class="fas fa-hourglass-half"></i></div>
            <div>
                <span>Waiting Client</span>
                <strong>{{ $summary['waiting'] ?? 0 }}</strong>
                <small>Awaiting response</small>
            </div>
        </div>

        <div class="support-stat-card">
            <div class="support-stat-icon green"><i class="fas fa-circle-check"></i></div>
            <div>
                <span>Resolved Today</span>
                <strong>{{ $summary['resolved_today'] ?? 0 }}</strong>
                <small>Closed support work</small>
            </div>
        </div>
    </section>

    <section class="support-filter-card">
        <form method="GET" action="{{ route('admin.support.index') }}" class="support-filter-form">
            <div class="support-search-control">
                <i class="fas fa-magnifying-glass"></i>
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search tickets or clients..."
                    value="{{ request('search') }}"
                >
            </div>

            <div class="support-tabs">
                <a href="{{ route('admin.support.index', array_filter(['search' => request('search')])) }}"
                   class="{{ request('status') ? '' : 'active' }}">
                    All
                </a>
                <a href="{{ route('admin.support.index', array_filter(['status' => 'open', 'search' => request('search')])) }}"
                   class="{{ request('status') === 'open' ? 'active' : '' }}">
                    Open
                </a>
                <a href="{{ route('admin.support.index', array_filter(['status' => 'routed', 'search' => request('search')])) }}"
                   class="{{ request('status') === 'routed' ? 'active' : '' }}">
                    Escalated
                </a>
                <a href="{{ route('admin.support.index', array_filter(['status' => 'resolved', 'search' => request('search')])) }}"
                   class="{{ request('status') === 'resolved' ? 'active' : '' }}">
                    Resolved
                </a>
            </div>

            <div class="support-filter-actions">
                <button class="btn btn-primary">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                <a href="{{ route('admin.support.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </section>

    <section class="support-workspace">
        <aside class="support-ticket-panel">
            <div class="support-panel-head">
                <div>
                    <h5>Tickets</h5>
                    <p>Showing {{ $conversations->count() }} of {{ $conversations->total() }} requests.</p>
                </div>
                <span class="support-chip">Newest</span>
            </div>

            <div class="support-ticket-list">
                @forelse ($conversations as $conversation)
                    @php
                        $name = $clientName($conversation);
                        [$priorityLabel, $priorityClass] = $priorityFor($conversation);
                        $isSelected = $selected && (int) $selected->id === (int) $conversation->id;
                    @endphp

                    <a href="{{ route('admin.support.index', array_merge(request()->except('page'), ['conversation' => $conversation->id])) }}"
                       class="support-ticket-card {{ $isSelected ? 'active' : '' }}">
                        <div class="support-ticket-avatar">{{ $clientInitials($name) }}</div>

                        <div class="support-ticket-copy">
                            <div class="support-ticket-top">
                                <strong>{{ $name }}</strong>
                                <span>{{ optional($conversation->created_at)->diffForHumans() }}</span>
                            </div>

                            <h6>{{ $conversation->status === 'resolved' ? 'Resolved conversation' : 'Client support concern' }}</h6>
                            <p>{{ $messagePreview($conversation) }}</p>

                            <div class="support-badge-row">
                                <span class="support-status {{ $statusClass($conversation->status) }}">
                                    {{ ucfirst($conversation->status ?? 'open') }}
                                </span>
                                <span class="support-priority {{ $priorityClass }}">{{ $priorityLabel }}</span>
                                <span class="support-count">
                                    <i class="fas fa-message"></i> {{ $conversation->messages_count }}
                                </span>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="support-empty-mini">
                        <i class="fas fa-inbox"></i>
                        <strong>No support requests found</strong>
                        <span>Client concerns routed to Admin will appear here.</span>
                    </div>
                @endforelse
            </div>

            @if(method_exists($conversations, 'links'))
                <div class="support-pagination">
                    {{ $conversations->links() }}
                </div>
            @endif
        </aside>

        <main class="support-conversation-panel">
            @if ($selected)
                @php
                    $selectedName = $clientName($selected);
                    [$selectedPriorityLabel, $selectedPriorityClass] = $priorityFor($selected);
                    $latestClientMessage = $selected->messages->where('sender_type', 'client')->last();
                @endphp

                <div class="support-conversation-head">
                    <div>
                        <div class="support-ticket-id">
                            Ticket #{{ $ticketNo($selected) }}
                            <button type="button" class="support-copy-btn" title="Ticket reference">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <h4>{{ $selected->status === 'resolved' ? 'Resolved support conversation' : 'Client support concern' }}</h4>
                        <p>{{ $selectedName }} • {{ optional($selected->created_at)->format('M d, Y h:i A') }}</p>
                    </div>

                    <div class="support-head-badges">
                        <span class="support-priority {{ $selectedPriorityClass }}">{{ $selectedPriorityLabel }}</span>
                        <span class="support-status {{ $statusClass($selected->status) }}">{{ ucfirst($selected->status ?? 'open') }}</span>
                    </div>
                </div>

                <div class="support-summary-box">
                    <span><i class="fas fa-clipboard-list me-1"></i> Concern Summary</span>
                    <p>{{ $latestClientMessage ? \Illuminate\Support\Str::limit($latestClientMessage->message, 180) : 'No client message summary available yet.' }}</p>
                </div>

                <div class="support-thread" id="supportThread">
                    @forelse ($selected->messages as $message)
                        @php
                            $sender = $message->sender_type;
                            $senderLabel = match ($sender) {
                                'admin' => 'Admin',
                                'client' => $selectedName,
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
                                <small>{{ optional($message->created_at)->format('M d, h:i A') }}</small>
                            </div>
                        @else
                            <div class="support-message-row {{ $sender === 'admin' ? 'right' : 'left' }}">
                                <div class="support-message-avatar {{ $bubbleClass }}">
                                    {{ strtoupper(substr($senderLabel, 0, 1)) }}
                                </div>
                                <div class="support-message-wrap">
                                    <div class="support-sender-label">{{ $senderLabel }}</div>
                                    <div class="support-bubble {{ $bubbleClass }}">{{ $message->message }}</div>
                                    <small>{{ optional($message->created_at)->format('M d, h:i A') }}</small>
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="support-empty-mini compact">
                            <i class="fas fa-comments"></i>
                            <strong>No messages yet</strong>
                            <span>This conversation has no recorded messages.</span>
                        </div>
                    @endforelse
                </div>

                @if ($selected->current_queue === 'admin' && $selected->status !== 'resolved')
                    <form method="POST" action="{{ route('admin.support.reply', $selected) }}" class="support-reply-box">
                        @csrf

                        <label class="support-reply-label">Reply to Client</label>
                        <div class="support-reply-control">
                            <textarea name="message" rows="2" placeholder="Type your message..." required></textarea>
                            <button class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i> Send
                            </button>
                        </div>
                    </form>
                @else
                    <div class="support-resolved-note">
                        <i class="fas fa-circle-check"></i>
                        This conversation is already resolved.
                    </div>
                @endif
            @else
                <div class="support-empty-state compact">
                    <div><i class="fas fa-headset"></i></div>
                    <strong>No ticket selected</strong>
                    <p>Select a support ticket from the list to review the conversation and available actions.</p>
                </div>
            @endif
        </main>

        <aside class="support-action-panel">
            @if ($selected)
                @php
                    $selectedName = $clientName($selected);
                @endphp

                <div class="support-actions-card">
                    <h5>Actions</h5>

                    @if ($selected->status !== 'resolved')
                        <a href="#supportThread" class="btn btn-primary w-100">
                            <i class="fas fa-paper-plane me-1"></i> Reply to Client
                        </a>

                        <form method="POST" action="{{ route('admin.support.resolve', $selected) }}">
                            @csrf
                            <button class="btn btn-outline-success w-100">
                                <i class="fas fa-circle-check me-1"></i> Mark Resolved
                            </button>
                        </form>
                    @else
                        <div class="support-resolved-note compact">
                            <i class="fas fa-circle-check"></i>
                            Resolved support ticket.
                        </div>
                    @endif
                </div>

                <div class="support-details-card">
                    <h5>Ticket Details</h5>

                    <div class="support-detail-row">
                        <span>Ticket ID</span>
                        <strong>{{ $ticketNo($selected) }}</strong>
                    </div>
                    <div class="support-detail-row">
                        <span>Client</span>
                        <strong>{{ $selectedName }}</strong>
                    </div>
                    <div class="support-detail-row">
                        <span>Email</span>
                        <strong>{{ $selected->client?->email ?? '—' }}</strong>
                    </div>
                    <div class="support-detail-row">
                        <span>Queue</span>
                        <strong>{{ ucfirst($selected->current_queue ?? 'admin') }}</strong>
                    </div>
                    <div class="support-detail-row">
                        <span>Created</span>
                        <strong>{{ optional($selected->created_at)->format('M d, Y h:i A') ?? '—' }}</strong>
                    </div>
                    <div class="support-detail-row">
                        <span>Routed At</span>
                        <strong>{{ optional($selected->routed_at)->format('M d, Y h:i A') ?? '—' }}</strong>
                    </div>
                </div>

                <div class="support-timeline-card">
                    <h5><i class="fas fa-route me-1 text-primary"></i> Routing Timeline</h5>

                    <div class="support-timeline">
                        <div class="support-timeline-item done">
                            <span>C</span>
                            <div>
                                <strong>Client</strong>
                                <small>{{ optional($selected->created_at)->format('M d, h:i A') }}</small>
                                <p>Ticket created</p>
                            </div>
                        </div>

                        @if ($selected->routed_at)
                            <div class="support-timeline-item done">
                                <span>H</span>
                                <div>
                                    <strong>HR Team</strong>
                                    <small>{{ optional($selected->routed_at)->format('M d, h:i A') }}</small>
                                    <p>Escalated to Admin</p>
                                </div>
                            </div>
                        @endif

                        <div class="support-timeline-item {{ $selected->status === 'resolved' ? 'done' : 'active' }}">
                            <span>A</span>
                            <div>
                                <strong>Admin</strong>
                                <small>{{ $selected->status === 'resolved' ? optional($selected->resolved_at)->format('M d, h:i A') : 'Now' }}</small>
                                <p>{{ $selected->status === 'resolved' ? 'Resolved ticket' : 'Reviewing concern' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="support-details-card">
                    <h5>Ticket Details</h5>
                    <div class="support-empty-mini">
                        <i class="fas fa-circle-info"></i>
                        <strong>No ticket selected</strong>
                        <span>Choose a ticket to show actions and routing details.</span>
                    </div>
                </div>
            @endif
        </aside>
    </section>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const supportThread = document.getElementById('supportThread');
        if (supportThread) {
            supportThread.scrollTop = supportThread.scrollHeight;
        }
    });
</script>
@endpush
