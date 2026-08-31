@extends('client.layouts.app')

@section('title', 'Customer Support - WRPlumb')
@section('topbar_title', 'Customer Support')
@section('topbar_subtitle', 'Ask questions and get help with your service request.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/client/support.css') }}?v=20260818b">
@endpush

@section('content')
@php
    $isWidget = request()->boolean('support_widget');
@endphp

@if (!$isWidget)
    <div class="page-header support-page-heading">
        <h1>Customer Support</h1>
        <p>
            Chat with the support bot first. Your concern may be routed to HR or Admin when needed.
        </p>
    </div>
@endif

<div class="{{ $isWidget ? 'support-widget-shell' : 'support-page-shell' }}">
    <div class="support-chat-card">
        <header class="support-chat-header">
            <div class="support-header-main">
                <div class="support-avatar">
                    <img
                        src="{{ asset('image/294539416_407599744767669_1937739510480713048_n.jpg') }}"
                        alt="WRPlumb"
                    >
                </div>

                <div class="support-header-text">
                    <h2>Chat with us!</h2>
                    <span>
                        <span class="support-online-dot"></span>
                        Customer Support
                    </span>
                </div>

                <div class="support-header-actions">
                    <div class="support-options-wrap">
                        <button
                            type="button"
                            class="support-header-button"
                            id="supportOptionsToggle"
                            aria-label="More options"
                            aria-expanded="false"
                        >
                            <i class="fas fa-ellipsis-vertical"></i>
                        </button>

                        <div class="support-options-menu" id="supportOptionsMenu" hidden>
                            <button
                                type="button"
                                class="support-options-item danger"
                                id="supportClearChatButton"
                                {{ $conversation ? '' : 'disabled' }}
                            >
                                <i class="fas fa-trash-can"></i>
                                <span>Clear Chat</span>
                            </button>
                        </div>
                    </div>

                    @if ($isWidget)
                        <button
                            type="button"
                            class="support-header-button"
                            onclick="window.parent?.document?.getElementById('clientSupportToggle')?.click();"
                            aria-label="Close support"
                        >
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    @endif
                </div>
            </div>

            <div class="support-response-note">
                We typically reply within a few minutes.
            </div>

            <div class="support-header-wave"></div>
        </header>

        <main class="support-chat-body" id="clientSupportChatBody">
            @if ($conversation && $conversation->messages->count())
                @foreach ($conversation->messages as $message)
                    @php
                        $sender = $message->sender_type;
                    @endphp

                    @if ($sender === 'system')
                        <div class="support-system-row">
                            <span>{{ $message->message }}</span>
                        </div>
                    @elseif ($sender === 'client')
                        <div class="support-message-row support-message-client">
                            <div class="support-message-group">
                                @if (!empty($message->attachment_path))
                                    <button
                                        type="button"
                                        class="support-image-message support-image-message-client"
                                        data-support-image="{{ asset('storage/' . $message->attachment_path) }}"
                                        aria-label="Open sent image"
                                    >
                                        <img
                                            src="{{ asset('storage/' . $message->attachment_path) }}"
                                            alt="Sent image"
                                        >
                                    </button>
                                @endif

                                @if (!empty($message->message))
                                    <div class="support-bubble support-client-bubble">
                                        {{ $message->message }}
                                    </div>
                                @endif

                                <time>
                                    {{ $message->created_at->format('M d, h:i A') }}
                                </time>
                            </div>
                        </div>
                    @else
                        @php
                            $senderName = match ($sender) {
                                'hr' => 'HR Support',
                                'admin' => 'Admin Support',
                                default => 'Support Bot',
                            };

                            $senderIcon = match ($sender) {
                                'hr' => 'fa-user-tie',
                                'admin' => 'fa-user-shield',
                                default => 'fa-robot',
                            };
                        @endphp

                        <div class="support-message-row support-message-other">
                            <div class="support-message-avatar support-sender-{{ $sender }}">
                                <i class="fas {{ $senderIcon }}"></i>
                            </div>

                            <div class="support-message-group">
                                <span class="support-sender-name">
                                    {{ $senderName }}
                                </span>

                                @if (!empty($message->attachment_path))
                                    <button
                                        type="button"
                                        class="support-image-message support-image-message-other"
                                        data-support-image="{{ asset('storage/' . $message->attachment_path) }}"
                                        aria-label="Open sent image"
                                    >
                                        <img
                                            src="{{ asset('storage/' . $message->attachment_path) }}"
                                            alt="Sent image"
                                        >
                                    </button>
                                @endif

                                @if (!empty($message->message))
                                    <div class="support-bubble support-other-bubble">
                                        {{ $message->message }}
                                    </div>
                                @endif

                                <time>
                                    {{ $message->created_at->format('M d, h:i A') }}
                                </time>
                            </div>
                        </div>
                    @endif
                @endforeach
            @else
                <div class="support-message-row support-message-other support-intro-message">
                    <div class="support-message-avatar support-sender-bot">
                        <i class="fas fa-robot"></i>
                    </div>

                    <div class="support-message-group">
                        <span class="support-sender-name">Support Bot</span>

                        <div class="support-bubble support-other-bubble">
                            Hi there! I’m the WRPlumb support assistant. How can I help you today?
                        </div>
                    </div>
                </div>
            @endif

