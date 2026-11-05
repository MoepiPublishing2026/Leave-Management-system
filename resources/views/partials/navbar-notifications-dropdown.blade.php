<ul class="dropdown-menu notifications-dropdown dropdown-menu-end" aria-labelledby="notificationsDropdown">
    <li class="dropdown-header">Notifications</li>
    @forelse($notifications as $notification)
        <li>
            @php
                $notificationRedirect = \App\Support\NotificationDestination::url($notification, $user);
            @endphp
            <a href="{{ route('notifications.open', ['notification' => $notification->id, 'redirect' => $notificationRedirect]) }}" class="dropdown-item notification-dropdown-item">
                <span class="notification-dropdown-message">{{ $notification->data['message'] ?? 'Notification' }}</span>
                <small class="notification-dropdown-time">{{ $notification->created_at->diffForHumans() }}</small>
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
    @empty
        <li class="dropdown-item text-center text-muted">No new notifications</li>
    @endforelse
    <li class="notification-dropdown-footer"><a href="{{ route('notifications.index') }}" class="dropdown-item notification-dropdown-all">View all</a></li>
</ul>
