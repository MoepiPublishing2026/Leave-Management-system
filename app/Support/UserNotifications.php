<?php

namespace App\Support;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\LeaveReminderNotification;
use App\Notifications\LeaveStatusNotification;
use App\Notifications\LeaveConvertedToUnpaidNotification;
use App\Notifications\LeaveRequestOverdueNotification;
use App\Notifications\LeaveWithdrawnNotification;
use App\Notifications\LeaveSubmittedNotification;
use App\Notifications\ProofUploadReminderNotification;
use App\Notifications\ProofUploadedNotification;
use App\Notifications\MessageReceivedNotification;
use App\Notifications\NewLeaveRequestNotification;
use App\Notifications\LeaveRequestUpdatedNotification;
use App\Notifications\OverdueLeaveEscalatedNotification;
use App\Notifications\PendingLeaveActionReminderNotification;
use App\Notifications\PendingReviewReminderNotification;
use App\Notifications\ManagerDailySummaryNotification;
use App\Notifications\PerformanceReviewSubmittedNotification;
use App\Notifications\PerformanceReviewAcknowledgedNotification;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;

class UserNotifications
{
    public static function unreadForDropdown(User $user, int $limit = 10): Collection
    {
        return $user->unreadNotifications()
            ->latest()
            ->limit(max($limit * 5, $limit))
            ->get()
            ->filter(fn (DatabaseNotification $notification) => self::isVisibleTo($notification, $user))
            ->take($limit)
            ->values();
    }

    public static function unreadCount(User $user): int
    {
        return $user->unreadNotifications()
            ->latest()
            ->get()
            ->filter(fn (DatabaseNotification $notification) => self::isVisibleTo($notification, $user))
            ->count();
    }

    public static function latestForIndex(User $user, int $perPage = 20)
    {
        $filtered = $user->notifications()
            ->latest()
            ->get()
            ->filter(fn (DatabaseNotification $notification) => self::isVisibleTo($notification, $user))
            ->values();

        $page = max(1, (int) request()->query('page', 1));
        $offset = ($page - 1) * $perPage;

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $filtered->slice($offset, $perPage)->values(),
            $filtered->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    public static function isVisibleTo(DatabaseNotification $notification, User $user): bool
    {
        if ((string) $notification->notifiable_id !== (string) $user->getKey()
            || $notification->notifiable_type !== $user->getMorphClass()) {
            return false;
        }

        $role = strtolower((string) ($user->role ?? 'employee'));
        $type = (string) $notification->type;
        $data = is_array($notification->data) ? $notification->data : [];

        if ($role === 'employee') {
            if (! in_array($type, [
                LeaveStatusNotification::class,
                LeaveReminderNotification::class,
                LeaveConvertedToUnpaidNotification::class,
                LeaveRequestOverdueNotification::class,
                LeaveSubmittedNotification::class,
                ProofUploadReminderNotification::class,
                PerformanceReviewAcknowledgedNotification::class,
            ], true)) {
                return false;
            }

            if ($type === PerformanceReviewAcknowledgedNotification::class) {
                return true;
            }

            $leaveId = $data['leave_request_id'] ?? null;
            if ($leaveId) {
                return LeaveRequest::query()
                    ->whereKey($leaveId)
                    ->where('user_id', $user->id)
                    ->exists();
            }

            return true;
        }

        if ($role === 'manager') {
            if (in_array($type, [
                LeaveStatusNotification::class,
                LeaveReminderNotification::class,
                LeaveConvertedToUnpaidNotification::class,
                LeaveRequestOverdueNotification::class,
                LeaveSubmittedNotification::class,
                ProofUploadReminderNotification::class,
                ProofUploadedNotification::class,
                LeaveRequestUpdatedNotification::class,
            ], true)) {
                $leaveId = $data['leave_request_id'] ?? null;
                if ($leaveId) {
                    $teamIds = ManagerTeam::employeeIdsFor($user);

                    return LeaveRequest::query()
                        ->whereKey($leaveId)
                        ->where(function ($q) use ($user, $teamIds) {
                            $q->where('user_id', $user->id);
                            if ($teamIds->isNotEmpty()) {
                                $q->orWhereIn('user_id', $teamIds);
                            }
                        })
                        ->exists();
                }

                return false;
            }

            return in_array($type, [
                NewLeaveRequestNotification::class,
                MessageReceivedNotification::class,
                LeaveWithdrawnNotification::class,
                PendingReviewReminderNotification::class,
                PendingLeaveActionReminderNotification::class,
                ManagerDailySummaryNotification::class,
                PerformanceReviewAcknowledgedNotification::class,
            ], true);
        }

        if ($role === 'admin') {
            $leaveId = $data['leave_request_id'] ?? null;
            if ($leaveId) {
                $leave = LeaveRequest::query()->find($leaveId);

                return $leave && LeaveApprovalWorkflow::seniorCanAccessLeave($user, $leave);
            }

            if (in_array($type, [
                NewLeaveRequestNotification::class,
                LeaveRequestUpdatedNotification::class,
            ], true)) {
                return false;
            }

            return in_array($type, [
                MessageReceivedNotification::class,
                OverdueLeaveEscalatedNotification::class,
                LeaveStatusNotification::class,
                LeaveConvertedToUnpaidNotification::class,
                LeaveRequestOverdueNotification::class,
                LeaveWithdrawnNotification::class,
                ProofUploadedNotification::class,
                PendingReviewReminderNotification::class,
                PendingLeaveActionReminderNotification::class,
                PerformanceReviewSubmittedNotification::class,
            ], true);
        }

        return true;
    }
}