@if (!$conversation || !$conversation->messages->count())
    <div class="support-quick-actions">
        <button
            type="button"
            class="support-quick-button quick-fill"
            data-message="How do I request a quotation?"
        >
            Quotation Help
        </button>

        <button
            type="button"
            class="support-quick-button quick-fill"
            data-message="How do I track my service request?"
        >
            Track My Request
        </button>

        <button
            type="button"
            class="support-quick-button quick-fill"
            data-message="I have a concern about my invoice or payment."
        >
            Payment Concern
        </button>

        <button
            type="button"
            class="support-quick-button quick-fill"
            data-message="I need assistance from customer support."
        >
            Contact Support
        </button>
    </div>
@endif
</main>

        @if ($conversation)
            @if ($conversation->current_queue === 'hr')
                <div class="support-queue-note support-queue-hr">
                    <i class="fas fa-user-tie"></i>
                    Your concern is currently being handled by HR.
                </div>
            @elseif ($conversation->current_queue === 'admin')
                <div class="support-queue-note support-queue-admin">
                    <i class="fas fa-user-shield"></i>
                    Your concern is currently being handled by Admin.
                </div>
            @elseif ($conversation->status === 'resolved')
                <div class="support-queue-note support-queue-resolved">
                    <i class="fas fa-circle-check"></i>
                    This conversation has already been resolved.
                </div>
            @endif
        @endif

        <form
            method="POST"
            enctype="multipart/form-data"
            action="{{ $isWidget
                ? route('client.support.send', ['support_widget' => 1])
                : route('client.support.send') }}"
            class="support-composer"
            id="clientSupportForm"
        >
            @csrf

            @if ($isWidget)
                <input type="hidden" name="support_widget" value="1">
            @endif

            <div class="support-input-wrap">
                <div class="support-attachment-preview" id="supportAttachmentPreview" hidden>
                    <img id="supportAttachmentPreviewImage" src="" alt="Selected image preview">

                    <div class="support-attachment-preview-info">
                        <strong id="supportAttachmentPreviewName">Selected image</strong>
                        <span>Ready to send</span>
                    </div>

                    <button
                        type="button"
                        class="support-attachment-remove"
                        id="supportAttachmentRemove"
                        aria-label="Remove selected image"
                    >
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>

                <textarea
                    name="message"
                    id="clientSupportMessage"
                    rows="1"
                    placeholder="Type your message..."
                    maxlength="2000"
                ></textarea>

                <input
                    type="file"
                    name="image"
                    id="supportImageInput"
                    accept="image/jpeg,image/png,image/webp"
                    hidden
                >

                <div class="support-input-tools">
                    <button
                        type="button"
                        id="supportImageButton"
                        aria-label="Send an image"
                        title="Send image"
                    >
                        <i class="far fa-image"></i>
                    </button>

                    <div class="support-emoji-wrap">
                        <button
                            type="button"
                            id="supportEmojiButton"
                            aria-label="Emoji"
                            aria-expanded="false"
                            title="Emoji"
                        >
                            <i class="far fa-face-smile"></i>
                        </button>

                        <div class="support-emoji-picker" id="supportEmojiPicker" hidden>
                            @foreach (['😀','😃','😄','😁','😂','🤣','😊','😍','🥰','😎','🤔','😅','🥹','😭','😢','😡','👍','👎','👌','🙏','👏','🎉','❤️','💙'] as $emoji)
                                <button type="button" class="support-emoji-option" data-emoji="{{ $emoji }}">
                                    {{ $emoji }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <button
                type="submit"
                class="support-send-button"
                aria-label="Send message"
            >
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>

        <div class="support-brand-footer">
            <span>Powered by</span>
            <strong>WRPlumb</strong>
        </div>
    </div>
</div>

@if ($conversation)
    <form
        method="POST"
        action="{{ route('client.support.clear') }}"
        id="supportClearChatForm"
    >
        @csrf

        @if ($isWidget)
            <input type="hidden" name="support_widget" value="1">
        @endif
    </form>
@endif

<div class="support-confirm-modal" id="supportClearConfirmModal" hidden>
    <div class="support-confirm-backdrop" data-support-confirm-close></div>

    <div class="support-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="supportClearTitle">
        <div class="support-confirm-icon">
            <i class="fas fa-trash-can"></i>
        </div>

        <h3 id="supportClearTitle">Clear this conversation?</h3>
        <p>
            This removes the messages in your current support conversation. This action cannot be undone.
        </p>

        <div class="support-confirm-actions">
            <button type="button" class="support-confirm-btn secondary" data-support-confirm-close>
                Cancel
            </button>

            <button type="button" class="support-confirm-btn danger" id="supportConfirmClearButton">
                Clear Chat
            </button>
        </div>
    </div>
</div>

<div class="support-image-lightbox" id="supportImageLightbox" hidden>
    <div class="support-image-lightbox-backdrop" data-support-image-close></div>

    <div class="support-image-lightbox-dialog">
        <button
            type="button"
            class="support-image-lightbox-close"
            data-support-image-close
            aria-label="Close image preview"
        >
            <i class="fas fa-xmark"></i>
        </button>

        <img id="supportImageLightboxPreview" src="" alt="Chat image preview">
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const chatBody = document.getElementById('clientSupportChatBody');
        const input = document.getElementById('clientSupportMessage');
        const form = document.getElementById('clientSupportForm');

        const imageButton = document.getElementById('supportImageButton');
        const imageInput = document.getElementById('supportImageInput');
        const attachmentPreview = document.getElementById('supportAttachmentPreview');
        const attachmentPreviewImage = document.getElementById('supportAttachmentPreviewImage');
        const attachmentPreviewName = document.getElementById('supportAttachmentPreviewName');
        const attachmentRemove = document.getElementById('supportAttachmentRemove');

        const emojiButton = document.getElementById('supportEmojiButton');
        const emojiPicker = document.getElementById('supportEmojiPicker');

        const optionsToggle = document.getElementById('supportOptionsToggle');
        const optionsMenu = document.getElementById('supportOptionsMenu');
        const clearChatButton = document.getElementById('supportClearChatButton');
        const clearConfirmModal = document.getElementById('supportClearConfirmModal');
        const confirmClearButton = document.getElementById('supportConfirmClearButton');
        const clearForm = document.getElementById('supportClearChatForm');

        const imageLightbox = document.getElementById('supportImageLightbox');
        const imageLightboxPreview = document.getElementById('supportImageLightboxPreview');

        @if ($conversation && $conversation->messages->count())
            if (chatBody) {
                chatBody.scrollTop = chatBody.scrollHeight;
            }
        @else
            if (chatBody) {
                chatBody.scrollTop = 0;
            }
        @endif

        function resizeTextarea() {
            if (!input) return;

            input.style.height = 'auto';
            input.style.height = Math.min(input.scrollHeight, 110) + 'px';
        }

        function closeEmojiPicker() {
            if (!emojiPicker || !emojiButton) return;

            emojiPicker.hidden = true;
            emojiButton.setAttribute('aria-expanded', 'false');
        }

        function closeOptionsMenu() {
            if (!optionsMenu || !optionsToggle) return;

            optionsMenu.hidden = true;
            optionsToggle.setAttribute('aria-expanded', 'false');
        }

        function openConfirmModal() {
            if (!clearConfirmModal) return;

            clearConfirmModal.hidden = false;
            document.body.classList.add('support-modal-open');
        }

        function closeConfirmModal() {
            if (!clearConfirmModal) return;

            clearConfirmModal.hidden = true;
            document.body.classList.remove('support-modal-open');
        }

        function clearSelectedImage() {
            if (!imageInput) return;

            imageInput.value = '';

            if (attachmentPreview) {
                attachmentPreview.hidden = true;
            }

            if (attachmentPreviewImage) {
                attachmentPreviewImage.src = '';
            }

            if (attachmentPreviewName) {
                attachmentPreviewName.textContent = 'Selected image';
            }
        }

        if (input) {
            input.addEventListener('input', resizeTextarea);

            input.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();

                    const hasMessage = input.value.trim().length > 0;
                    const hasImage = imageInput && imageInput.files && imageInput.files.length > 0;

                    if (hasMessage || hasImage) {
                        form.requestSubmit();
                    }
                }
            });
        }

        document.querySelectorAll('.quick-fill').forEach(function (button) {
            button.addEventListener('click', function () {
                if (!input) return;

                input.value = this.dataset.message;
                resizeTextarea();
                input.focus();
            });
        });

        if (emojiButton && emojiPicker) {
            emojiButton.addEventListener('click', function (event) {
                event.stopPropagation();

                const willOpen = emojiPicker.hidden;
                closeOptionsMenu();

                emojiPicker.hidden = !willOpen;
                emojiButton.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            });

            document.querySelectorAll('.support-emoji-option').forEach(function (button) {
                button.addEventListener('click', function () {
                    if (!input) return;

                    const emoji = this.dataset.emoji || '';
                    const start = input.selectionStart ?? input.value.length;
                    const end = input.selectionEnd ?? input.value.length;

                    input.value =
                        input.value.slice(0, start) +
                        emoji +
                        input.value.slice(end);

                    const caret = start + emoji.length;
                    input.setSelectionRange(caret, caret);

                    resizeTextarea();
                    input.focus();
                });
            });
        }

        if (imageButton && imageInput) {
            imageButton.addEventListener('click', function () {
                closeEmojiPicker();
                closeOptionsMenu();
                imageInput.click();
            });

            imageInput.addEventListener('change', function () {
                const file = imageInput.files?.[0];

                if (!file) {
                    clearSelectedImage();
                    return;
                }

                if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                    alert('Please choose a JPG, PNG, or WEBP image.');
                    clearSelectedImage();
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    alert('The image must be 5 MB or smaller.');
                    clearSelectedImage();
                    return;
                }

                const previewUrl = URL.createObjectURL(file);

                if (attachmentPreviewImage) {
                    attachmentPreviewImage.src = previewUrl;
                }

                if (attachmentPreviewName) {
                    attachmentPreviewName.textContent = file.name;
                }

                if (attachmentPreview) {
                    attachmentPreview.hidden = false;
                }
            });
        }

        if (attachmentRemove) {
            attachmentRemove.addEventListener('click', clearSelectedImage);
        }

        if (optionsToggle && optionsMenu) {
            optionsToggle.addEventListener('click', function (event) {
                event.stopPropagation();

                const willOpen = optionsMenu.hidden;
                closeEmojiPicker();

                optionsMenu.hidden = !willOpen;
                optionsToggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            });
        }

        if (clearChatButton) {
            clearChatButton.addEventListener('click', function () {
                closeOptionsMenu();
                openConfirmModal();
            });
        }

        document.querySelectorAll('[data-support-confirm-close]').forEach(function (element) {
            element.addEventListener('click', closeConfirmModal);
        });

        if (confirmClearButton && clearForm) {
            confirmClearButton.addEventListener('click', function () {
                confirmClearButton.disabled = true;
                clearForm.submit();
            });
        }

        document.querySelectorAll('[data-support-image]').forEach(function (button) {
            button.addEventListener('click', function () {
                if (!imageLightbox || !imageLightboxPreview) return;

                imageLightboxPreview.src = button.dataset.supportImage || '';
                imageLightbox.hidden = false;
                document.body.classList.add('support-modal-open');
            });
        });

        document.querySelectorAll('[data-support-image-close]').forEach(function (element) {
            element.addEventListener('click', function () {
                if (!imageLightbox || !imageLightboxPreview) return;

                imageLightbox.hidden = true;
                imageLightboxPreview.src = '';
                document.body.classList.remove('support-modal-open');
            });
        });

        document.addEventListener('click', function (event) {
            if (emojiPicker && emojiButton && !emojiPicker.contains(event.target) && !emojiButton.contains(event.target)) {
                closeEmojiPicker();
            }

            if (optionsMenu && optionsToggle && !optionsMenu.contains(event.target) && !optionsToggle.contains(event.target)) {
                closeOptionsMenu();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') return;

            closeEmojiPicker();
            closeOptionsMenu();
            closeConfirmModal();

            if (imageLightbox && !imageLightbox.hidden) {
                imageLightbox.hidden = true;

                if (imageLightboxPreview) {
                    imageLightboxPreview.src = '';
                }

                document.body.classList.remove('support-modal-open');
            }
        });
    });
</script>
@endsection