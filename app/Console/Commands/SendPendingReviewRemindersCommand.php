<?php

namespace App\Console\Commands;

use App\Mail\PendingReviewReminderMail;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class SendPendingReviewRemindersCommand extends Command
{
    protected $signature = 'leave:send-pending-review-reminders';

    protected $description = 'Email managers about pending leave reviews at 24h, 48h, then daily';

    public function handle(): int
    {
        $now = Carbon::now();
        $sent = 0;

        $requests = LeaveRequest::query()
            ->with('user')
            ->whereRaw('LOWER(TRIM(status)) = ?', ['pending'])
            ->whereHas('user', fn ($q) => $q->where('role', 'employee'))
            ->get();

        foreach ($requests as $leave) {
            $hours = $leave->created_at ? $leave->created_at->diffInHours($now) : 0;
            $stage = (int) ($leave->pending_review_reminder_stage ?? 0);
            $lastAt = $leave->last_pending_review_reminder_at
                ? Carbon::parse($leave->last_pending_review_reminder_at)
                : null;

            $nextStage = null;
            $label = null;

            if ($hours >= 24 && $stage < 1) {
                $nextStage = 1;
                $label = '24 hours';
            } elseif ($hours >= 48 && $stage < 2) {
                $nextStage = 2;
                $label = '48 hours';
            } elseif ($hours >= 72 && $stage >= 2 && ($lastAt === null || $lastAt->lte($now->copy()->subDay()))) {
                $nextStage = $stage; // stay on daily stage marker (>=2)
                $label = 'daily';
                if ($stage < 3) {
                    $nextStage = 3;
                }
            }

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
                Mail::to($manager->email)->send(new PendingReviewReminderMail($leave, $label));
                $leave->pending_review_reminder_stage = $nextStage;
                $leave->last_pending_review_reminder_at = $now;
                $leave->save();
                $sent++;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $this->info("Sent {$sent} pending-review reminder(s).");

        return self::SUCCESS;
    }
}
