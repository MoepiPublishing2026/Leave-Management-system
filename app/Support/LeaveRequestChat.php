<?php

namespace App\Support;

use App\Models\LeaveRequestConversation;
use App\Models\User;
use App\Notifications\MessageReceivedNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LeaveRequestChat
{
    public static function conversationsQueryFor(User $user): Builder
    {
        $query = LeaveRequestConversation::query();

        if (($user->role ?? null) === 'manager') {
            $query->where('manager_id', $user->id);
        }

        // All administrators can access every manager↔admin thread.
        return $query;
    }

    public static function userCanAccessConversation(User $user, LeaveRequestConversation $conversation): bool
    {
        if (($user->role ?? null) === 'manager') {
            return (int) $conversation->manager_id === (int) $user->id;
        }

        if (($user->role ?? null) === 'admin') {
            return true;
        }

        return false;
    }

    public static function unreadCountFor(User $user): int
    {
        if (! Schema::hasTable('leave_request_messages') || ! Schema::hasTable('leave_request_conversations')) {
            return 0;
        }

        $role = $user->role ?? null;
        if (! in_array($role, ['admin', 'manager'], true)) {
            return 0;
        }

        $query = DB::table('leave_request_messages as m')
            ->join('leave_request_conversations as c', 'c.id', '=', 'm.conversation_id')
            ->where('m.sender_id', '!=', $user->id)
            ->whereNull('m.read_at');

        if ($role === 'manager') {
            $query->where('c.manager_id', $user->id);
        }

        return (int) $query->count();
    }

    public static function notifyAdmins(int $conversationId, int $leaveRequestId, string $senderName): void
    {
        $admins = User::query()->where('role', 'admin')->get();

        foreach ($admins as $admin) {
            $admin->notify(new MessageReceivedNotification($conversationId, $leaveRequestId, $senderName));
        }
    }
}
