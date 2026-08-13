<?php

namespace App\Support;

use App\Models\LeaveRequest;
use App\Notifications\ProofUploadReminderNotification;
use Illuminate\Support\Carbon;

class ProofUploadReminderService
{
    public static function syncAll(?Carbon $referenceDate = null): void
    {
        $today = ($referenceDate ?? Carbon::today())->copy()->startOfDay();

        LeaveRequest::query()
            ->with('user')
            ->whereRaw('LOWER(TRIM(status)) = ?', ['approved'])
            ->orderBy('id')
            ->each(function (LeaveRequest $leave) use ($today): void {
                self::maybeNotify($leave, $today);
            });
    }

    public static function notifyIfNeeded(LeaveRequest $leave, string $kind = 'approved', ?Carbon $referenceDate = null, bool $sendMail = true): void
    {
        $leave->loadMissing('user');
        $user = $leave->user;
        if (! $user) {
            return;
        }

        if (! LeaveProofRules::requiresDoctorsLetter($leave)) {
            return;
        }

        $today = ($referenceDate ?? Carbon::today())->copy()->startOfDay();
        self::send($user, $leave, $kind, $today, $sendMail);
    }

    private static function maybeNotify(LeaveRequest $leave, Carbon $today): void
    {
        $user = $leave->user;
        if (! $user || ! LeaveProofRules::requiresDoctorsLetter($leave)) {
            return;
        }

        $opens = LeaveProofRules::proofWindowOpensAt($leave)?->copy()->startOfDay();
        $deadline = LeaveProofRules::proofDeadlineAt($leave)?->copy()->startOfDay();

        if ($opens && $opens->equalTo($today) && ! self::alreadySentKind($user, $leave, 'approved', $today)) {
            self::send($user, $leave, 'window_open', $today);
        }

        if ($deadline && $deadline->equalTo($today->copy()->addDay())) {
            self::send($user, $leave, 'deadline_tomorrow', $today);
        }

        if ($deadline && $deadline->equalTo($today)) {
            self::send($user, $leave, 'deadline_today', $today);
        }
    }

    private static function send($user, LeaveRequest $leave, string $kind, Carbon $today, bool $sendMail = true): void
    {
        $notification = new ProofUploadReminderNotification($leave, $kind, $today, $sendMail);

        if (self::alreadySentKind($user, $leave, $kind, $today)) {
            return;
        }

        try {
            $user->notify($notification);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private static function alreadySentKind($user, LeaveRequest $leave, string $kind, Carbon $today): bool
    {
        $key = sprintf('proof_%s_%s_%s', $leave->id, $kind, $today->toDateString());

        return $user->notifications()
            ->where('type', ProofUploadReminderNotification::class)
            ->where('data->reminder_key', $key)
            ->exists();
    }
}
