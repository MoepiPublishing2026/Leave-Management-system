<?php

namespace App\Support;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Support\Carbon;

class LeaveReminderMessage
{
    public static function build(User $user, LeaveRequest $leave, string $kind, Carbon $referenceDate, bool $withGreeting = true): string
    {
        $sentence = self::sentence($leave, $kind, $referenceDate);

        if (! $withGreeting) {
            return ucfirst($sentence);
        }

        $name = EmailGreeting::displayName($user);
        $greeting = $name !== '' ? "Hello {$name}" : 'Hello';

        return "{$greeting}, {$sentence}";
    }

    private static function sentence(LeaveRequest $leave, string $kind, Carbon $referenceDate): string
    {
        $type = trim((string) ($leave->leave_type ?? 'leave'));
        $start = Carbon::parse($leave->start_date)->startOfDay();
        $today = $referenceDate->copy()->startOfDay();

        if (in_array($kind, ['team_today', 'team_tomorrow'], true)) {
            $employee = trim(($leave->user->first_name ?? '').' '.($leave->user->last_name ?? ''));
            $employee = $employee !== '' ? $employee : 'A team member';
            $when = $kind === 'team_today' ? 'today' : 'tomorrow';

            return "{$employee} is on {$type} leave {$when}.";
        }

        if ($kind === 'today') {
            if ($start->isSameDay($today)) {
                return "today is your leave day ({$type}).";
            }

            return "you are on leave today ({$type}).";
        }

        if ($kind === 'tomorrow') {
            $tomorrow = $today->copy()->addDay();
            if ($start->isSameDay($tomorrow)) {
                return "tomorrow is your leave day ({$type}).";
            }

            return "tomorrow you are still on leave ({$type}).";
        }

        $startLabel = $start->format('l, j M Y');
        $daysUntil = $today->diffInDays($start);

        if ($daysUntil === 1) {
            return "tomorrow is your leave day ({$type}).";
        }

        if ($daysUntil === 7) {
            return "your {$type} leave starts next week ({$startLabel}).";
        }

        return "your {$type} leave starts in {$daysUntil} days ({$startLabel}).";
    }
}
