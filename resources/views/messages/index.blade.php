@extends((auth()->user()->role ?? null) === 'admin' ? 'layouts.admin' : 'layouts.manager')

@section('title', 'Messages')

@section('content')
<style>
    .messages-wrap {
        max-width: 1400px;
        margin: 20px auto 60px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 0 10px #a7bd8dff;
        overflow: hidden;
        display: grid;
        grid-template-columns: 360px 1fr;
        min-height: 75vh;
    }
    .messages-sidebar {
        border-right: 1px solid #e5e7eb;
        background: #fbfbfb;
        display: flex;
        flex-direction: column;
    }
    .messages-sidebar h2 {
        margin: 0;
        padding: 16px 18px;
        font-size: 18px;
        font-weight: 800;
        color: #2c3e50;
        border-bottom: 1px solid #e5e7eb;
    }
    .thread-list {
        overflow-y: auto;
        padding: 10px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .thread {
        display: block;
        padding: 12px 12px;
        border-radius: 14px;
        text-decoration: none;
        border: 1px solid #e5e7eb;
        background: white;
        color: #111827;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .thread.active {
        border-color: #138056ff;
        box-shadow: 0 6px 18px rgba(19, 128, 86, 0.18);
    }
    .thread-title {
        font-weight: 800;
        font-size: 14px;
        color: #0a1347;
        display: flex;
        justify-content: space-between;
        gap: 10px;
    }
    .thread-sub {
        margin-top: 6px;
        font-size: 13px;
        color: #475569;
        display: flex;
        justify-content: space-between;
        gap: 10px;
    }
    .thread-preview {
        margin-top: 8px;
        font-size: 13px;
        color: #334155;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .thread-unread {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        border-radius: 999px;
        background: #138056ff;
        color: white;
        font-size: 12px;
        font-weight: 900;
        flex-shrink: 0;
    }
    .messages-main {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }
    .messages-header {
        border-bottom: 1px solid #e5e7eb;
        padding: 16px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .messages-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 900;
        color: #2c3e50;
    }
    .messages-header .meta {
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
        text-align: right;
        white-space: nowrap;
    }
    .messages-body {
        flex: 1;
        padding: 18px;
        overflow-y: auto;
        background: linear-gradient(180deg, #ffffff 0%, #fbfbfb 100%);
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .bubble-row {
        display: flex;
    }
    .bubble-row.me {
        justify-content: flex-end;
    }
    .bubble {
        max-width: 70%;
        padding: 10px 12px;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        background: white;
        color: #111827;
        font-size: 14px;
        line-height: 1.4;
        white-space: pre-wrap;
        word-break: break-word;
    }
    .bubble-row.me .bubble {
        background: #eafff1;
        border-color: #9ee7c2;
    }
    .bubble-meta {
        margin-top: 6px;
        font-size: 12px;
        font-weight: 700;
        color: #64748b;
        display: flex;
        justify-content: space-between;
        gap: 12px;
    }
    .ticks {
        display: inline-flex;
        align-items: center;
        gap: 2px;
        font-weight: 900;
        letter-spacing: -1px;
    }
    .ticks.gray {
        color: #64748b;
    }
    .ticks.blue {
        color: #1d4ed8;
    }
    .messages-compose {
        border-top: 1px solid #e5e7eb;
        padding: 14px 18px;
        background: white;
    }
    .compose-form {
        display: flex;
        gap: 10px;
        align-items: flex-end;
    }
    .compose-form textarea {
        flex: 1;
        min-height: 44px;
        max-height: 140px;
        resize: vertical;
        padding: 10px 12px;
        border-radius: 12px;
        border: 1px solid #cbd5e1;
        font-family: inherit;
        font-size: 14px;
        outline: none;
    }
    .compose-form textarea:focus {
        border-color: #138056ff;
        box-shadow: 0 0 0 3px rgba(19, 128, 86, 0.12);
    }
    .compose-form button {
        padding: 10px 18px;
        border-radius: 12px;
        border: none;
        font-weight: 900;
        background: #138056ff;
        color: white;
        cursor: pointer;
    }
    .compose-form button:hover {
        background: #0f6b47;
    }
    .compose-error {
        margin-top: 8px;
        font-size: 13px;
        font-weight: 800;
        color: #b91c1c;
    }
    .empty {
        padding: 40px 18px;
        color: #64748b;
        font-weight: 700;
        text-align: center;
    }
    @media (max-width: 900px) {
        .messages-wrap {
            grid-template-columns: 1fr;
        }
        .messages-sidebar {
            border-right: none;
            border-bottom: 1px solid #e5e7eb;
        }

        /* WhatsApp-like behavior: list -> thread */
        .messages-wrap.is-mobile-thread .messages-sidebar {
            display: none;
        }
        .messages-wrap:not(.is-mobile-thread) .messages-main {
            display: none;
        }
        .mobile-back {
            display: inline-flex !important;
        }
    }

    .mobile-back {
        display: none;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #0a1347;
        font-weight: 900;
        padding: 8px 10px;
        border-radius: 12px;
        text-decoration: none;
        align-items: center;
        justify-content: center;
        line-height: 1;
        flex-shrink: 0;
    }
</style>

<div class="messages-wrap {{ $activeConversation ? 'is-mobile-thread' : '' }}">
    <div class="messages-sidebar">
        <h2>Messages</h2>
        <div class="thread-list">
            @forelse($conversations as $c)
                @php
                    $activeId = $activeConversation?->id;
                    $employee = $c->leaveRequest?->user;
                    $employeeName = trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')) ?: 'Employee';
                    $lastMessage = $c->messages->first();
                    $preview = $lastMessage?->body ?? 'No messages yet';
                    $isAdmin = (auth()->user()->role ?? null) === 'admin';
                    $otherName = $isAdmin
                        ? (trim(($c->manager?->first_name ?? '') . ' ' . ($c->manager?->last_name ?? '')) ?: 'Manager')
                        : (trim(($c->admin?->first_name ?? '') . ' ' . ($c->admin?->last_name ?? '')) ?: 'Admin');
                @endphp
                <a class="thread {{ $activeId === $c->id ? 'active' : '' }}" href="{{ route('messages.index', ['conversation' => $c->id]) }}">
                    <div class="thread-title">
                        <span>{{ $otherName }}</span>
                        <span style="display:flex; align-items:center; gap:10px;">
                            @if(($c->unread_count ?? 0) > 0)
                                <span class="thread-unread">{{ $c->unread_count }}</span>
                            @endif
                        </span>
                    </div>
                    <div class="thread-sub">
                        <span>Leave #{{ $c->leave_request_id }} — {{ $employeeName }}</span>
                        <span>{{ optional($c->updated_at)->diffForHumans() }}</span>
                    </div>
                    <div class="thread-preview">{{ $preview }}</div>
                </a>
            @empty
                <div class="empty">No conversations yet.</div>
            @endforelse
        </div>
    </div>

    <div class="messages-main">
        @if(!$activeConversation)
            <div class="empty">Select a conversation to start chatting.</div>
        @else
            @php
                $employee = $activeConversation->leaveRequest?->user;
                $employeeName = trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')) ?: 'Employee';
                $meId = auth()->id();
                $isAdmin = (auth()->user()->role ?? null) === 'admin';
                $otherName = $isAdmin
                    ? (trim(($activeConversation->manager?->first_name ?? '') . ' ' . ($activeConversation->manager?->last_name ?? '')) ?: 'Manager')
                    : (trim(($activeConversation->admin?->first_name ?? '') . ' ' . ($activeConversation->admin?->last_name ?? '')) ?: 'Admin');
            @endphp
            <div class="messages-header">
                <a class="mobile-back" href="{{ route('messages.index') }}" aria-label="Back to chats">←</a>
                <div>
                    <h3>{{ $otherName }}</h3>
                    <div class="meta">Leave #{{ $activeConversation->leave_request_id }} — {{ $employeeName }} • {{ $activeConversation->leaveRequest?->leave_type ?? '' }} • {{ $activeConversation->leaveRequest?->status ?? '' }}</div>
                </div>
                <div class="meta">{{ optional($activeConversation->updated_at)->diffForHumans() }}</div>
            </div>

            <div class="messages-body" id="messagesBody">
                @foreach($activeConversation->messages()->with('sender')->oldest()->get() as $m)
                    @php
                        $isMe = $m->sender_id === $meId;
                        $senderName = trim(($m->sender?->first_name ?? '') . ' ' . ($m->sender?->last_name ?? '')) ?: 'User';
                    @endphp
                    <div class="bubble-row {{ $isMe ? 'me' : '' }}">
                        <div class="bubble">
                            {{ $m->body }}
                            <div class="bubble-meta">
                                <span>{{ $senderName }}</span>
                                <span style="display:flex; align-items:center; gap:8px;">
                                    <span>{{ optional($m->created_at)->format('d M Y H:i') }}</span>
                                    @if ($isMe)
                                        @if ($m->read_at)
                                            <span class="ticks blue" title="Seen">✓✓</span>
                                        @elseif ($m->delivered_at)
                                            <span class="ticks gray" title="Delivered">✓✓</span>
                                        @else
                                            <span class="ticks gray" title="Sent">✓</span>
                                        @endif
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="messages-compose">
                <form class="compose-form" method="POST" action="{{ route('messages.send', ['conversation' => $activeConversation->id]) }}">
                    @csrf
                    <textarea name="body" required placeholder="Type a message...">{{ old('body') }}</textarea>
                    <button type="submit">Send</button>
                </form>
                @error('body')
                    <div class="compose-error">{{ $message }}</div>
                @enderror
            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const box = document.getElementById('messagesBody');
        if (box) box.scrollTop = box.scrollHeight;
    });
</script>
@endsection
