<?php

namespace App\Support;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\LeaveRequestOverdueNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OverdueLeaveNotifier
{
    public static function notifyDue(): int
    {
        try {
            PendingLeaveDeadlineNotifier::notifyDue();
        } catch (\Throwable $e) {
            report($e);
        }

        $ids = LeaveRequest::query()
            ->overdue()
            ->whereNull('overdue_notified_at')
            ->orderBy('id')
            ->pluck('id');

        $notified = 0;
        foreach ($ids as $id) {
            try {
                if (self::notifyOne((int) $id)) {
                    $notified++;
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $notified;
    }

    public static function notifyOne(int $id): bool
    {
        return (bool) DB::transaction(function () use ($id): bool {
            $leave = LeaveRequest::query()
                ->with('user')
                ->lockForUpdate()
                ->find($id);

            if (! $leave || ! $leave->isOverdue() || $leave->overdue_notified_at !== null) {
                return false;
            }

            $recipients = self::recipientsFor($leave);
            if ($recipients->isEmpty()) {
                return false;
            }

            foreach ($recipients as $recipient) {
                $notification = new LeaveRequestOverdueNotification($leave);

                try {
                    $recipient->notifyNow($notification, ['database']);
                } catch (\Throwable $e) {
                    report($e);
                    Log::error('Failed saving overdue in-app notification.', [
                        'leave_request_id' => $leave->id,
                        'user_id' => $recipient->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                try {
                    $recipient->notifyNow($notification, ['mail']);
                } catch (\Throwable $e) {
                    report($e);
                    Log::error('Failed emailing overdue leave notification.', [
                        'leave_request_id' => $leave->id,
                        'user_id' => $recipient->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $leave->overdue_notified_at = Carbon::now();
            $leave->save();

            return true;
        });
    }

    /** @return Collection<int, User> */
    public static function recipientsFor(LeaveRequest $leave): Collection
    {
        $recipients = collect();
        $leave->loadMissing('user');

        if ($leave->user) {
            $recipients->push($leave->user);

            if ($leave->user->manager_id && ! $leave->isManagerApplicant()) {
                $manager = User::query()
                    ->managers()
                    ->whereKey($leave->user->manager_id)
                    ->where('is_active', true)
                    ->first();
                if ($manager) {
                    $recipients->push($manager);
                }
            }
        }

        return $recipients
            ->merge(LeaveApprovalWorkflow::seniorsForLeave($leave))
            ->unique('id')
            ->values();
    }
}
