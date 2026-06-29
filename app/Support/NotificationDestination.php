<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

class NotificationDestination
{
    public static function url(DatabaseNotification $notification, User $user): string
    {
        $data = is_array($notification->data) ? $notification->data : [];
        $role = strtolower((string) ($user->role ?? 'employee'));

        if (! empty($data['conversation_id']) && in_array($role, ['admin', 'manager'], true)) {
            return route('messages.index', ['conversation' => $data['conversation_id']]);
        }

        if (! empty($data['leave_request_id'])) {
            return match ($role) {
                'manager' => route('manager.requests.index', ['status' => 'all']),
                'admin' => route('admin.requests', ['status' => 'all']),
                default => route('leave.myRequests'),
            };
        }

        return route('notifications.index');
    }
}
