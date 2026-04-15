<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $notifications = $user->notifications()->latest()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function open(Request $request, string $notification)
    {
        $user = auth()->user();

        $notificationModel = $user->notifications()->whereKey($notification)->firstOrFail();

        if (is_null($notificationModel->read_at)) {
            $notificationModel->markAsRead();
        }

        $redirect = $request->query('redirect');
        if (is_string($redirect) && $redirect !== '') {
            $appUrl = rtrim(url('/'), '/');
            if (str_starts_with($redirect, $appUrl) || str_starts_with($redirect, '/')) {
                return redirect()->to($redirect);
            }
        }

        if (($notificationModel->data['leave_request_id'] ?? null) && ($user->role ?? null) === 'manager') {
            return redirect()->route('manager.requests.index');
        }

        if (($notificationModel->data['leave_request_id'] ?? null) && ($user->role ?? null) === 'employee') {
            return redirect()->route('leave.myRequests');
        }

        if (($notificationModel->data['leave_request_id'] ?? null) && ($user->role ?? null) === 'admin') {
            return redirect()->route('admin.requests', ['status' => 'Overdue']);
        }

        if (($notificationModel->data['conversation_id'] ?? null) && in_array($user->role ?? null, ['admin', 'manager'], true)) {
            return redirect()->route('messages.index', ['conversation' => $notificationModel->data['conversation_id']]);
        }

        return redirect()->route('notifications.index');
    }

    public function markAsRead(Request $request)
    {
        $user = auth()->user();
        $user->unreadNotifications->markAsRead();

        return back()->with('success', 'Notifications marked as read.');
    }
}
