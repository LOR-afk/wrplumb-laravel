@extends('client.layouts.app')

@section('title', 'Customer Support - WRPlumb')
@section('topbar_title', 'Customer Support')
@section('topbar_subtitle', 'Ask questions and get help with your service request.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/client/support.css') }}">
@endpush

@section('content')
@php
    $isWidget = request()->boolean('support_widget');
@endphp

@if (!$isWidget)
    <div class="page-header">
        <h1>Customer Support</h1>
        <p>Chat with the support bot first. If needed, your concern will be routed automatically to HR or Admin.</p>
    </div>
@endif

<div class="{{ $isWidget ? 'support-widget-shell' : 'support-page-shell' }}">
    <div class="support-messenger">
        <div class="support-messenger-header">
            <div class="support-avatar">
                <i class="fas fa-headset"></i>
            </div>

            <div class="flex-grow-1">
                <h5 class="mb-0">Customer Support</h5>
                <small>Typically replies in minutes</small>
            </div>

            @if ($isWidget)
                <button
                    type="button"
                    class="support-header-icon"
                    onclick="window.parent?.document?.getElementById('clientSupportToggle')?.click();"
                    aria-label="Close support"
                >
                    <i class="fas fa-xmark"></i>
                </button>
            @endif
        </div>

        <div class="support-messenger-body" id="clientSupportChatBody">
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
                                <div class="bubble client">{{ $message->message }}</div>
                                <div class="time-label text-end">
                                    {{ $message->created_at->format('M d, h:i A') }}
                                </div>
                            </div>
                        </div>
                    @elseif ($sender === 'hr')
                        <div class="chat-row other">
                            <div class="sender-avatar hr">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div class="bubble-wrap">
                                <div class="sender-label">HR</div>
                                <div class="bubble hr">{{ $message->message }}</div>
                                <div class="time-label">
                                    {{ $message->created_at->format('M d, h:i A') }}
                                </div>
                            </div>
                        </div>
                    @elseif ($sender === 'admin')
                        <div class="chat-row other">
                            <div class="sender-avatar admin">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <div class="bubble-wrap">
                                <div class="sender-label">Admin</div>
                                <div class="bubble admin">{{ $message->message }}</div>
                                <div class="time-label">
                                    {{ $message->created_at->format('M d, h:i A') }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="chat-row other">
                            <div class="sender-avatar bot">
                                <i class="fas fa-headset"></i>
                            </div>
                            <div class="bubble-wrap">
                                <div class="sender-label">Support Bot</div>
                                <div class="bubble bot">{{ $message->message }}</div>
                                <div class="time-label">
                                    {{ $message->created_at->format('M d, h:i A') }}
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            @else
                <div class="chat-row other">
                    <div class="sender-avatar bot">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div class="bubble-wrap">
                        <div class="sender-label">Support Bot</div>
                        <div class="bubble bot">Hi! How can we help you today?</div>
                    </div>
                </div>
            @endif

            <div class="quick-actions">
                <button type="button" class="quick-fill" data-message="How do I request a quotation?">Quotation Help</button>
                <button type="button" class="quick-fill" data-message="How do I track my request?">Track Request</button>
                <button type="button" class="quick-fill" data-message="How do I contact support?">Contact Support</button>
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

        <form
            method="POST"
            action="{{ $isWidget ? route('client.support.send', ['support_widget' => 1]) : route('client.support.send') }}"
            class="support-composer"
        >
            @csrf

            @if ($isWidget)
                <input type="hidden" name="support_widget" value="1">
            @endif

            <textarea
                name="message"
                id="clientSupportMessage"
                class="form-control"
                rows="2"
                placeholder="Send a message..."
                required
            ></textarea>

            <button class="btn btn-primary support-send-btn">
                <i class="fas fa-paper-plane me-1"></i>Send Message
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
