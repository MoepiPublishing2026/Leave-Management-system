<?php

namespace App\Support;

use App\Models\LeavePolicy;
use App\Models\LeaveRequest;

class LeaveReviewChecklist
{
    /** @var array<int, array<string, mixed>> */
    private static array $balanceCache = [];

    /**
     * @return array<string, mixed>
     */
    public static function forRequest(LeaveRequest $leave): array
    {
        $type = (string) ($leave->leave_type ?? '');
        $requestedDays = LeaveWorkingDays::count($leave);
        $policy = LeaveProofRules::policyForType($type);
        $balances = self::balancesForUser((int) $leave->user_id);

        $allocated = (float) ($policy->default_days ?? 0);
        $taken = 0.0;
        $remaining = $allocated;

        foreach (($balances['utilization'] ?? []) as $row) {
            if (strcasecmp((string) ($row['type'] ?? ''), $type) === 0) {
                $allocated = (float) ($row['total'] ?? 0);
                $taken = (float) ($row['used'] ?? 0);
                $remaining = (float) ($row['remaining'] ?? 0);
                break;
            }
        }

        foreach (($balances['balances'] ?? []) as $balanceType => $value) {
            if (strcasecmp((string) $balanceType, $type) === 0) {
                $remaining = (float) $value;
                break;
            }
        }

        $annualRemaining = null;
        foreach (($balances['balances'] ?? []) as $balanceType => $value) {
            if (in_array(strtolower((string) $balanceType), ['annual', 'annual leave'], true)) {
                $annualRemaining = (float) $value;
                break;
            }
        }

        $fitsBalance = $requestedDays <= ($remaining + 0.0001);
        $proofRequired = LeaveProofRules::requiresProof($type);
        $proofDeferred = LeaveProofRules::allowsDeferredProof($type) && $proofRequired;
        $proofAttached = LeaveProofRules::hasProofAttached($leave);
        $deadline = LeaveProofRules::proofDeadlineAt($leave);
        $deadlineLabel = $deadline?->format('d M Y') ?? '';
        $willConvert = $proofDeferred
            && ! $proofAttached
            && ! in_array(strtolower(trim((string) $leave->status)), ['rejected', 'withdrawn'], true)
            && empty($leave->converted_to_unpaid_at);

        $unpaidNote = '';
        if ($willConvert && $deadlineLabel !== '') {
            $unpaidNote = "If the doctor's letter is not uploaded by {$deadlineLabel}, this sick leave will automatically become unpaid.";
        } elseif ($willConvert) {
            $unpaidNote = "If the doctor's letter is not uploaded by the deadline, this sick leave will automatically become unpaid.";
        }

        $documentsOk = ! $proofRequired || $proofAttached || $proofDeferred;
        $policyText = self::policyText($policy, $type);

        return [
            'days' => LeaveDaysFormatter::format($requestedDays),
            'isHalfDay' => (bool) $leave->is_half_day,
            'halfDayPeriod' => (string) ($leave->half_day_period ?? ''),
            'halfDayPeriodLabel' => LeaveWorkdayRules::halfDayPeriodLabel($leave->half_day_period),
            'review' => [
                'allocated' => LeaveDaysFormatter::format($allocated),
                'taken' => LeaveDaysFormatter::format($taken),
                'remaining' => LeaveDaysFormatter::format($remaining),
                'requested' => LeaveDaysFormatter::format($requestedDays),
                'fitsBalance' => $fitsBalance,
                'countsAgainstAnnual' => AnnualLeaveRules::countsAgainstAnnual($type),
                'annualRemaining' => $annualRemaining !== null ? LeaveDaysFormatter::format($annualRemaining) : '',
                'proofRequired' => $proofRequired,
                'proofDeferred' => $proofDeferred,
                'proofAttached' => $proofAttached,
                'proofDeadline' => $deadlineLabel,
                'willConvertToUnpaid' => $willConvert,
                'unpaidNote' => $unpaidNote,
                'allowHalfDays' => (bool) ($policy->allow_half_days ?? false),
                'policyDefaultDays' => LeaveDaysFormatter::format($allocated),
                'policyText' => $policyText,
                'documentsOk' => $documentsOk,
            ],
        ];
    }

    /**
     * @return array{balances: array<string, float|int>, utilization: array<int, array<string, mixed>>}
     */
    private static function balancesForUser(int $userId): array
    {
        if ($userId < 1) {
            return ['balances' => [], 'utilization' => []];
        }

        if (isset(self::$balanceCache[$userId])) {
            return self::$balanceCache[$userId];
        }

        try {
            $data = PersonalLeaveDashboardData::build($userId);
            self::$balanceCache[$userId] = [
                'balances' => $data['leaveBalances'] ?? [],
                'utilization' => $data['leaveUtilization'] ?? [],
            ];
        } catch (\Throwable $e) {
            report($e);
            self::$balanceCache[$userId] = ['balances' => [], 'utilization' => []];
        }

        return self::$balanceCache[$userId];
    }

    private static function policyText(?LeavePolicy $policy, string $type): string
    {
        if (! $policy) {
            return 'Policy details are currently unavailable.';
        }

        try {
            $html = view('partials.policy-leave-type-text', [
                'policy' => $policy,
                'type' => $type,
            ])->render();

            return trim(preg_replace('/\s+/', ' ', strip_tags($html)) ?? '') ?: 'See leave policies.';
        } catch (\Throwable $e) {
            report($e);

            return $type.' leave is limited to '.LeaveDaysFormatter::format((float) $policy->default_days).' days per year.';
        }
    }
}
