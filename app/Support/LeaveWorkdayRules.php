<?php

namespace App\Support;

use App\Models\LeaveRequest;
use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Company workday rules: 08:00–17:00, with half-day slots 08:00–12:00 and 12:00–17:00.
 * Same-day apply/withdraw is blocked once the workday has started (08:00),
 * except Sick leave which may still be applied for until 12:00.
 */
class LeaveWorkdayRules
{
    public const WORK_START_HOUR = 8;

    public const WORK_START_MINUTE = 0;

    /** Same-day Sick leave may still be submitted until this hour. */
    public const SICK_SAME_DAY_APPLY_UNTIL_HOUR = 12;

    public const HALF_DAY_MORNING = 'morning';

    public const HALF_DAY_AFTERNOON = 'afternoon';

    /** @return array<string, string> */
    public static function halfDayPeriods(): array
    {
        return [
            self::HALF_DAY_MORNING => '08:00 – 12:00',
            self::HALF_DAY_AFTERNOON => '12:00 – 17:00',
        ];
    }

    public static function halfDayPeriodLabel(?string $period): string
    {
        $periods = self::halfDayPeriods();

        return $periods[$period ?? ''] ?? '';
    }

    /** Label for emails/UI when the leave is a half day (empty when not). */
    public static function halfDaySlotForLeave(?LeaveRequest $leave): string
    {
        if (! $leave || empty($leave->is_half_day)) {
            return '';
        }

        $label = self::halfDayPeriodLabel($leave->half_day_period ?? null);

        return $label !== '' ? 'Half-day: '.$label : 'Half day';
    }

    public static function isValidHalfDayPeriod(?string $period): bool
    {
        return array_key_exists((string) $period, self::halfDayPeriods());
    }

    public static function workStartToday(?CarbonInterface $now = null): Carbon
    {
        $now = $now ? Carbon::instance($now)->copy() : Carbon::now();

        return $now->copy()->startOfDay()->setTime(self::WORK_START_HOUR, self::WORK_START_MINUTE, 0);
    }

    public static function workStartOn(CarbonInterface $date): Carbon
    {
        return Carbon::instance($date)->copy()->startOfDay()->setTime(self::WORK_START_HOUR, self::WORK_START_MINUTE, 0);
    }

    public static function isSickLeave(?string $leaveType): bool
    {
        return strtolower(trim((string) $leaveType)) === 'sick';
    }

    /** Cutoff datetime for same-day apply (08:00 normally, 12:00 for Sick). */
    public static function sameDayApplyCutoff(?string $leaveType = null, ?CarbonInterface $now = null): Carbon
    {
        $now = $now ? Carbon::instance($now)->copy() : Carbon::now();
        $hour = self::isSickLeave($leaveType)
            ? self::SICK_SAME_DAY_APPLY_UNTIL_HOUR
            : self::WORK_START_HOUR;

        return $now->copy()->startOfDay()->setTime($hour, 0, 0);
    }

    /** True once today's same-day apply cutoff has passed for this leave type. */
    public static function sameDayCutoffPassed(?CarbonInterface $now = null, ?string $leaveType = null): bool
    {
        $now = $now ? Carbon::instance($now)->copy() : Carbon::now();

        return $now->gte(self::sameDayApplyCutoff($leaveType, $now));
    }

    /**
     * Whether a leave start date may be chosen/submitted now.
     * Same-day apply: before 08:00 normally; Sick allowed until 12:00.
     */
    public static function canApplyStartingOn(
        CarbonInterface $startDate,
        ?CarbonInterface $now = null,
        ?CarbonInterface $existingStartDate = null,
        ?string $leaveType = null
    ): bool {
        $now = $now ? Carbon::instance($now)->copy() : Carbon::now();
        $startDay = Carbon::instance($startDate)->copy()->startOfDay();

        if (! $startDay->isSameDay($now)) {
            return $startDay->gte($now->copy()->startOfDay());
        }

        // Editing an existing same-day request may keep today's date after the cutoff.
        if ($existingStartDate && Carbon::instance($existingStartDate)->copy()->startOfDay()->isSameDay($now)) {
            return true;
        }

        return ! self::sameDayCutoffPassed($now, $leaveType);
    }

    public static function sameDayApplyBlockedMessage(?string $leaveType = null): string
    {
        if (self::isSickLeave($leaveType)) {
            return 'Same-day sick leave can only be applied for before 12:00. After midday, please choose another date or contact your manager.';
        }

        return 'Same-day leave can only be applied for before 08:00 (work starts at 08:00). Please choose another date or apply before 08:00.';
    }

    /**
     * Self-service withdraw is allowed until 08:00 on the leave start date.
     * After that the leave day has begun.
     */
    public static function canWithdraw(LeaveRequest $leave, ?CarbonInterface $now = null): bool
    {
        $status = strtolower(trim((string) ($leave->status ?? '')));
        if (! in_array($status, ['pending', 'pending_senior', 'approved'], true)) {
            return false;
        }

        if (! $leave->start_date || ! $leave->end_date) {
            return false;
        }

        $now = $now ? Carbon::instance($now)->copy() : Carbon::now();
        $endDay = Carbon::parse($leave->end_date)->startOfDay();
        if ($endDay->lt($now->copy()->startOfDay())) {
            return false;
        }

        $startCutoff = self::workStartOn(Carbon::parse($leave->start_date));

        return $now->lt($startCutoff);
    }

    public static function withdrawBlockedMessage(LeaveRequest $leave, ?CarbonInterface $now = null): string
    {
        $now = $now ? Carbon::instance($now)->copy() : Carbon::now();
        $status = strtolower(trim((string) ($leave->status ?? '')));

        if (! in_array($status, ['pending', 'pending_senior', 'approved'], true)) {
            return 'Only pending or approved leave can be withdrawn.';
        }

        if ($leave->end_date && Carbon::parse($leave->end_date)->startOfDay()->lt($now->copy()->startOfDay())) {
            return 'This leave can no longer be withdrawn because all of its leave days have passed.';
        }

        if ($leave->start_date && $now->gte(self::workStartOn(Carbon::parse($leave->start_date)))) {
            return 'This leave can no longer be withdrawn after 08:00 on its start day (work starts at 08:00).';
        }

        return 'This leave can no longer be withdrawn.';
    }
}
