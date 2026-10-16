{{-- Canonical order: Dashboard, Employees, Requests, Reviews, Apply, Messages, Calendar, Analytics, Reports, Profile, Leave Policies --}}
<a href="{{ route('manager.dashboard') }}" class="{{ request()->routeIs('manager.dashboard') ? 'active' : '' }}">Dashboard</a>
@if(\App\Support\SupportStaff::canManageEmployees(auth()->user()))
    <a href="{{ route('manager.employees.index') }}" class="{{ request()->routeIs('manager.employees.*') ? 'active' : '' }}">Employees</a>
@endif
<a href="{{ route('manager.requests.index', ['status' => 'all']) }}" class="{{ request()->routeIs('manager.requests.*') ? 'active' : '' }}">Team Requests</a>
<a href="{{ route('manager.reviews.index') }}" class="{{ request()->routeIs('manager.reviews.*') ? 'active' : '' }}">Reviews</a>
<a href="{{ route('manager.apply') }}" class="{{ request()->routeIs('manager.apply') ? 'active' : '' }}">Apply</a>
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
<a href="{{ route('manager.calendar') }}" class="{{ request()->routeIs(['manager.calendar', 'manager.myCalendar']) ? 'active' : '' }}">Calendar</a>
<a href="{{ route('manager.analytics') }}" class="{{ request()->routeIs('manager.analytics') ? 'active' : '' }}">Analytics</a>
<a href="{{ route('manager.reports') }}" class="{{ request()->routeIs('manager.reports') ? 'active' : '' }}">Reports</a>
<a href="{{ route('profile.edit') }}" class="{{ request()->routeIs(['profile.edit', 'settings.edit']) ? 'active' : '' }}">Profile</a>
<a href="{{ route('manager.settings') }}" class="{{ request()->routeIs('manager.settings') ? 'active' : '' }}">Leave Policies</a>
