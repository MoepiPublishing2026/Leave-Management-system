<?php

namespace App\Support;

use App\Models\LeavePolicy;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use App\Services\PublicHolidayService;
use Illuminate\Support\Facades\DB;

class PersonalLeaveDashboardData
{
    public static function build(int $userId): array
    {
        $rawStatusCounts = LeaveRequest::where('user_id', $userId)
            ->selectRaw('LOWER(status) as status, COUNT(*) as total')
            ->groupBy(DB::raw('LOWER(status)'))
            ->pluck('total', 'status')
            ->toArray();

        $statusCounts = ['pending' => 0, 'pending_senior' => 0, 'approved' => 0, 'rejected' => 0, 'withdrawn' => 0];
        foreach ($rawStatusCounts as $status => $count) {
            $statusCounts[strtolower(trim((string) $status))] = $count;
        }

        $pendingTotalCount = (int) ($statusCounts['pending'] ?? 0) + (int) ($statusCounts['pending_senior'] ?? 0);
        $approvedCount = $statusCounts['approved'];
        $rejectedCount = $statusCounts['rejected'];
        $withdrawnCount = $statusCounts['withdrawn'];

        $overduePendingCount = LeaveRequest::where('user_id', $userId)
            ->overdue()
            ->count();
        $pendingCount = max(0, $pendingTotalCount - $overduePendingCount);

        $allRequestsCount = LeaveRequest::where('user_id', $userId)->count();
        $activeRequestsQuery = LeaveRequest::where('user_id', $userId)
            ->whereRaw('LOWER(TRIM(status)) != ?', ['withdrawn']);

        $payStatusCounts = LeaveTypeChartCounts::payStatusFromQuery($activeRequestsQuery);
        $payStatusPercentages = PercentageDistribution::oneDecimalPercents($payStatusCounts);

        $user = \App\Models\User::find($userId);
        $departmentName = trim((string) ($user->department ?? '')) ?: 'Unknown';
        $departmentLabels = [$departmentName];
        $departmentData = [(clone $activeRequestsQuery)->count()];

        $today = Carbon::today();
        $isTodayDisabled = $today->isWeekend() || PublicHolidayService::isHoliday($today);
        $onLeaveTodayCount = 0;
        if (! $isTodayDisabled) {
            $onLeaveTodayCount = LeaveRequest::where('user_id', $userId)
                ->whereRaw('LOWER(TRIM(status)) = ?', ['approved'])
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->count();
        }

        $leaveTypeCounts = LeaveTypeChartCounts::fromQuery(
            LeaveRequest::where('user_id', $userId)
        );
        $leaveTypeCounts = self::filterChartLeaveTypes($leaveTypeCounts, $userId);
        $leaveTypePercentages = PercentageDistribution::oneDecimalPercents($leaveTypeCounts);

        $dashboardYear = LeaveYear::current();
        [$yearStart, $yearEnd] = LeaveYear::window($dashboardYear);
        $barLabels = LeaveYear::barLabels();

        $monthlyBreakdown = MonthlyLeaveStatusBreakdown::fromQuery(
            LeaveRequest::where('user_id', $userId)
                ->where('created_at', '>=', $yearStart)
                ->where('created_at', '<=', $yearEnd),
            'created_at'
        );

        $monthlyApprovedData = $monthlyBreakdown['monthlyApprovedData'];
        $monthlyPendingData = $monthlyBreakdown['monthlyPendingData'];
        $monthlyOverdueData = $monthlyBreakdown['monthlyOverdueData'];
        $monthlyRejectedData = $monthlyBreakdown['monthlyRejectedData'];
        $monthlyWithdrawnData = $monthlyBreakdown['monthlyWithdrawnData'];
        $barData = [];
        for ($i = 0; $i < 12; $i++) {
            $barData[] = (int) $monthlyApprovedData[$i]
                + (int) $monthlyPendingData[$i]
                + (int) $monthlyOverdueData[$i]
                + (int) $monthlyRejectedData[$i]
                + (int) $monthlyWithdrawnData[$i];
        }

        $policies = LeavePolicy::all()->keyBy('leave_type');
        $year = $dashboardYear;
        $startOfYear = $yearStart;
        $endOfYear = $yearEnd;

        $leavesTakenRaw = LeaveRequest::where('user_id', $userId)
            ->whereRaw('LOWER(status) = ?', ['approved'])
            ->whereDate('end_date', '>=', $startOfYear->toDateString())
            ->whereDate('start_date', '<=', $endOfYear->toDateString())
            ->get();

        $publicHolidays = self::generateHolidaysRange((int) $startOfYear->year, (int) $endOfYear->year);

        $leaveDaysCounted = [];
        foreach ($leavesTakenRaw as $leave) {
            $start = Carbon::parse($leave->start_date);
            $end = Carbon::parse($leave->end_date);
            $daysCount = self::countWorkingDaysInLeaveYear($start, $end, $publicHolidays, $year);
            if (! empty($leave->is_half_day) && $daysCount > 0) {
                $daysCount = 0.5;
            }
            $leaveDaysCounted[$leave->leave_type] = ($leaveDaysCounted[$leave->leave_type] ?? 0) + $daysCount;
        }

        $leaveBalances = [];
        foreach ($policies as $type => $policy) {
            $taken = $leaveDaysCounted[$type] ?? 0;
            $leaveBalances[$type] = max(0, $policy->default_days - $taken);
        }

        $annualKey = $policies->has('Annual') ? 'Annual' : ($policies->has('Annual Leave') ? 'Annual Leave' : 'Annual');
        if ($policies->has($annualKey)) {
            $annualDefault = $policies[$annualKey]->default_days ?? 30;
            $annualPoolUsed = AnnualLeaveRules::sumDaysAgainstAnnual($leaveDaysCounted);
            $leaveBalances[$annualKey] = max(0, $annualDefault - $annualPoolUsed);
        }

        $leaveUtilization = [];
        foreach ($policies as $type => $policy) {
            $defaultDays = (float) ($policy->default_days ?? 0);
            $takenDays = (float) ($leaveDaysCounted[$type] ?? 0);
            $remainingDays = (float) max(0, $defaultDays - $takenDays);

            $leaveUtilization[] = [
                'type' => $type,
                'total' => $defaultDays,
                'used' => $takenDays,
                'remaining' => $remainingDays,
            ];
        }

        if ($policies->has($annualKey)) {
            $annualDefault = (float) ($policies[$annualKey]->default_days ?? 30);
            $annualPoolUsed = AnnualLeaveRules::sumDaysAgainstAnnual($leaveDaysCounted);
            $leaveUtilization = array_values(array_filter($leaveUtilization, fn ($it) => $it['type'] !== $annualKey));
            $leaveUtilization[] = [
                'type' => $annualKey,
                'total' => $annualDefault,
                'used' => $annualPoolUsed,
                'remaining' => (float) max(0, $annualDefault - $annualPoolUsed),
            ];
        }

        $leaveUtilization = array_values(array_filter(
            $leaveUtilization,
            fn (array $item) => ! ParentalLeaveRules::isExcludedFromCharts($userId, (string) ($item['type'] ?? ''))
        ));

        $chartLeaveBalances = self::filterChartLeaveTypes($leaveBalances, $userId);

        $statusPercentages = PercentageDistribution::oneDecimalPercents([
            'Pending' => (int) $pendingCount,
            'Overdue' => (int) $overduePendingCount,
            'Rejected' => (int) $rejectedCount,
            'Approved' => (int) $approvedCount,
        ]);

        return compact(
            'allRequestsCount',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'withdrawnCount',
            'overduePendingCount',
            'onLeaveTodayCount',
            'payStatusCounts',
            'payStatusPercentages',
            'departmentLabels',
            'departmentData',
            'statusPercentages',
            'leaveTypeCounts',
            'leaveTypePercentages',
            'year',
            'barLabels',
            'barData',
            'monthlyApprovedData',
            'monthlyPendingData',
            'monthlyOverdueData',
            'monthlyRejectedData',
            'monthlyWithdrawnData',
            'leaveBalances',
            'chartLeaveBalances',
            'leaveUtilization'
        );
    }

    private static function filterChartLeaveTypes(array $countsByType, int $userId): array
    {
        return array_filter(
            $countsByType,
            static fn ($_, string $type) => ! ParentalLeaveRules::isExcludedFromCharts($userId, $type),
            ARRAY_FILTER_USE_BOTH
        );
    }

    private static function generateHolidaysRange(int $startYear, int $endYear): array
    {
        return PublicHolidayService::datesForYearRange($startYear, $endYear);
    }

    private static function countWorkingDaysInLeaveYear(Carbon $start, Carbon $end, array $publicHolidaysForYear, int $year): int
    {
        [$yearStart, $yearEnd] = LeaveYear::window($year);
        $windowStart = $yearStart->copy()->startOfDay();
        $windowEnd = $yearEnd->copy()->startOfDay();
        $current = $start->copy()->startOfDay();
        $last = $end->copy()->startOfDay();
        $daysCount = 0;
        while ($current->lte($last)) {
            if (
                $current->betweenIncluded($windowStart, $windowEnd)
                && ! $current->isWeekend()
                && ! in_array($current->toDateString(), $publicHolidaysForYear, true)
            ) {
                $daysCount++;
            }
            $current->addDay();
        }

        return $daysCount;
    }
}
