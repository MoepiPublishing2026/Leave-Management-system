<?php

namespace App\Support;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\LeaveReminderNotification;
use Illuminate\Support\Carbon;

class LeaveReminderService
{
    /** @var array<int> */
    private const UPCOMING_DAY_OFFSETS = [7, 3, 2];

    public static function syncForUser(User $user, ?Carbon $referenceDate = null): void
    {
        $role = strtolower((string) ($user->role ?? ''));
        if (! in_array($role, ['employee', 'manager'], true)) {
            return;
        }

        $today = ($referenceDate ?? Carbon::today())->copy()->startOfDay();

        $requests = LeaveRequest::query()
            ->where('user_id', $user->id)
            ->whereRaw('LOWER(TRIM(status)) = ?', ['approved'])
            ->whereDate('end_date', '>=', $today)
            ->get();

        foreach ($requests as $request) {
            self::maybeNotify($user, $request, 'today', $today);
            self::maybeNotify($user, $request, 'tomorrow', $today);

            foreach (self::UPCOMING_DAY_OFFSETS as $offset) {
                self::maybeNotifyUpcoming($user, $request, $today, $offset);
            }
        }
    }

    public static function syncAllApplicants(?Carbon $referenceDate = null): void
    {
        User::query()
            ->whereIn('role', ['employee', 'manager'])
            ->orderBy('id')
            ->each(fn (User $user) => self::syncForUser($user, $referenceDate));
    }

    private static function maybeNotify(User $user, LeaveRequest $request, string $kind, Carbon $today): void
    {
        $start = Carbon::parse($request->start_date)->startOfDay();
        $end = Carbon::parse($request->end_date)->startOfDay();

        if ($kind === 'today') {
            if ($today->lt($start) || $today->gt($end)) {
                return;
            }
        } else {
            $target = $today->copy()->addDay();
            if ($target->lt($start) || $target->gt($end)) {
                return;
            }
        }

        $request->setRelation('user', $user);
        self::send($user, $request, $kind, $today);

        if (! in_array($kind, ['today', 'tomorrow'], true) || ! $user->manager_id) {
            return;
        }

        $manager = User::query()->managers()->whereKey($user->manager_id)->first();
        if (! $manager || (int) $manager->id === (int) $user->id) {
            return;
        }

        self::send($manager, $request, $kind === 'today' ? 'team_today' : 'team_tomorrow', $today);
    }

    private static function maybeNotifyUpcoming(User $user, LeaveRequest $request, Carbon $today, int $daysBeforeStart): void
    {
        $start = Carbon::parse($request->start_date)->startOfDay();
        if ($start->lte($today)) {
            return;
        }

        if ($today->diffInDays($start) !== $daysBeforeStart) {
            return;
        }

        self::send($user, $request, 'upcoming', $today);
    }

    private static function send(User $user, LeaveRequest $request, string $kind, Carbon $referenceDate): void
    {
        $notification = new LeaveReminderNotification($request, $kind, $referenceDate);

        if (self::alreadySent($user, $notification->reminderKey())) {
            return;
        }

        try {
            $user->notify($notification);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private static function alreadySent(User $user, string $reminderKey): bool
    {
        return $user->notifications()
            ->where('type', LeaveReminderNotification::class)
            ->where('data->reminder_key', $reminderKey)
            ->exists();
    }
}
