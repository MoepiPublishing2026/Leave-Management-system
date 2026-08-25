{{-- Canonical order: Dashboard, Requests, Reviews, Messages, Calendar, Analytics, Reports, Profile, Leave Policies --}}
<a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
<a href="{{ route('admin.requests', ['status' => 'all']) }}" class="{{ request()->routeIs(['admin.requests', 'admin.manager-requests*']) ? 'active' : '' }}">All Leave Requests</a>
<a href="{{ route('admin.reviews.index') }}" class="{{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">Reviews</a>
<a href="{{ route('messages.index') }}" class="{{ request()->routeIs('messages.*') ? 'active' : '' }}">
    Messages
    @if(($unreadMessageCount ?? 0) > 0)
        <span class="badge-notification">{{ $unreadMessageCount }}</span>
    @endif
</a>
@if(!empty($includeNotifications))
    <a href="{{ route('notifications.index') }}" class="{{ request()->routeIs('notifications.*') ? 'active' : '' }}">
        <span>Notifications</span>
        @if(($notificationCount ?? 0) > 0)
            <span class="badge-notification">{{ $notificationCount }}</span>
        @endif
    </a>
@endif
<a href="{{ route('admin.calendar') }}" class="{{ request()->routeIs('admin.calendar') ? 'active' : '' }}">Calendar</a>
<a href="{{ route('admin.analytics') }}" class="{{ request()->routeIs('admin.analytics') ? 'active' : '' }}">Analytics</a>
<a href="{{ route('admin.reports') }}" class="{{ request()->routeIs('admin.reports') ? 'active' : '' }}">Reports</a>
<a href="{{ route('profile.edit') }}" class="{{ request()->routeIs(['profile.edit', 'settings.edit']) ? 'active' : '' }}">Profile</a>
<a href="{{ route('admin.settings') }}" class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}">Leave Policies</a>
