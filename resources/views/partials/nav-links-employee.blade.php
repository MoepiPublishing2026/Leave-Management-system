{{-- Canonical order: Dashboard, Employees, Apply, Requests, Reviews, Calendar, Profile, Leave Policies --}}
<a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
@if(\App\Support\SupportStaff::canManageEmployees(auth()->user()))
    <a href="{{ route('manager.employees.index') }}" class="{{ request()->routeIs('manager.employees.*') ? 'active' : '' }}">Employees</a>
@endif
<a href="{{ route('apply') }}" class="{{ request()->routeIs('apply') ? 'active' : '' }}">Apply</a>
<a href="{{ route('leave.myRequests') }}" class="{{ request()->routeIs('leave.myRequests') ? 'active' : '' }}">My Requests</a>
<a href="{{ route('reviews.index') }}" class="{{ request()->routeIs('reviews.*') ? 'active' : '' }}">Performance Reviews</a>
@if(!empty($includeNotifications))
    <a href="{{ route('notifications.index') }}" class="{{ request()->routeIs('notifications.*') ? 'active' : '' }}">
        <span>Notifications</span>
        @if(($notificationCount ?? 0) > 0)
            <span class="badge-notification">{{ $notificationCount }}</span>
        @endif
    </a>
@endif
<a href="{{ route('employee.calendar') }}" class="{{ request()->routeIs('employee.calendar') ? 'active' : '' }}">Calendar</a>
<a href="{{ route('profile.edit') }}" class="{{ request()->routeIs(['profile.edit', 'settings.edit']) ? 'active' : '' }}">Profile</a>
<a href="{{ route('policies') }}" class="{{ request()->routeIs('policies') ? 'active' : '' }}">Leave Policies</a>
