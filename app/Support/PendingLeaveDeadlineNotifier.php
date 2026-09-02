<?php

namespace App\Support;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\PendingLeaveActionReminderNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class PendingLeaveDeadlineNotifier
{
    /** @var list<int> */
    private const DAY_OFFSETS = [7, 3, 1, 0];

    public static function notifyDue(?Carbon $today = null): int
    {
        $today = ($today ?? Carbon::today())->copy()->startOfDay();
        $latestStart = $today->copy()->addDays(max(self::DAY_OFFSETS));

        $requests = LeaveRequest::query()
            ->with('user')
            ->whereRaw('LOWER(TRIM(status)) in (?, ?)', ['pending', 'pending_senior'])
            ->whereDate('start_date', '>=', $today)
            ->whereDate('start_date', '<=', $latestStart)
            ->orderBy('id')
            ->get();

        $sent = 0;
        foreach ($requests as $leave) {
            $start = $leave->start_date ? Carbon::parse($leave->start_date)->startOfDay() : null;
            if (! $start) {
                continue;
            }

            $daysUntilStart = (int) $today->diffInDays($start);
            if (! in_array($daysUntilStart, self::DAY_OFFSETS, true)) {
                continue;
            }

            $sent += self::notifyLeave($leave, $daysUntilStart);
        }

        return $sent;
    }

    private static function notifyLeave(LeaveRequest $leave, int $daysUntilStart): int
    {
        $recipients = self::recipientsFor($leave);
        if ($recipients->isEmpty()) {
            return 0;
        }

        $sent = 0;
        foreach ($recipients as $recipient) {
            $notification = new PendingLeaveActionReminderNotification($leave, $daysUntilStart);
            if (self::alreadySent($recipient, $notification->reminderKey())) {
                continue;
            }

            try {
                $recipient->notifyNow($notification, ['database']);
            } catch (\Throwable $e) {
                report($e);
                Log::error('Failed saving pending-deadline in-app notification.', [
                    'leave_request_id' => $leave->id,
                    'user_id' => $recipient->id,
                    'error' => $e->getMessage(),
                ]);
            }

            try {
                $recipient->notifyNow($notification, ['mail']);
            } catch (\Throwable $e) {
                report($e);
                Log::error('Failed emailing pending-deadline reminder.', [
                    'leave_request_id' => $leave->id,
                    'user_id' => $recipient->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $sent++;
        }

        return $sent;
    }

    /** @return Collection<int, User> */
    public static function recipientsFor(LeaveRequest $leave): Collection
    {
        $recipients = collect();
        $leave->loadMissing('user');

        if ($leave->user && $leave->user->manager_id && ! $leave->isManagerApplicant()) {
            $manager = User::query()
                ->managers()
                ->whereKey($leave->user->manager_id)
                ->where('is_active', true)
                ->first();
            if ($manager) {
                $recipients->push($manager);
            }
        }

        return $recipients
            ->merge(LeaveApprovalWorkflow::seniorsForLeave($leave))
            ->unique('id')
            ->values();
    }

    private static function alreadySent(User $user, string $reminderKey): bool
    {
        return $user->notifications()
            ->where('type', PendingLeaveActionReminderNotification::class)
            ->where('data->reminder_key', $reminderKey)
            ->exists();
    }
}
