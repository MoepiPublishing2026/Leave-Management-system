<?php

namespace App\Support;

use App\Models\LeaveRequest;
use App\Models\LeaveRequestConversation;
use App\Models\User;
use App\Notifications\MessageReceivedNotification;
use App\Support\LeaveApprovalWorkflow;
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
        } elseif (($user->role ?? null) === 'admin') {
            $managerIds = User::query()->managers()->where('manager_id', $user->id)->pluck('id');
            if ($managerIds->isEmpty()) {
                return $query->whereRaw('1 = 0');
            }
            $query->whereIn('manager_id', $managerIds);
        }

        return $query;
    }

    public static function userCanAccessConversation(User $user, LeaveRequestConversation $conversation): bool
    {
        if (($user->role ?? null) === 'manager') {
            return (int) $conversation->manager_id === (int) $user->id;
        }

        if (($user->role ?? null) === 'admin') {
            $manager = User::query()->find($conversation->manager_id);

            return $manager && (int) $manager->manager_id === (int) $user->id;
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
        } elseif ($role === 'admin') {
            $managerIds = User::query()->managers()->where('manager_id', $user->id)->pluck('id');
            if ($managerIds->isEmpty()) {
                return 0;
            }
            $query->whereIn('c.manager_id', $managerIds);
        }

        return (int) $query->count();
    }

    public static function notifyAdmins(int $conversationId, int $leaveRequestId, string $senderName): void
    {
        $leave = LeaveRequest::query()->with('user')->find($leaveRequestId);
        $admins = $leave
            ? LeaveApprovalWorkflow::seniorsForLeave($leave)
            : collect();

        foreach ($admins as $admin) {
            try {
                $admin->notify(new MessageReceivedNotification($conversationId, $leaveRequestId, $senderName));
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
