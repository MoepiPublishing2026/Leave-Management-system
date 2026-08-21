<?php

namespace App\Support;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\LeaveReminderNotification;
use Illuminate\Notifications\DatabaseNotification;

class NotificationDestination
{
    public static function url(DatabaseNotification $notification, User $user): string
    {
        if (! UserNotifications::isVisibleTo($notification, $user)) {
            return route('notifications.index');
        }

        $data = is_array($notification->data) ? $notification->data : [];
        $role = strtolower((string) ($user->role ?? 'employee'));

        if (! empty($data['conversation_id']) && in_array($role, ['admin', 'manager'], true)) {
            return route('messages.index', ['conversation' => $data['conversation_id']]);
        }

        if (! empty($data['leave_request_id'])) {
            $requestId = (int) $data['leave_request_id'];

            if ($notification->type === LeaveReminderNotification::class) {
                $kind = (string) ($data['kind'] ?? '');

                if (str_starts_with($kind, 'team_')) {
                    return $role === 'admin'
                        ? route('admin.calendar')
                        : route('manager.calendar');
                }

                if ($role === 'employee') {
                    return route('employee.calendar');
                }

                if ($role === 'manager') {
                    $leaveRequest = LeaveRequest::query()->find($requestId);

                    if ($leaveRequest && (int) $leaveRequest->user_id === (int) $user->id) {
                        return route('manager.myCalendar');
                    }
                }
            }

            if ($role === 'employee') {
                $ownsRequest = LeaveRequest::query()
                    ->whereKey($requestId)
                    ->where('user_id', $user->id)
                    ->exists();

                if (! $ownsRequest) {
                    return route('notifications.index');
                }

                return route('leave.myRequests', [
                    'status' => 'all',
                    'search' => (string) $requestId,
                    'request_id' => $requestId,
                ]);
            }

            if ($role === 'manager') {
                $leaveRequest = LeaveRequest::query()->find($requestId);

                if ($leaveRequest && (int) $leaveRequest->user_id === (int) $user->id) {
                    return route('manager.myLeave', [
                        'status' => 'all',
                        'search' => (string) $requestId,
                        'request_id' => $requestId,
                    ]);
                }

                return route('manager.requests.index', [
                    'status' => 'all',
                    'search' => (string) $requestId,
                    'request_id' => $requestId,
                ]);
            }

            if ($role === 'admin') {
                return route('admin.requests', [
                    'status' => 'all',
                    'search' => (string) $requestId,
                    'request_id' => $requestId,
                ]);
            }
        }

        return route('notifications.index');
    }
}
