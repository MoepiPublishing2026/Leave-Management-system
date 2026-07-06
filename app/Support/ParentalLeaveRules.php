<?php

namespace App\Support;

use App\Models\LeavePolicy;
use App\Models\LeaveRequest;

class ParentalLeaveRules
{
  /** @var list<string> */
    public const TYPES = ['maternity', 'paternity'];

    public static function isParentalLeaveType(string $leaveType): bool
    {
        return in_array(strtolower(trim($leaveType)), self::TYPES, true);
    }

  /** Leave type the user has already applied for (any non-withdrawn request), or null. */
    public static function usedParentalLeaveType(int $userId): ?string
    {
        $request = LeaveRequest::query()
            ->where('user_id', $userId)
            ->whereRaw('LOWER(TRIM(status)) != ?', ['withdrawn'])
            ->where(function ($query) {
                foreach (self::TYPES as $type) {
                    $query->orWhereRaw('LOWER(TRIM(leave_type)) = ?', [$type]);
                }
            })
            ->orderByDesc('created_at')
            ->first();

        if (! $request) {
            return null;
        }

        return (string) $request->leave_type;
    }

    /** Opposite parental leave type label for the apply form, or null if none is blocked. */
    public static function blockedLeaveTypeLabel(int $userId): ?string
    {
        $used = self::usedParentalLeaveType($userId);
        if (! $used) {
            return null;
        }

        $opposite = strtolower(trim($used)) === 'maternity' ? 'paternity' : 'maternity';

        $policyLabel = LeavePolicy::query()
            ->whereRaw('LOWER(TRIM(leave_type)) = ?', [$opposite])
            ->value('leave_type');

        return $policyLabel ?: ucfirst($opposite);
    }

    public static function canApply(int $userId, string $leaveType): bool
    {
        return ! self::isBlockedForUser($userId, $leaveType);
    }

    public static function isBlockedForUser(int $userId, string $leaveType): bool
    {
        if (! self::isParentalLeaveType($leaveType)) {
            return false;
        }

        $blocked = self::blockedLeaveTypeLabel($userId);
        if ($blocked === null) {
            return false;
        }

        return strcasecmp(trim($leaveType), trim($blocked)) === 0;
    }

    /** Whether a leave type should be hidden from dashboard charts (blocked parental type). */
    public static function isExcludedFromCharts(int $userId, string $leaveType): bool
    {
        return self::isBlockedForUser($userId, $leaveType);
    }

    public static function blockedMessage(int $userId, string $leaveType): string
    {
        $used = self::usedParentalLeaveType($userId) ?? 'parental leave';

        return 'You already have a '.$used.' request on record. You cannot apply for '.$leaveType.' leave.';
    }
}
