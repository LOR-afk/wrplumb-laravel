@extends('admin.layouts.app')

@section('title', 'Support Center - WRPlumb')
@section('topbar_title', 'Support Center')
@section('topbar_subtitle', 'Manage client inquiries, escalations, and admin support actions.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/support.css') }}?v=admin-support-ref-01">
@endpush

@section('content')
@php
    $statusClass = fn ($status) => match ($status) {
        'open' => 'open',
        'routed' => 'waiting',
        'resolved' => 'resolved',
        default => 'default',
    };

    $priorityFor = function ($conversation) {
        if (($conversation->status ?? null) === 'routed' && ($conversation->current_queue ?? null) === 'admin') {
            return ['Urgent', 'urgent', 3];
        }

        if (($conversation->status ?? null) === 'resolved') {
            return ['Low', 'low', 1];
        }

        return ['Normal', 'normal', 2];
    };

    $clientName = fn ($conversation) => $conversation?->client?->name
        ?: trim(($conversation?->client?->first_name ?? '') . ' ' . ($conversation?->client?->last_name ?? ''))
        ?: 'Unknown Client';

    $clientInitials = fn ($name) => collect(explode(' ', $name))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('') ?: 'C';

    $latestMessage = fn ($conversation) => $conversation->messages->last() ?? null;

    $subjectFor = function ($conversation) use ($latestMessage) {
        $message = $latestMessage($conversation);

        return $message
            ? \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', trim($message->message)), 62)
            : 'Client support concern';
    };

    $previewFor = function ($conversation) use ($latestMessage) {
        $message = $latestMessage($conversation);

        return $message
            ? \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', trim($message->message)), 125)
            : 'No conversation messages yet.';
    };

    $ticketNo = fn ($conversation) =>
        'SR-' .
        optional($conversation?->created_at)->format('Ymd') .
        '-' .
        str_pad((string) ($conversation?->id ?? 0), 4, '0', STR_PAD_LEFT);

    $activeStatus = request('status');
@endphp

