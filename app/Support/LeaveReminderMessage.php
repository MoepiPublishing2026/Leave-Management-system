<?php

namespace App\Support;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Support\Carbon;

class LeaveReminderMessage
{
    public static function build(User $user, LeaveRequest $leave, string $kind, Carbon $referenceDate): string
    {
        $name = trim((string) ($user->first_name ?? ''));
        $greeting = $name !== '' ? "Hello {$name}" : 'Hello';
        $type = trim((string) ($leave->leave_type ?? 'leave'));
        $start = Carbon::parse($leave->start_date)->startOfDay();
        $today = $referenceDate->copy()->startOfDay();

        if ($kind === 'today') {
            if ($start->isSameDay($today)) {
                return "{$greeting}, today is your leave day ({$type}).";
            }

            return "{$greeting}, you are on leave today ({$type}).";
        }

        if ($kind === 'tomorrow') {
            $tomorrow = $today->copy()->addDay();
            if ($start->isSameDay($tomorrow)) {
                return "{$greeting}, tomorrow is your leave day ({$type}).";
            }

            return "{$greeting}, tomorrow you are still on leave ({$type}).";
        }

        $startLabel = $start->format('l, j M Y');
        $daysUntil = $today->diffInDays($start);

        if ($daysUntil === 1) {
            return "{$greeting}, tomorrow is your leave day ({$type}).";
        }

        if ($daysUntil === 7) {
            return "{$greeting}, your {$type} leave starts next week ({$startLabel}).";
        }

        return "{$greeting}, your {$type} leave starts in {$daysUntil} days ({$startLabel}).";
    }
}
