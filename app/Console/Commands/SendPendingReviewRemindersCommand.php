<?php

namespace App\Console\Commands;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\PendingReviewReminderNotification;
use App\Support\LeaveApprovalWorkflow;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendPendingReviewRemindersCommand extends Command
{
    protected $signature = 'leave:send-pending-review-reminders';

    protected $description = 'Email managers and senior managers about pending leave reviews at 24h, 48h, then daily';

    public function handle(): int
    {
        $now = Carbon::now();
        $sent = 0;

        $sent += $this->remindManagerPending($now);
        $sent += $this->remindSeniorPending($now);

        $this->info("Sent {$sent} pending-review reminder(s).");

        return self::SUCCESS;
    }

    private function remindManagerPending(Carbon $now): int
    {
        $sent = 0;

        $requests = LeaveRequest::query()
            ->with('user')
            ->whereRaw('LOWER(TRIM(status)) = ?', ['pending'])
            ->whereHas('user', fn ($q) => $q->where('role', 'employee'))
            ->get();

        foreach ($requests as $leave) {
            [$nextStage, $label] = $this->nextStage($leave, $now);
            if ($nextStage === null || $label === null) {
                continue;
            }

            $manager = null;
            if ($leave->user?->manager_id) {
                $manager = User::query()->managers()->whereKey($leave->user->manager_id)->where('is_active', true)->first();
            }
            if (! $manager) {
                continue;
            }

            try {
                $manager->notify(new PendingReviewReminderNotification($leave, $label));
                $leave->pending_review_reminder_stage = $nextStage;
                $leave->last_pending_review_reminder_at = $now;
                $leave->save();
                $sent++;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $sent;
    }

    private function remindSeniorPending(Carbon $now): int
    {
        $sent = 0;

        $requests = LeaveRequest::query()
            ->with('user')
            ->whereRaw('LOWER(TRIM(status)) = ?', ['pending_senior'])
            ->get();

        foreach ($requests as $leave) {
            // Prefer time since recommendation; fall back to created_at for manager-applicant leave.
            $anchor = $leave->recommended_at
                ? Carbon::parse($leave->recommended_at)
                : ($leave->created_at ? Carbon::parse($leave->created_at) : null);

            if (! $anchor) {
                continue;
            }

            [$nextStage, $label] = $this->nextStageForHours(
                (int) $anchor->diffInHours($now),
                (int) ($leave->pending_review_reminder_stage ?? 0),
                $leave->last_pending_review_reminder_at
                    ? Carbon::parse($leave->last_pending_review_reminder_at)
                    : null,
                $now
            );

            if ($nextStage === null || $label === null) {
                continue;
            }

            $seniors = LeaveApprovalWorkflow::seniorsForLeave($leave);
            if ($seniors->isEmpty()) {
                continue;
            }

            try {
                foreach ($seniors as $senior) {
                    $senior->notify(new PendingReviewReminderNotification($leave, $label));
                }
                $leave->pending_review_reminder_stage = $nextStage;
                $leave->last_pending_review_reminder_at = $now;
                $leave->save();
                $sent += $seniors->count();
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $sent;
    }

    /** @return array{0: ?int, 1: ?string} */
    private function nextStage(LeaveRequest $leave, Carbon $now): array
    {
        $hours = $leave->created_at ? $leave->created_at->diffInHours($now) : 0;
        $stage = (int) ($leave->pending_review_reminder_stage ?? 0);
        $lastAt = $leave->last_pending_review_reminder_at
            ? Carbon::parse($leave->last_pending_review_reminder_at)
            : null;

        return $this->nextStageForHours($hours, $stage, $lastAt, $now);
    }

    /** @return array{0: ?int, 1: ?string} */
    private function nextStageForHours(int $hours, int $stage, ?Carbon $lastAt, Carbon $now): array
    {
        if ($hours >= 24 && $stage < 1) {
            return [1, '24 hours'];
        }

        if ($hours >= 48 && $stage < 2) {
            return [2, '48 hours'];
        }

        if ($hours >= 72 && $stage >= 2 && ($lastAt === null || $lastAt->lte($now->copy()->subDay()))) {
            return [$stage < 3 ? 3 : $stage, 'daily'];
        }

        return [null, null];
    }
}
