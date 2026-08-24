<?php

namespace App\Support;

class AnnualLeaveRules
{
    /** @var list<string> */
    public const ANNUAL_TYPES = [
        'annual',
        'annual leave',
    ];

    /** @var list<string> */
    public const EXCLUDED_FROM_ANNUAL = [
        'sick',
        'maternity',
        'paternity',
        'bereavement',
        'unpaid',
        'family responsibility',
        'study',
    ];

    public static function isAnnualType(string $leaveType): bool
    {
        return in_array(strtolower(trim($leaveType)), self::ANNUAL_TYPES, true);
    }

    public static function isExcludedFromAnnual(string $leaveType): bool
    {
        return ! self::isAnnualType($leaveType);
    }

    public static function countsAgainstAnnual(string $leaveType): bool
    {
        return self::isAnnualType($leaveType);
    }

    /**
     * @param  array<string|int, float|int>  $leaveDaysCounted
     */
    public static function sumDaysAgainstAnnual(array $leaveDaysCounted): float
    {
        $sum = 0.0;
        foreach ($leaveDaysCounted as $type => $takenDays) {
            if (self::countsAgainstAnnual((string) $type)) {
                $sum += (float) $takenDays;
            }
        }

        return $sum;
    }

    /** @return list<string> */
    public static function notDisabledWhenAnnualExhaustedLabels(): array
    {
        return ['Annual', 'Annual Leave', 'Sick', 'Bereavement', 'Maternity', 'Paternity', 'Unpaid', 'Family Responsibility', 'Study'];
    }

    public static function isAllowedWhenAnnualExhausted(string $leaveType): bool
    {
        return self::isExcludedFromAnnual($leaveType) || self::isAnnualType($leaveType);
    }

    public static function exhaustedAnnualMessage(): string
    {
        return 'You have exhausted your annual leave. Other leave types use their own balances and can still be requested.';
    }
}
