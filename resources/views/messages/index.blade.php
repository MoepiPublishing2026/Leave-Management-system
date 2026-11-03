@extends((auth()->user()->role ?? null) === 'admin' ? 'layouts.admin' : 'layouts.manager')

@section('title', 'Messages')

@section('content')
<style>
    .messages-page-title {
        font-weight: 700;
        font-size: 32px;
        color: #263248;
        margin: 0 0 20px;
    }
    .messages-wrap {
        width: 100%;
        max-width: 1200px;
        height: 75vh;
        min-height: 500px;
        margin: 0 auto 40px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 1px solid #e2e8f0;
        overflow: hidden;
        display: grid;
        grid-template-columns: 340px 1fr; /* Wider sidebar */
    }
    
    /* ===== SIDEBAR ===== */
    .messages-sidebar {
        background: #fff;
        border-right: 2px solid #9ca3af; /* Thicker grey divider */
        display: flex;
        flex-direction: column;
    }
    .messages-sidebar-head {
        padding: 24px 24px 10px;
        font-size: 1.8rem; /* Large bold title */
        font-weight: 800;
        color: #1e293b;
        background: #fff;
    }
    
    /* Search Bar */
    .sidebar-search {
        padding: 10px 24px 20px;
        background: #fff;
    }
    .sidebar-search-wrap {
        display: flex;
        align-items: center;
        background: #cbd5e1; /* Darker grey background */
        border-radius: 8px;
        padding: 10px 14px;
        gap: 10px;
    }
    .sidebar-search-wrap svg {
        width: 18px;
        height: 18px;
        stroke: #475569;
    }
    .sidebar-search-wrap input {
        border: none;
        background: transparent;
        outline: none;
        width: 100%;
        font-size: 1rem;
        color: #334155;
    }

    .thread-list {
        overflow-y: auto;
        flex: 1;
        padding: 0 12px; /* Slight inset for the cards */
    }
    .thread {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 18px 16px;
        text-decoration: none;
        color: #1e293b;
        border-radius: 8px;
        transition: background-color 0.15s ease;
    }
    .thread:hover {
        background: #f1f5f9;
    }
    .thread.active {
        background: #cbd5e1; /* Grey highlight for active thread */
    }
    .thread-avatar {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: #a3e635; /* Bright lime green */
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 700;
        font-size: 1.1rem;
        flex-shrink: 0;
    }
    .thread-body { flex: 1; min-width: 0; }
    .thread-row-top { display: flex; justify-content: space-between; margin-bottom: 4px; }
    .thread-name { font-weight: 800; font-size: 1.1rem; color: #1e293b; }
    .thread-time { font-size: 0.85rem; color: #334155; font-weight: 600; }
    .thread-row-bottom { display: flex; justify-content: space-between; align-items: center; }
    .thread-preview { font-size: 0.95rem; color: #475569; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .thread-unread { background: #0ea5e9; color: #fff; font-size: 0.8rem; font-weight: 700; padding: 4px 8px; border-radius: 999px; }

    /* ===== MAIN CHAT AREA ===== */
    .messages-main {
        display: flex;
        flex-direction: column;
        background: #fff;
    }
    .messages-header {
        padding: 20px 30px;
        border-bottom: 2px solid #9ca3af; /* Match sidebar divider */
        display: flex;
        align-items: center;
        gap: 16px;
        background: #fff;
    }
    .messages-header-avatar {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: #a3e635;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 700;
        font-size: 1.2rem;
    }
    .messages-header-text h3 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 800;
        color: #1e293b;
    }
    .messages-header-text .meta {
        margin-top: 2px;
        font-size: 0.9rem;
        color: #475569;
        font-weight: 600;
    }

    /* Chat Bubbles */
    .messages-body {
        flex: 1;
        padding: 24px 30px;
        overflow-y: auto;
        background: #fff;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .messages-options-hint {
        text-align: center;
        font-size: 0.9rem;
        font-weight: 700;
        color: #64748b;
        background: #fef3c7; /* Light yellow */
        padding: 14px;
        border-radius: 8px;
        margin-bottom: 16px;
    }
    
    .bubble-row {
        display: flex;
        width: 100%;
    }
    .bubble-row.me { justify-content: flex-end; }
    .bubble-row:not(.me) { justify-content: flex-start; }
    
    .bubble-wrap {
        max-width: 80%;
    }
    .bubble {
        padding: 16px 20px;
        border-radius: 12px;
        font-size: 1rem;
        line-height: 1.5;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    /* Sent Message: Vibrant Blue */
    .bubble-row.me .bubble {
        background: #0ea5e9 !important; 
        color: #fff !important;
        border-bottom-right-radius: 4px;
    }
    /* Received Message: Light grey */
    .bubble-row:not(.me) .bubble {
        background: #e2e8f0 !important;
        color: #1e293b !important;
        border-bottom-left-radius: 4px;
    }
    
    .bubble-meta {
        display: block;
        text-align: right;
        font-size: 0.8rem;
        margin-top: 6px;
        opacity: 0.9;
        font-weight: 600;
    }

    /* ===== COMPOSE BAR ===== */
    .messages-compose {
        padding: 20px 30px;
        background: #fff;
        border-top: 2px solid #9ca3af; /* Match divider */
    }
    .compose-form {
        display: flex;
        width: 100%;
        gap: 16px;
        align-items: center;
    }
    
    /* Voice Button */
    .compose-voice-btn {
        background: #e8f5e9 !important; /* Very light mint green */
        border: 1px solid #c3e0c8 !important;
        border-radius: 24px !important;
        height: 48px !important;
        padding: 0 24px !important;
        font-weight: 800 !important;
        font-size: 1rem !important;
        color: #4d8c57 !important;
        cursor: pointer;
    }
    
    /* Type a message Input */
    .compose-form textarea {
        flex: 1;
        background: #e8f5e9 !important; /* Very light mint green */
        border: none !important;
        border-radius: 24px !important;
        padding: 14px 24px !important;
        font-size: 1rem !important;
        resize: none;
        min-height: 48px !important;
        outline: none;
        color: #334155;
    }
    
    /* Send Button */
    .compose-form button[type="submit"] {
        background: #a3e635 !important; /* Bright lime green */
        color: #fff !important;
        border: none !important;
        border-radius: 50% !important;
        width: 48px !important;
        height: 48px !important;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(163, 230, 53, 0.4) !important;
    }

        /* Final touches to align the dividers */
    .messages-sidebar {
        border-right: 2px solid #94a3b8 !important; 
    }
    .messages-header {
        border-bottom: 2px solid #94a3b8 !important;
    }
    .messages-compose {
        border-top: 2px solid #94a3b8 !important;
    }
    
    /* Make the search bar slightly lighter */
    .sidebar-search-wrap {
        background: #cbd5e1 !important;
        border: 1px solid #94a3b8 !important;
    }


        /* ===== FINAL FIXES ===== */
    
    /* 1. Remove the grey box and border from the active/open chat in the sidebar */
    .thread.active {
        background: #ffffff !important;
        border-left: none !important;
        box-shadow: none !important;
    }

    /* 2. Remove the green outline/box around the search input */
    .sidebar-search-wrap {
        border: none !important;
    }
    .sidebar-search-wrap input {
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
        background: transparent !important;
    }
    .sidebar-search-wrap:focus-within {
        outline: none !important;
        box-shadow: none !important;
    }

    /* 3. Make both profile avatars the exact Lime Green */
    .thread-avatar, 
    .messages-header-avatar {
        background-color: #c0d72a !important; 
        color: #ffffff !important;
    }

    /* 4. Hide the tiny blue back arrow */
    .mobile-back {
        display: none !important;
    }

    /* 5. Style the Voice button text and input container */
    .compose-voice-btn {
        color: #c0d72a !important; /* Lime green text */
        background: #f0f9e8 !important; /* Very light mint background */
        border: none !important;
        font-weight: 800 !important;
    }
    .compose-form textarea {
        background: #f0f9e8 !important; /* Very light mint background */
    }

    /* 6. Make the Send button lime green and point North-East */
    .compose-form button[type="submit"] {
        background-color: #c0d72a !important; /* Lime green */
        color: #ffffff !important;
        transform: rotate(45deg) !important; /* Rotates the arrow */
        width: 46px !important;
        height: 46px !important;
        border-radius: 50% !important;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none !important;
    }
    /* Keep the arrow icon itself un-rotated so it renders correctly inside the circle */
    .compose-form button[type="submit"] span,
    .compose-form button[type="submit"] svg {
        transform: rotate(  45deg) !important; 
    }

</style>

@php
    $isAdmin = (auth()->user()->role ?? null) === 'admin';
    $meId = auth()->id();

    $threadTime = function ($date) {
        if (!$date) {
            return '';
        }
        $c = \Carbon\Carbon::parse($date);
        if ($c->isToday()) {
            return $c->format('H:i');
        }
        if ($c->isYesterday()) {
            return 'Yesterday';
        }
        if ($c->isCurrentWeek()) {
            return $c->format('l');
        }
        return $c->format('d/m/y');
    };

    $userInitials = function ($user) {
        if (!$user) {
            return '?';
        }
        $a = strtoupper(substr((string) ($user->first_name ?? ''), 0, 1));
        $b = strtoupper(substr((string) ($user->last_name ?? ''), 0, 1));
        $init = trim($a . $b);
        return $init !== '' ? $init : '?';
    };
@endphp

<h1 class="messages-page-title">Messages</h1>

<div class="messages-wrap {{ $activeConversation ? 'is-mobile-thread' : '' }}">
    <aside class="messages-sidebar" aria-label="Chats">
        <div class="messages-sidebar-head">Chats</div>
        <!-- NEW SEARCH BAR -->
        <div class="sidebar-search">
            <div class="sidebar-search-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" placeholder="Search conversations...">
            </div>
        </div>
        <div class="thread-list">
            @forelse($conversations as $c)
                @php
                    $otherUser = $isAdmin ? $c->manager : $c->admin;
                    $otherName = trim(($otherUser->first_name ?? '') . ' ' . ($otherUser->last_name ?? '')) ?: ($isAdmin ? 'Manager' : 'Senior Manager');
                    $lastMessage = $c->messages->first();
                    if ($lastMessage) {
                        if ($lastMessage->deleted_for_everyone_at) {
                            $label = 'This message was deleted';
                        } elseif (!empty($lastMessage->voice_path)) {
                            $label = 'Voice message';
                        } else {
                            $label = $lastMessage->body;
                        }
                        $preview = ($lastMessage->sender_id === $meId ? 'You: ' : '') . $label;
                    } else {
                        $preview = 'No messages yet';
                    }
                @endphp
                <a class="thread {{ ($activeConversation?->id) === $c->id ? 'active' : '' }}"
                   href="{{ route('messages.index', ['conversation' => $c->id]) }}">
                    <div class="thread-avatar" aria-hidden="true">
                        @if(!empty($otherUser?->profile_picture))
                            <img src="{{ route('users.profile_picture', ['user' => $otherUser->id]) }}?v={{ optional($otherUser->updated_at)->timestamp }}" alt="">
                        @else
                            <span class="thread-avatar-fallback">{{ $userInitials($otherUser) }}</span>
                        @endif
                    </div>
                    <div class="thread-body">
                        <div class="thread-row-top">
                            <span class="thread-name">{{ $otherName }}</span>
                            <span class="thread-time">{{ $threadTime($c->updated_at) }}</span>
                        </div>
                        <div class="thread-row-bottom">
                            <span class="thread-preview">{{ $preview }}</span>
                            @if(($c->unread_count ?? 0) > 0)
                                <span class="thread-unread">{{ $c->unread_count }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="messages-empty">No chats yet</div>
            @endforelse
        </div>
    </aside>

    <section class="messages-main" aria-label="Conversation">
        @if(!$activeConversation)
            <div class="messages-empty-pane">Select a chat to view messages</div>
        @else
            @php
                $employee = $activeConversation->leaveRequest?->user;
                $employeeName = trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')) ?: 'Employee';
                $otherUser = $isAdmin ? $activeConversation->manager : $activeConversation->admin;
                $otherName = trim(($otherUser->first_name ?? '') . ' ' . ($otherUser->last_name ?? '')) ?: ($isAdmin ? 'Manager' : 'Senior Manager');
                $leave = $activeConversation->leaveRequest;
            @endphp
            <header class="messages-header">
                <a class="mobile-back" href="{{ route('messages.index') }}" aria-label="Back">←</a>
                <div class="messages-header-avatar" aria-hidden="true">
                    @if(!empty($otherUser?->profile_picture))
                        <img src="{{ route('users.profile_picture', ['user' => $otherUser->id]) }}?v={{ optional($otherUser->updated_at)->timestamp }}" alt="">
                    @else
                        <span>{{ $userInitials($otherUser) }}</span>
                    @endif
                </div>
                <div class="messages-header-text">
                    <h3>{{ $otherName }}</h3>
                    <div class="meta">
                        Leave #{{ $activeConversation->leave_request_id }}
                        · {{ $employeeName }}
                        · {{ $leave?->leave_type ?? 'Leave' }}
                        @if($leave?->start_date && $leave?->end_date)
                            · {{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }} – {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}
                        @endif
                        @if($leave?->status)
                            · {{ ucfirst($leave->status) }}
                        @endif
                    </div>
                </div>
            </header>

            <div class="messages-body" id="messagesBody">
                @if($activeMessages->isNotEmpty())
                    <p class="messages-options-hint">Click any message — options appear below</p>
                @endif
                @forelse($activeMessages as $m)
                    @php
                        $isMe = $m->sender_id === $meId;
                        $isDeleted = $m->isDeletedForEveryone();
                        $replySender = $m->replyTo?->sender;
                        $replySenderName = $replySender
                            ? (trim(($replySender->first_name ?? '') . ' ' . ($replySender->last_name ?? '')) ?: 'User')
                            : 'User';
                        $copyText = $m->copyableText();
                        $senderName = trim(($m->sender->first_name ?? '') . ' ' . ($m->sender->last_name ?? '')) ?: 'User';
                    @endphp
                    <div class="bubble-row {{ $isMe ? 'me' : '' }}">
                        <div class="bubble-wrap">
                            <div class="bubble {{ $isDeleted ? 'is-deleted' : '' }}"
                                 data-message-id="{{ $m->id }}"
                                 data-copy-text="{{ $copyText }}"
                                 data-is-mine="{{ $isMe ? '1' : '0' }}"
                                 data-is-deleted="{{ $isDeleted ? '1' : '0' }}"
                                 data-sender-name="{{ $senderName }}"
                                 title="Click for message options">
                            @if($m->replyTo)
                                <div class="bubble-reply">
                                    <span class="bubble-reply-name">{{ $replySenderName }}</span>
                                    <span class="bubble-reply-text">{{ $m->replyTo->displayBody() }}</span>
                                </div>
                            @endif
                            @if(!empty($m->voice_path) && !$isDeleted)
                                <audio controls preload="metadata" src="{{ route('messages.voice', ['message' => $m->id]) }}"></audio>
                                @if(trim((string) $m->body) !== '' && strcasecmp(trim((string) $m->body), 'Voice message') !== 0)
                                    <div class="bubble-line">
                                        <span class="bubble-text">{{ $m->body }}</span>
                                    </div>
                                @endif
                                <div class="bubble-voice-meta">
                                    <span class="bubble-meta">
                                        {{ optional($m->created_at)->format('H:i') }}
                                        @if ($isMe)
                                            @if ($m->read_at)
                                                <span class="ticks blue" title="Read"> ✓✓</span>
                                            @elseif ($m->delivered_at)
                                                <span class="ticks gray" title="Delivered"> ✓✓</span>
                                            @else
                                                <span class="ticks gray" title="Sent"> ✓</span>
                                            @endif
                                        @endif
                                    </span>
                                </div>
                            @else
                                <div class="bubble-line">
                                    <span class="bubble-text">{{ $m->displayBody() }}</span>
                                    <span class="bubble-meta">
                                        {{ optional($m->created_at)->format('H:i') }}
                                        @if ($isMe)
                                            @if ($m->read_at)
                                                <span class="ticks blue" title="Read"> ✓✓</span>
                                            @elseif ($m->delivered_at)
                                                <span class="ticks gray" title="Delivered"> ✓✓</span>
                                            @else
                                                <span class="ticks gray" title="Sent"> ✓</span>
                                            @endif
                                        @endif
                                    </span>
                                </div>
                            @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="messages-empty">No messages yet</div>
                @endforelse
            </div>

            <div class="messages-compose">
                <div class="message-actions-bar" id="messageActionsBar" hidden>
                    <span class="message-actions-label">Message options</span>
                    <button type="button" data-action="reply">Reply</button>
                    <button type="button" data-action="copy">Copy</button>
                    <button type="button" data-action="forward">Forward</button>
                    <button type="button" data-action="delete-me" class="danger">Delete for me</button>
                    <button type="button" data-action="delete-everyone" class="danger" hidden>Delete for everyone</button>
                    <button type="button" class="close-btn" id="clearMessageSelection" aria-label="Close">✕</button>
                </div>
                <div class="reply-bar" id="replyBar" hidden>
                    <div class="reply-bar-text">
                        <strong id="replyBarName"></strong>
                        <span id="replyBarPreview"></span>
                    </div>
                    <button type="button" class="reply-bar-cancel" id="cancelReply" aria-label="Cancel reply">×</button>
                </div>
                <form class="compose-form" id="composeForm" method="POST" action="{{ route('messages.send', ['conversation' => $activeConversation->id]) }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="reply_to_message_id" id="replyToInput" value="">
                    <input type="file" name="voice" id="voiceInput" accept="audio/*" hidden>
                    <button type="button" class="compose-voice-btn" id="voiceRecordBtn" aria-label="Record voice note" title="Tap to record a voice note"><span id="voiceRecordLabel">Voice</span></button>
                    <textarea name="body" id="composeBody" placeholder="Type a message" rows="1">{{ old('body') }}</textarea>
                    <button type="submit" aria-label="Send">➤</button>
                </form>
                <div class="voice-status" id="voiceStatus" hidden></div>
                @error('body')
                    <div class="compose-error">{{ $message }}</div>
                @enderror
                @error('voice')
                    <div class="compose-error">{{ $message }}</div>
                @enderror
            </div>
        @endif
    </section>
</div>

@if($activeConversation)
    <form id="deleteMessageForm" method="POST" action="" hidden>
        @csrf
        @method('DELETE')
        <input type="hidden" name="scope" id="deleteScopeInput" value="me">
    </form>

    <div id="forwardModalBackdrop" class="forward-modal-backdrop" hidden>
        <div class="forward-modal" role="dialog" aria-labelledby="forwardModalTitle">
            <h4 id="forwardModalTitle">Forward message</h4>
            <form id="forwardForm" method="POST" action="">
                @csrf
                <select name="target_conversation_id" id="forwardTargetSelect" required>
                    <option value="">Choose a chat</option>
                    @foreach($forwardConversations as $fc)
                        @php
                            $fcOther = $isAdmin ? $fc->manager : $fc->admin;
                            $fcOtherName = trim(($fcOther->first_name ?? '') . ' ' . ($fcOther->last_name ?? '')) ?: ($isAdmin ? 'Manager' : 'Senior Manager');
                            $fcEmployee = $fc->leaveRequest?->user;
                            $fcEmployeeName = trim(($fcEmployee->first_name ?? '') . ' ' . ($fcEmployee->last_name ?? '')) ?: 'Employee';
                        @endphp
                        <option value="{{ $fc->id }}">{{ $fcOtherName }} · Leave #{{ $fc->leave_request_id }} · {{ $fcEmployeeName }}</option>
                    @endforeach
                </select>
                <div class="forward-modal-actions">
                    <button type="button" id="forwardCancelBtn">Cancel</button>
                    <button type="submit" class="primary">Forward</button>
                </div>
            </form>
            @if($forwardConversations->isEmpty())
                <p style="margin:0;font-size:0.88rem;color:#64748b;">You have no other chats to forward to.</p>
            @endif
        </div>
    </div>
@endif

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const box = document.getElementById('messagesBody');
        if (box) box.scrollTop = box.scrollHeight;

        const form = document.getElementById('composeForm');
        const voiceBtn = document.getElementById('voiceRecordBtn');
        const voiceInput = document.getElementById('voiceInput');
        const voiceStatus = document.getElementById('voiceStatus');
        const voiceRecordLabel = document.getElementById('voiceRecordLabel');
        const messageActionsBar = document.getElementById('messageActionsBar');
        const deleteMessageForm = document.getElementById('deleteMessageForm');
        const forwardModalBackdrop = document.getElementById('forwardModalBackdrop');
        const forwardForm = document.getElementById('forwardForm');
        const replyBar = document.getElementById('replyBar');
        const replyBarName = document.getElementById('replyBarName');
        const replyBarPreview = document.getElementById('replyBarPreview');
        const replyToInput = document.getElementById('replyToInput');
        const cancelReply = document.getElementById('cancelReply');
        const composeBody = document.getElementById('composeBody');
        const clearMessageSelection = document.getElementById('clearMessageSelection');

        let activeBubble = null;

        const closeActions = () => {
            if (messageActionsBar) messageActionsBar.hidden = true;
            document.querySelectorAll('.bubble.is-selected').forEach((el) => el.classList.remove('is-selected'));
            activeBubble = null;
        };

        const openActions = (bubble) => {
            if (!messageActionsBar || !bubble) return;
            document.querySelectorAll('.bubble.is-selected').forEach((el) => el.classList.remove('is-selected'));
            activeBubble = bubble;
            bubble.classList.add('is-selected');

            const isMine = bubble.dataset.isMine === '1';
            const isDeleted = bubble.dataset.isDeleted === '1';
            const deleteEveryoneBtn = messageActionsBar.querySelector('[data-action="delete-everyone"]');
            const forwardBtn = messageActionsBar.querySelector('[data-action="forward"]');
            const copyBtn = messageActionsBar.querySelector('[data-action="copy"]');
            const replyBtn = messageActionsBar.querySelector('[data-action="reply"]');

            if (deleteEveryoneBtn) deleteEveryoneBtn.hidden = !isMine || isDeleted;
            if (forwardBtn) forwardBtn.hidden = isDeleted;
            if (copyBtn) copyBtn.hidden = isDeleted || !bubble.dataset.copyText;
            if (replyBtn) replyBtn.hidden = isDeleted;

            messageActionsBar.hidden = false;
            messageActionsBar.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        };

        const clearReply = () => {
            if (replyBar) replyBar.hidden = true;
            if (replyToInput) replyToInput.value = '';
            if (replyBarName) replyBarName.textContent = '';
            if (replyBarPreview) replyBarPreview.textContent = '';
        };

        const startReply = (bubble) => {
            if (!bubble || bubble.dataset.isDeleted === '1') return;
            const preview = bubble.querySelector('.bubble-text')?.textContent?.trim()
                || (bubble.querySelector('audio') ? 'Voice message' : '');
            if (replyBar) replyBar.hidden = false;
            if (replyBarName) replyBarName.textContent = bubble.dataset.senderName || 'User';
            if (replyBarPreview) replyBarPreview.textContent = preview;
            if (replyToInput) replyToInput.value = bubble.dataset.messageId || '';
            composeBody?.focus();
            closeActions();
        };

        document.querySelectorAll('.bubble').forEach((bubble) => {
            bubble.addEventListener('click', (event) => {
                if (event.target.closest('audio')) return;
                event.preventDefault();
                if (activeBubble === bubble && !messageActionsBar?.hidden) {
                    closeActions();
                    return;
                }
                openActions(bubble);
            });

            bubble.addEventListener('contextmenu', (event) => {
                if (event.target.closest('audio')) return;
                event.preventDefault();
                openActions(bubble);
            });
        });

        messageActionsBar?.addEventListener('click', async (event) => {
            const button = event.target.closest('button[data-action]');
            if (!button || !activeBubble) return;

            const action = button.dataset.action;
            const messageId = activeBubble.dataset.messageId;
            const copyText = activeBubble.dataset.copyText || '';

            if (action === 'reply') {
                startReply(activeBubble);
                return;
            }

            if (action === 'copy') {
                try {
                    await navigator.clipboard.writeText(copyText);
                } catch (error) {
                    const helper = document.createElement('textarea');
                    helper.value = copyText;
                    document.body.appendChild(helper);
                    helper.select();
                    document.execCommand('copy');
                    helper.remove();
                }
                closeActions();
                return;
            }

            if (action === 'forward') {
                if (forwardForm && forwardModalBackdrop) {
                    forwardForm.action = `{{ url('/messages') }}/${messageId}/forward`;
                    forwardModalBackdrop.hidden = false;
                }
                closeActions();
                return;
            }

            if (action === 'delete-me' || action === 'delete-everyone') {
                if (deleteMessageForm) {
                    deleteMessageForm.action = `{{ url('/messages') }}/${messageId}`;
                    const scopeInput = document.getElementById('deleteScopeInput');
                    if (scopeInput) {
                        scopeInput.value = action === 'delete-everyone' ? 'everyone' : 'me';
                    }
                    deleteMessageForm.submit();
                }
                closeActions();
            }
        });

        clearMessageSelection?.addEventListener('click', closeActions);

        cancelReply?.addEventListener('click', clearReply);

        document.getElementById('forwardCancelBtn')?.addEventListener('click', () => {
            if (forwardModalBackdrop) forwardModalBackdrop.hidden = true;
        });

        forwardModalBackdrop?.addEventListener('click', (event) => {
            if (event.target === forwardModalBackdrop) {
                forwardModalBackdrop.hidden = true;
            }
        });

        if (!form || !voiceBtn || !voiceInput) return;

        let mediaRecorder = null;
        let mediaStream = null;
        let chunks = [];
        let isRecording = false;

        const stopStream = () => {
            if (mediaStream) {
                mediaStream.getTracks().forEach((track) => track.stop());
                mediaStream = null;
            }
        };

        voiceBtn.addEventListener('click', async () => {
            if (isRecording) {
                mediaRecorder?.stop();
                return;
            }

            if (!navigator.mediaDevices?.getUserMedia) {
                alert('Voice notes are not supported in this browser.');
                return;
            }

            try {
                mediaStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                const mimeType = MediaRecorder.isTypeSupported('audio/webm;codecs=opus')
                    ? 'audio/webm;codecs=opus'
                    : (MediaRecorder.isTypeSupported('audio/webm') ? 'audio/webm' : '');

                mediaRecorder = mimeType
                    ? new MediaRecorder(mediaStream, { mimeType })
                    : new MediaRecorder(mediaStream);

                chunks = [];
                mediaRecorder.ondataavailable = (event) => {
                    if (event.data.size > 0) {
                        chunks.push(event.data);
                    }
                };

                mediaRecorder.onstop = () => {
                    stopStream();
                    voiceBtn.classList.remove('is-recording');
                    if (voiceRecordLabel) voiceRecordLabel.textContent = 'Voice';
                    voiceStatus.hidden = true;
                    voiceStatus.classList.remove('is-recording');
                    isRecording = false;

                    if (!chunks.length) {
                        return;
                    }

                    const type = mediaRecorder.mimeType || 'audio/webm';
                    const blob = new Blob(chunks, { type });
                    const ext = type.includes('ogg') ? 'ogg' : 'webm';
                    const file = new File([blob], `voice-note.${ext}`, { type });

                    const transfer = new DataTransfer();
                    transfer.items.add(file);
                    voiceInput.files = transfer.files;
                    form.submit();
                };

                mediaRecorder.start();
                isRecording = true;
                voiceBtn.classList.add('is-recording');
                if (voiceRecordLabel) voiceRecordLabel.textContent = 'Stop';
                voiceStatus.hidden = false;
                voiceStatus.classList.add('is-recording');
                voiceStatus.textContent = 'Recording… Tap the microphone again to stop and send';
            } catch (error) {
                stopStream();
                alert('Microphone access was denied or unavailable.');
            }
        });
    });
</script>
@endsection
