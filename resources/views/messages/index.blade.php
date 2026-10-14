@extends((auth()->user()->role ?? null) === 'admin' ? 'layouts.admin' : 'layouts.manager')

@section('title', 'Messages')

@section('content')
<style>
    .messages-page-title {
        font-weight: 700;
        font-size: clamp(24px, 3vw, 34px);
        color: #273548;
        margin: 0 0 20px;
        letter-spacing: 0;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }
    .messages-wrap {
        width: 100%;
        max-width: 1200px;
        height: clamp(400px, 65vh, 750px);
        min-height: 400px;
        margin: 0 auto 40px;
        background: #fff;
        border-radius: 12px;
        box-shadow: none;
        overflow: hidden;
        display: grid;
        box-sizing: border-box;
        grid-template-columns: minmax(220px, 30%) minmax(0, 1fr);
        border: none;
    }
    .messages-sidebar {
        background: #fffaf0;
        border-right: 3px solid #858585;
        display: flex;
        flex-direction: column;
        min-height: 0;
        min-width: 0;
        box-sizing: border-box;
    }
    .messages-sidebar-head {
        padding: clamp(12px, 2vw, 20px);
        font-size: 1.05rem;
        font-weight: 800;
        color: #273548;
        text-align: center;
        border-bottom: 1px solid #b8b4aa;
        background: #fffaf0;
        letter-spacing: 0.02em;
    }
    .thread-list {
        overflow-y: auto;
        flex: 1;
    }
    .thread {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        text-decoration: none;
        color: inherit;
        border-bottom: 1px solid #b8b4aa;
        transition: background-color 0.12s ease;
    }
    .thread:hover {
        background: #f4f0e6;
    }
    .thread.active {
        background: #fffaf0;
        box-shadow: inset 3px 0 0 #a0b51b;
    }
    .thread-avatar {
        width: 40px;
        height: 36px;
        border-radius: 50%;
        flex-shrink: 0;
        overflow: hidden;
        background: #a0b51b;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: none;
    }
    .thread-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .thread-avatar-fallback {
        font-size: 0.95rem;
        font-weight: 800;
        color: #273548;
        letter-spacing: 0.02em;
    }
    .thread-body {
        flex: 1;
        min-width: 0;
    }
    .thread-row-top {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 4px;
    }
    .thread-name {
        font-weight: 800;
        font-size: 0.95rem;
        color: #273548;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        letter-spacing: 0.01em;
    }
    .thread-time {
        font-size: 0.58rem;
        color: #4b5563;
        flex-shrink: 0;
        font-weight: 600;
    }
    .thread-row-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }
    .thread-preview {
        font-size: 0.58rem;
        color: #555;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        flex: 1;
        min-width: 0;
        font-weight: 600;
    }
    .thread-unread {
        flex-shrink: 0;
        min-width: 16px;
        height: 16px;
        padding: 0 4px;
        border-radius: 999px;
        background: #80bce0;
        color: #fff;
        font-size: 0.65rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .messages-main {
        display: flex;
        flex-direction: column;
        min-width: 0;
        min-height: 0;
        overflow: hidden;
        background: #fffaf0;
    }
    .messages-header {
        border-bottom: 1px solid #b8b4aa;
        padding: 10px 12px;
        display: flex;
        align-items: center;
        gap: 12px;
        background: #fffaf0;
        box-shadow: none;
    }
    .messages-header-avatar {
        width: 40px;
        height: 36px;
        border-radius: 50%;
        flex-shrink: 0;
        overflow: hidden;
        background: #a0b51b;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .messages-header-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .messages-header-avatar span {
        font-size: 0.85rem;
        font-weight: 800;
        color: #273548;
    }
    .messages-header-text h3 {
        margin: 0;
        font-size: 0.85rem;
        font-weight: 800;
        color: #273548;
        letter-spacing: 0.01em;
        line-height: 1.25;
    }
    .messages-header-text .meta {
        margin-top: 4px;
        color: #555;
        font-size: 0.58rem;
        font-weight: 600;
        line-height: 1.45;
        white-space: normal;
    }
    .messages-body {
        flex: 1;
        padding: 10px 14px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 6px;
        background: #fffaf0;
    }
    
    .bubble-row {
        display: flex;
        width: 100%;
        align-items: flex-end;
    }
    .bubble-row.me {
        justify-content: flex-end;
    }
    .bubble-row:not(.me) {
        justify-content: flex-start;
    }
    .bubble-wrap {
        display: flex;
        flex-direction: column;
        width: max-content;
        max-width: min(24rem, 85%);
        min-width: 0;
    }
    .bubble-row.me .bubble-wrap {
        align-items: stretch;
        margin-left: auto;
    }
    .bubble-row:not(.me) .bubble-wrap {
        align-items: stretch;
        margin-right: auto;
    }
    .bubble {
        display: block;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        box-sizing: border-box;
        padding: 8px 10px 5px;
        border-radius: 5px;
        background: #e9f2f4;
        color: #273548;
        font-size: 13px;
        line-height: 1.45;
        overflow-wrap: anywhere;
        word-break: break-word;
        box-shadow: none;
        cursor: pointer;
        transition: outline-color 0.12s ease, box-shadow 0.12s ease;
    }
    .bubble-line {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 4px;
        min-width: 0;
        max-width: 100%;
    }
    .bubble-text {
        display: block;
        min-width: 0;
        max-width: 100%;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    .bubble-row.me .bubble {
        background: #80bce0;
        color: #fff;
        box-shadow: none;
    }
    .bubble-meta {
        align-self: flex-end;
        font-size: 0.58rem;
        font-weight: 700;
        color: #667781;
        white-space: nowrap;
        line-height: 1.3;
        padding-top: 2px;
    }
    .bubble-row.me .bubble-meta {
        color: #fff;
    }
    .ticks.gray { color: rgba(255, 255, 255, 0.75); }
    .ticks.blue { color: #fff; }
    .bubble:has(audio) {
        min-width: 248px;
    }
    .bubble audio {
        width: 220px;
        height: 36px;
        margin-bottom: 4px;
        display: block;
    }
    .bubble-voice-meta {
        display: flex;
        justify-content: flex-end;
        margin-top: 4px;
        padding-top: 2px;
    }
    .bubble.is-deleted .bubble-text {
        font-style: italic;
        opacity: 0.85;
    }
    .bubble-reply {
        border-left: 3px solid #0f766e;
        padding: 6px 10px;
        margin-bottom: 8px;
        border-radius: 6px;
        background: rgba(0, 0, 0, 0.06);
        max-width: 100%;
    }
    .bubble-row.me .bubble-reply {
        border-left-color: rgba(255, 255, 255, 0.85);
        background: rgba(255, 255, 255, 0.16);
    }
    .bubble-reply-name {
        display: block;
        font-size: 0.72rem;
        font-weight: 800;
        color: #0f766e;
        margin-bottom: 2px;
    }
    .bubble-row.me .bubble-reply-name {
        color: rgba(255, 255, 255, 0.95);
    }
    .bubble-reply-text {
        display: block;
        font-size: 0.82rem;
        opacity: 0.9;
        min-width: 0;
        max-width: 100%;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    .bubble:hover {
        box-shadow: 0 2px 6px rgba(39, 53, 72, 0.12);
    }
    .bubble.is-selected {
        outline: 2px solid #a0b51b;
        outline-offset: 2px;
    }
    .messages-options-hint {
        margin: 0 0 8px;
        margin: 0 0 8px;
        padding: 10px 12px;
        border-radius: 7px;
        background: #faf5e9;
        color: #273548;
        font-size: 0.72rem;
        font-weight: 800;
        text-align: center;
        letter-spacing: 0;
    }
    .message-actions-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        padding: 12px 14px;
        margin-bottom: 10px;
        border-radius: 10px;
        background: #233138;
        color: #e9edef;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.18);
    }
    .message-actions-bar[hidden] {
        display: none !important;
    }
    .message-actions-label {
        flex: 1 1 100%;
        font-size: 0.8rem;
        font-weight: 700;
        color: rgba(255, 255, 255, 0.75);
        margin-bottom: 2px;
    }
    .message-actions-bar button {
        padding: 9px 14px;
        border: none;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
        font: inherit;
        font-size: 0.88rem;
        font-weight: 700;
        cursor: pointer;
        white-space: nowrap;
    }
    .message-actions-bar button:hover {
        background: rgba(255, 255, 255, 0.22);
    }
    .message-actions-bar button.danger {
        background: rgba(220, 38, 38, 0.35);
    }
    .message-actions-bar button.danger:hover {
        background: rgba(220, 38, 38, 0.5);
    }
    .message-actions-bar button.close-btn {
        margin-left: auto;
        background: transparent;
        font-size: 1.1rem;
        padding: 6px 10px;
    }
    .reply-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        margin-bottom: 8px;
        border-left: 4px solid #0f766e;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.75);
    }
    .reply-bar-text {
        flex: 1;
        min-width: 0;
    }
    .reply-bar-text strong {
        display: block;
        font-size: 0.8rem;
        color: #0f766e;
        margin-bottom: 2px;
    }
    .reply-bar-text span {
        display: block;
        font-size: 0.85rem;
        color: #334155;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .reply-bar-cancel {
        border: none;
        background: transparent;
        font-size: 1.25rem;
        line-height: 1;
        color: #64748b;
        cursor: pointer;
        padding: 4px;
    }
    .forward-modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 10040;
        background: rgba(2, 10, 14, 0.88);
        border: 3px solid #0f766e;
        box-shadow: inset 0 0 0 2px #0f766e;
        box-sizing: border-box;
        -webkit-backdrop-filter: blur(4px);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .forward-modal {
        width: min(420px, 100%);
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        border: none;
        outline: none;
        box-shadow: 0 28px 70px rgba(0, 0, 0, 0.58);
    }
    .forward-modal h4 {
        margin: 0 0 12px;
        font-size: 1.05rem;
        color: #0a1347;
    }
    .forward-modal select {
        width: 100%;
        padding: 10px 12px;
        border-radius: 8px;
        border: 1px solid #d1d5db;
        font: inherit;
        margin-bottom: 14px;
    }
    .forward-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    .forward-modal-actions button {
        border-radius: 8px;
        padding: 8px 14px;
        font-weight: 700;
        cursor: pointer;
        border: 1px solid #d1d5db;
        background: #fff;
    }
    .forward-modal-actions button.primary {
        background: #b2cd16;
        border-color: #91bf14;
        color: #fff;
    }
    .messages-compose {
        display: block;
        flex: 0 0 auto;
        width: 100%;
        box-sizing: border-box;
        border-top: 1px solid #e5dfd2;
        padding: 8px 16px 10px;
        background: #fffaf0;
        position: relative;
        z-index: 2;
    }
    .compose-form {
        display: flex;
        gap: 12px;
        align-items: center;
    }
    .compose-form textarea {
        flex: 1;
        min-height: 30px;
        max-height: 120px;
        resize: none;
        padding: 7px 32px;
        border-radius: 24px;
        border: none;
        background: #e7f1f2;
        font-family: inherit;
        font-size: 14px;
        outline: none;
        box-shadow: none;
    }
    .compose-form textarea:focus {
        border-color: #a0b51b;
        box-shadow: 0 0 0 2px rgba(160, 181, 27, 0.15);
    }
    .compose-form button[type="submit"] {
        width: 36px;
        height: 30px;
        padding: 0;
        border-radius: 50%;
        border: none;
        font-weight: 700;
        font-size: 0.9rem;
        line-height: 1;
        background: #a0b51b;
        color: #fff;
        cursor: pointer;
        box-shadow: none;
    }
    .compose-form button[type="submit"]:hover {
        background: #8d9f16;
    }
    .compose-voice-btn {
        width: auto;
        min-width: 48px;
        height: 30px;
        padding: 0 12px;
        border-radius: 22px;
        border: none;
        background: #e7f1f2;
        color: #a0b51b;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0;
        line-height: 1;
        cursor: pointer;
        flex-shrink: 0;
        font-family: inherit;
    }
    .compose-voice-btn.is-recording {
        background: #dc2626;
        border-color: #b91c1c;
        color: #fff;
        animation: voice-pulse 1s ease-in-out infinite;
    }
    .voice-status {
        margin-top: 8px;
        font-size: 0.82rem;
        font-weight: 600;
        color: #0f766e;
    }
    .voice-status.is-recording {
        color: #dc2626;
    }
    @keyframes voice-pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.06); }
    }
    .compose-error {
        margin-top: 8px;
        font-size: 0.82rem;
        font-weight: 700;
        color: #b91c1c;
    }
    .messages-sidebar .messages-empty {
        padding: 48px 20px;
        text-align: center;
        color: #555;
        font-weight: 600;
        font-size: 0.92rem;
    }
    .messages-body .messages-empty {
        padding: 48px 20px;
        text-align: center;
        color: #0f766e;
        font-weight: 600;
        font-size: 0.92rem;
    }
    .messages-empty-pane {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fffaf0;
        color: #555;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .mobile-back {
        display: none;
        padding: 8px 10px;
        border-radius: 8px;
        border: none;
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
        font-weight: 800;
        font-size: 1.2rem;
        text-decoration: none;
        line-height: 1;
    }
    @media (max-width: 560px) {
        .messages-page-title { font-size: 28px; }
        .messages-wrap { 
            height: 70vh;
            min-height: 400px;
            grid-template-columns: 1fr;
        }
        .messages-wrap.is-mobile-thread .messages-sidebar { display: none; }
        .messages-wrap:not(.is-mobile-thread) .messages-main { display: none; }
        .mobile-back { display: inline-flex; }
        .messages-compose { padding: 8px; }
        .compose-form { gap: 6px; }
        .compose-form textarea { padding: 7px 14px; }
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

@if(session('error'))
    <p style="color:#b91c1c;font-weight:700;margin:0 0 12px;">{{ session('error') }}</p>
@endif

<div class="messages-wrap {{ $activeConversation ? 'is-mobile-thread' : '' }}">
    <aside class="messages-sidebar" aria-label="Chats">
        <div class="messages-sidebar-head">Chats</div>
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
