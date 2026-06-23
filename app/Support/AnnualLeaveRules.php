<?php

namespace App\Support;

class AnnualLeaveRules
{
    /** @var list<string> */
    public const EXCLUDED_FROM_ANNUAL = [
        'sick',
        'maternity',
        'paternity',
        'bereavement',
        'unpaid',
    ];

    public static function isExcludedFromAnnual(string $leaveType): bool
    {
        return in_array(strtolower(trim($leaveType)), self::EXCLUDED_FROM_ANNUAL, true);
    }

    public static function countsAgainstAnnual(string $leaveType): bool
    {
        return !self::isExcludedFromAnnual($leaveType);
    }

  /**
   * @param array<string|int, float|int> $leaveDaysCounted
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
        return ['Annual', 'Annual Leave', 'Sick', 'Bereavement', 'Maternity', 'Paternity', 'Unpaid'];
    }

    public static function isAllowedWhenAnnualExhausted(string $leaveType): bool
    {
        $lower = strtolower(trim($leaveType));
        foreach (self::notDisabledWhenAnnualExhaustedLabels() as $label) {
            if (strtolower($label) === $lower) {
                return true;
            }
        }

        return false;
    }

    public static function exhaustedAnnualMessage(): string
    {
        return 'You have exhausted your annual leave limit. You may still request Sick, Bereavement (with proof), Maternity (with proof), Paternity (with proof), or Unpaid leave.';
    }
}
