<?php

namespace App\Http\Controllers;

use App\Support\NotificationDestination;
use App\Support\UserNotifications;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $notifications = UserNotifications::latestForIndex($user, 20);

        // Render the correct layout per role (prevents Manager/Admin from seeing Employee layout)
        $view = match (strtolower((string) ($user->role ?? 'employee'))) {
            'admin' => 'admin.notifications.index',
            'manager' => 'manager.notifications.index',
            default => 'notifications.index',
        };

        return view($view, compact('notifications'));
    }

    public function open(Request $request, string $notification)
    {
        $user = auth()->user();

        $notificationModel = $user->notifications()->whereKey($notification)->firstOrFail();

        if (! UserNotifications::isVisibleTo($notificationModel, $user)) {
            abort(404);
        }

        if (is_null($notificationModel->read_at)) {
            $notificationModel->markAsRead();
        }

        $destination = NotificationDestination::url($notificationModel, $user);
        $redirect = $request->query('redirect');

        if (is_string($redirect) && $redirect !== '') {
            $appUrl = rtrim(url('/'), '/');
            $notificationsIndex = route('notifications.index');
            $isNotificationsIndex = $redirect === $notificationsIndex
                || str_ends_with(rtrim($redirect, '/'), '/notifications');

            if (! $isNotificationsIndex && (str_starts_with($redirect, $appUrl) || str_starts_with($redirect, '/'))) {
                return redirect()->to($redirect);
            }
        }

        return redirect()->to($destination);
    }

    public function markAsRead(Request $request)
    {
        $user = auth()->user();

        $user->unreadNotifications->each(function ($notification) use ($user) {
            if (\App\Support\UserNotifications::isVisibleTo($notification, $user)) {
                $notification->markAsRead();
            }
        });

        return back()->with('success', 'Notifications marked as read.');
    }
}