<div class="support-center-shell">
    <aside class="support-rail">
        <div class="support-rail-head">
            <span class="support-rail-icon"><i class="fas fa-headset"></i></span>
            <div>
                <strong>Support Queue</strong>
                <small>Admin workspace</small>
            </div>
        </div>

        <nav class="support-rail-nav">
            <a href="{{ route('admin.support.index', array_filter(['search' => request('search')])) }}"
               class="{{ $activeStatus ? '' : 'active' }}">
                <span><i class="fas fa-ticket"></i> All Tickets</span>
                @if(isset($summary['total'])) <b>{{ $summary['total'] }}</b> @endif
            </a>

            <a href="{{ route('admin.support.index', array_filter(['status' => 'open', 'search' => request('search')])) }}"
               class="{{ $activeStatus === 'open' ? 'active' : '' }}">
                <span><i class="far fa-folder-open"></i> Open</span>
                <b>{{ $summary['open'] ?? 0 }}</b>
            </a>

            <a href="{{ route('admin.support.index', array_filter(['status' => 'routed', 'search' => request('search')])) }}"
               class="{{ $activeStatus === 'routed' ? 'active' : '' }}">
                <span><i class="fas fa-arrow-up-right-dots"></i> Escalated</span>
                <b>{{ $summary['escalated'] ?? 0 }}</b>
            </a>

            <a href="{{ route('admin.support.index', array_filter(['status' => 'resolved', 'search' => request('search')])) }}"
               class="{{ $activeStatus === 'resolved' ? 'active' : '' }}">
                <span><i class="far fa-circle-check"></i> Resolved</span>
                <b>{{ $summary['resolved'] ?? 0 }}</b>
            </a>
        </nav>

        <div class="support-rail-section">
            <span class="support-rail-label">Today</span>

            <div class="support-rail-summary">
                <div>
                    <span class="dot amber"></span>
                    <span>Waiting Client</span>
                    <b>{{ $summary['waiting'] ?? 0 }}</b>
                </div>

                <div>
                    <span class="dot green"></span>
                    <span>Resolved Today</span>
                    <b>{{ $summary['resolved_today'] ?? 0 }}</b>
                </div>
            </div>
        </div>
    </aside>

    <section class="support-list-panel">
        <form method="GET" action="{{ route('admin.support.index') }}" class="support-toolbar">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <div class="support-search-box">
                <i class="fas fa-magnifying-glass"></i>
                <input type="search"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search tickets or clients...">
            </div>

            <button type="submit" class="support-filter-btn">
                <i class="fas fa-filter"></i> Filter
            </button>

            <select id="supportSortSelect" class="support-sort-select" aria-label="Sort tickets">
                <option value="newest">Newest First</option>
                <option value="oldest">Oldest First</option>
                <option value="priority">Priority</option>
            </select>
        </form>

        <div class="support-list-head">
            <div>
                <strong>
                    @if($activeStatus === 'open')
                        Open Tickets
                    @elseif($activeStatus === 'routed')
                        Escalated Tickets
                    @elseif($activeStatus === 'resolved')
                        Resolved Tickets
                    @else
                        All Tickets
                    @endif
                </strong>
                <span>Showing {{ $conversations->count() }} of {{ $conversations->total() }} requests</span>
            </div>

            @if(request('search') || request('status'))
                <a href="{{ route('admin.support.index') }}" class="support-reset-link">Clear filters</a>
            @endif
        </div>

        <div class="support-ticket-list" id="supportTicketList">
            @forelse ($conversations as $conversation)
                @php
                    $name = $clientName($conversation);
                    [$priorityLabel, $priorityClass, $priorityRank] = $priorityFor($conversation);
                    $latest = $latestMessage($conversation);
                @endphp

                <a href="{{ route('admin.support.show', $conversation) }}"
                   class="support-ticket-row"
                   data-created="{{ optional($conversation->created_at)->timestamp ?? 0 }}"
                   data-priority="{{ $priorityRank }}"
                   data-admin-search="{{ strtolower(
                       $ticketNo($conversation) . ' ' .
                       $name . ' ' .
                       $subjectFor($conversation) . ' ' .
                       $previewFor($conversation) . ' ' .
                       ($conversation->status ?? '')
                   ) }}">
                    <span class="ticket-check" aria-hidden="true"></span>

                    <div class="ticket-main">
                        <div class="ticket-topline">
                            <span class="ticket-id">#{{ $ticketNo($conversation) }}</span>
                            <span class="ticket-priority {{ $priorityClass }}">{{ $priorityLabel }}</span>
                            <span class="ticket-status {{ $statusClass($conversation->status) }}">
                                {{ ucfirst($conversation->status ?? 'open') }}
                            </span>
                        </div>

                        <h3>{{ $subjectFor($conversation) }}</h3>
                        <p>{{ $previewFor($conversation) }}</p>

                        <div class="ticket-meta">
                            <span class="ticket-avatar">{{ $clientInitials($name) }}</span>
                            <strong>{{ $name }}</strong>
                            <span class="meta-divider"></span>
                            <span>
                                <i class="far fa-clock"></i>
                                {{ optional($latest?->created_at ?? $conversation->updated_at)->diffForHumans() }}
                            </span>
                            <span>
                                <i class="far fa-comment-dots"></i>
                                {{ $conversation->messages_count ?? $conversation->messages->count() }}
                                messages
                            </span>
                        </div>
                    </div>

                    <span class="ticket-row-arrow"><i class="fas fa-chevron-right"></i></span>
                </a>
            @empty
                <div class="support-empty-state">
                    <span><i class="fas fa-inbox"></i></span>
                    <strong>No support tickets found</strong>
                    <p>Try another search or status filter.</p>
                </div>
            @endforelse
        </div>

        @if(method_exists($conversations, 'links'))
            <div class="support-pagination">{{ $conversations->links() }}</div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/support.js') }}?v=admin-support-ref-01"></script>
@endpush
