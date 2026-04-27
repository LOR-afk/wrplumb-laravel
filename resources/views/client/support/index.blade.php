@extends('client.layouts.app')

@section('title', 'Customer Support - WRPlumb')
@section('topbar_title', 'Customer Support')
@section('topbar_subtitle', 'Ask questions and get help with your service request.')

@section('content')
<style>
    .support-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 18px;
    }

    .chat-shell {
        background: #f8fbff;
        border: 1px solid var(--wr-border);
        border-radius: 18px;
        padding: 18px;
        min-height: 420px;
    }

    .chat-scroll {
        display: flex;
        flex-direction: column;
        gap: 14px;
        max-height: 540px;
        overflow-y: auto;
        padding-right: 6px;
    }

    .chat-row {
        display: flex;
    }

    .chat-row.client {
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
        background: linear-gradient(90deg, #1d9bf0, #3bb6ff);
        color: #fff;
        border: none;
        border-bottom-right-radius: 8px;
    }

    .bubble.bot {
        background: #ffffff;
        color: #1f2937;
        border-bottom-left-radius: 8px;
    }

    .bubble.hr {
        background: #ecfdf3;
        color: #166534;
        border-color: #bbf7d0;
        border-bottom-left-radius: 8px;
    }

    .bubble.admin {
        background: #eef6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
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

    .quick-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 16px;
    }

    .quick-actions button {
        border-radius: 999px;
    }

    .queue-note {
        margin-top: 16px;
        padding: 12px 14px;
        border-radius: 14px;
        font-size: 0.92rem;
        font-weight: 600;
    }

    .queue-note.hr {
        background: #ecfdf3;
        color: #166534;
        border: 1px solid #bbf7d0;
    }

    .queue-note.admin {
        background: #eef6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }

    .queue-note.resolved {
        background: #f8fafc;
        color: #475569;
        border: 1px solid #e2e8f0;
    }

    @media (max-width: 767.98px) {
        .bubble-wrap {
            max-width: 92%;
        }
    }
</style>

<div class="page-header">
    <h1>Customer Support</h1>
    <p>Chat with the support bot first. If needed, your concern will be routed automatically to HR or Admin.</p>
</div>

<div class="panel">
    <div class="panel-header">
        <h5><i class="fas fa-headset me-2 text-primary"></i>Support Chat</h5>
    </div>

    <div class="panel-body">
        <div class="chat-shell">
            <div class="chat-scroll" id="clientSupportChatBody">
                @if ($conversation && $conversation->messages->count())
                    @foreach ($conversation->messages as $message)
                        @php
                            $sender = $message->sender_type;
                        @endphp

                        @if ($sender === 'system')
                            <div class="system-row">
                                <span class="system-badge">{{ $message->message }}</span>
                            </div>
                        @elseif ($sender === 'client')
                            <div class="chat-row client">
                                <div class="bubble-wrap">
                                    <div class="sender-label text-end">You</div>
                                    <div class="bubble client">{{ $message->message }}</div>
                                    <div class="small text-muted mt-2 text-end">
                                        {{ $message->created_at->format('M d, h:i A') }}
                                    </div>
                                </div>
                            </div>
                        @elseif ($sender === 'hr')
                            <div class="chat-row other">
                                <div class="bubble-wrap">
                                    <div class="sender-label">HR</div>
                                    <div class="bubble hr">{{ $message->message }}</div>
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
                @else
                    <div class="text-center py-5 text-muted">
                        Start a conversation with support.
                    </div>
                @endif
            </div>
        </div>

        @if ($conversation)
            @if ($conversation->current_queue === 'hr')
                <div class="queue-note hr">
                    Your concern is currently being handled by HR.
                </div>
            @elseif ($conversation->current_queue === 'admin')
                <div class="queue-note admin">
                    Your concern is currently being handled by Admin.
                </div>
            @elseif ($conversation->status === 'resolved')
                <div class="queue-note resolved">
                    This conversation has already been resolved.
                </div>
            @endif
        @endif

        <div class="quick-actions">
            <button type="button" class="btn btn-outline-primary btn-sm quick-fill" data-message="How do I request a quotation?">Quotation Help</button>
            <button type="button" class="btn btn-outline-primary btn-sm quick-fill" data-message="How do I track my request?">Track Request</button>
            <button type="button" class="btn btn-outline-primary btn-sm quick-fill" data-message="How do I contact support?">Contact Support</button>
        </div>

        <form method="POST" action="{{ route('client.support.send') }}" class="mt-4">
            @csrf

            <div class="mb-3">
                <label class="form-label">Your Message</label>
                <textarea
                    name="message"
                    id="clientSupportMessage"
                    class="form-control"
                    rows="4"
                    placeholder="Type your concern here..."
                    required
                ></textarea>
            </div>

            <button class="btn btn-primary">
                <i class="fas fa-paper-plane me-2"></i>Send Message
            </button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const chatBody = document.getElementById('clientSupportChatBody');
        if (chatBody) {
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        const input = document.getElementById('clientSupportMessage');
        document.querySelectorAll('.quick-fill').forEach(button => {
            button.addEventListener('click', function () {
                input.value = this.dataset.message;
                input.focus();
            });
        });
    });
</script>
@endsection