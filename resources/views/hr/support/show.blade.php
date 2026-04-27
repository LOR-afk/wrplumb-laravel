@extends('hr.layouts.app')

@section('title', 'HR Support Conversation - WRPlumb')
@section('topbar_title', 'Conversation Details')
@section('topbar_subtitle', 'Review and respond to the client concern.')

@section('content')
<style>
    .support-grid {
        display: grid;
        grid-template-columns: 1.55fr 0.95fr;
        gap: 18px;
    }

    .chat-shell {
        background: #f8fbff;
        border: 1px solid var(--wr-border);
        border-radius: 18px;
        padding: 18px;
        min-height: 380px;
    }

    .chat-scroll {
        display: flex;
        flex-direction: column;
        gap: 14px;
        max-height: 620px;
        overflow-y: auto;
        padding-right: 6px;
    }

    .chat-row {
        display: flex;
    }

    .chat-row.client {
        justify-content: flex-start;
    }

    .chat-row.hr {
        justify-content: flex-end;
    }

    .chat-row.other {
        justify-content: flex-start;
    }

    .bubble-wrap {
        max-width: 78%;
    }

    .sender-label {
        font-size: 0.78rem;
        font-weight: 800;
        color: var(--wr-muted);
        margin-bottom: 4px;
    }

    .bubble {
        padding: 12px 14px;
        border-radius: 18px;
        line-height: 1.5;
        font-size: 0.94rem;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
        border: 1px solid #edf2f7;
        word-break: break-word;
    }

    .bubble.client {
        background: #ffffff;
        color: #1f2937;
        border-bottom-left-radius: 8px;
    }

    .bubble.hr {
        background: linear-gradient(90deg, #1d9bf0, #3bb6ff);
        color: #fff;
        border: none;
        border-bottom-right-radius: 8px;
    }

    .bubble.admin {
        background: #ecfdf3;
        color: #166534;
        border-color: #bbf7d0;
        border-bottom-left-radius: 8px;
    }

    .bubble.bot {
        background: #f8fafc;
        color: #334155;
        border-bottom-left-radius: 8px;
    }

    .system-row {
        text-align: center;
        margin: 4px 0;
    }

    .system-badge {
        display: inline-flex;
        padding: 8px 14px;
        border-radius: 999px;
        background: #eef2f7;
        color: #475569;
        font-size: 0.83rem;
        font-weight: 700;
    }

    .meta-card {
        background: #fff;
        border: 1px solid var(--wr-border);
        border-radius: 18px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
        padding: 18px;
    }

    .meta-title {
        font-size: 1rem;
        font-weight: 800;
        margin-bottom: 14px;
    }

    .meta-row {
        padding: 10px 0;
        border-bottom: 1px solid #edf2f7;
    }

    .meta-row:last-child {
        border-bottom: none;
    }

    .meta-label {
        font-size: 0.78rem;
        color: var(--wr-muted);
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 3px;
    }

    .meta-value {
        font-weight: 700;
        color: #0f172a;
    }

    @media (max-width: 991.98px) {
        .support-grid {
            grid-template-columns: 1fr;
        }

        .bubble-wrap {
            max-width: 92%;
        }
    }
</style>

<div class="page-header">
    <h1>{{ $conversation->client->name }}</h1>
    <p>HR support conversation and escalation handling.</p>
</div>

<div class="support-grid">
    <div class="panel">
        <div class="panel-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5><i class="fas fa-comments me-2 text-primary"></i>Conversation Thread</h5>

            <div class="d-flex gap-2">
                @if ($conversation->status === 'open')
                    <span class="badge-soft green">Open</span>
                @elseif ($conversation->status === 'routed')
                    <span class="badge-soft orange">Routed</span>
                @elseif ($conversation->status === 'resolved')
                    <span class="badge-soft gray">Resolved</span>
                @else
                    <span class="badge-soft red">{{ ucfirst($conversation->status) }}</span>
                @endif

                @if ($conversation->current_queue === 'hr')
                    <span class="badge-soft blue">HR Queue</span>
                @elseif ($conversation->current_queue === 'resolved')
                    <span class="badge-soft gray">Resolved</span>
                @endif
            </div>
        </div>

        <div class="panel-body">
            <div class="chat-shell">
                <div class="chat-scroll" id="hrSupportChatBody">
                    @foreach ($conversation->messages as $message)
                        @php
                            $sender = $message->sender_type;
                        @endphp

                        @if ($sender === 'system')
                            <div class="system-row">
                                <span class="system-badge">{{ $message->message }}</span>
                            </div>
                        @elseif ($sender === 'hr')
                            <div class="chat-row hr">
                                <div class="bubble-wrap">
                                    <div class="sender-label text-end">HR</div>
                                    <div class="bubble hr">{{ $message->message }}</div>
                                    <div class="small text-muted mt-2 text-end">
                                        {{ $message->created_at->format('M d, h:i A') }}
                                    </div>
                                </div>
                            </div>
                        @elseif ($sender === 'client')
                            <div class="chat-row client">
                                <div class="bubble-wrap">
                                    <div class="sender-label">Client</div>
                                    <div class="bubble client">{{ $message->message }}</div>
                                    <div class="small text-muted mt-2">
                                        {{ $message->created_at->format('M d, h:i A') }}
                                    </div>
                                </div>
                            </div>
                        @elseif ($sender === 'admin')
                            <div class="chat-row other">
                                <div class="bubble-wrap">
                                    <div class="sender-label">Admin</div>
                                    <div class="bubble admin">{{ $message->message }}</div>
                                    <div class="small text-muted mt-2">
                                        {{ $message->created_at->format('M d, h:i A') }}
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="chat-row other">
                                <div class="bubble-wrap">
                                    <div class="sender-label">Support Bot</div>
                                    <div class="bubble bot">{{ $message->message }}</div>
                                    <div class="small text-muted mt-2">
                                        {{ $message->created_at->format('M d, h:i A') }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            @if ($conversation->current_queue === 'hr')
                <form method="POST" action="{{ route('hr.support.reply', $conversation) }}" class="mt-4">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Reply as HR</label>
                        <textarea
                            name="message"
                            class="form-control"
                            rows="5"
                            placeholder="Type your reply here..."
                            required
                        ></textarea>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Send Reply
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <div class="d-flex flex-column gap-3">
        <div class="meta-card">
            <div class="meta-title">Conversation Details</div>

            <div class="meta-row">
                <div class="meta-label">Name</div>
                <div class="meta-value">{{ $conversation->client->name }}</div>
            </div>

            <div class="meta-row">
                <div class="meta-label">Email</div>
                <div class="meta-value">{{ $conversation->client->email }}</div>
            </div>

            <div class="meta-row">
                <div class="meta-label">Username</div>
                <div class="meta-value">{{ $conversation->client->username ?? '—' }}</div>
            </div>

            <div class="meta-row">
                <div class="meta-label">Created</div>
                <div class="meta-value">{{ $conversation->created_at->format('Y-m-d h:i A') }}</div>
            </div>

            <div class="meta-row">
                <div class="meta-label">Routed At</div>
                <div class="meta-value">{{ optional($conversation->routed_at)->format('Y-m-d h:i A') ?? '—' }}</div>
            </div>
        </div>

        <div class="meta-card">
            <div class="meta-title">Conversation Actions</div>

            <form method="POST" action="{{ route('hr.support.escalate-admin', $conversation) }}">
                @csrf
                <button class="btn btn-outline-danger w-100">
                    <i class="fas fa-share me-2"></i>Escalate to Admin
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const chatBody = document.getElementById('hrSupportChatBody');
        if (chatBody) {
            chatBody.scrollTop = chatBody.scrollHeight;
        }
    });
</script>
@endsection