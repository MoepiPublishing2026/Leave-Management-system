@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')
@include('partials.notifications-page-styles')

@php
    $unreadCount = (int) ($notifications->whereNull('read_at')->count());
@endphp

<div class="notifications-page">
    <h1>Notifications</h1>
    <p class="notifications-lead">
        @if($notifications->total() > 0)
            Showing {{ $notifications->total() }} {{ \Illuminate\Support\Str::plural('notification', $notifications->total()) }}
            @if($unreadCount > 0) · <strong>{{ $unreadCount }}</strong> unread @endif
        @else
            All your notifications in one place.
        @endif
    </p>

    @if ($notifications->isEmpty())
        <div class="notifications-toolbar">
            <a href="{{ route('admin.dashboard') }}" class="notifications-btn notifications-btn--secondary">Dashboard</a>
        </div>
        <div class="notifications-list">
            <div class="notifications-empty">You have no notifications.</div>
        </div>
    @else
        <div class="notifications-toolbar">
            <a href="{{ route('admin.dashboard') }}" class="notifications-btn notifications-btn--secondary">Dashboard</a>
            <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                @if($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.mark.read') }}" style="margin:0;">
                        @csrf
                        <button type="submit" class="notifications-btn notifications-btn--primary">
                            Mark all as read
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <ul class="notifications-list">
            @foreach ($notifications as $notification)
                @php
                    $isUnread = is_null($notification->read_at);
                    $msg = (string) ($notification->data['message'] ?? 'Notification');
                @endphp
                <li class="notification-item {{ $isUnread ? 'is-unread' : '' }}">
                    <a class="notification-link"
                       href="{{ route('notifications.open', ['notification' => $notification->id, 'redirect' => route('notifications.index')]) }}">
                        <span class="notification-dot" aria-hidden="true"></span>
                        <div class="notification-body">
                            <div class="notification-message">{{ $msg }}</div>
                            <div class="notification-meta">{{ $notification->created_at->diffForHumans() }}</div>
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="notifications-pagination">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection

