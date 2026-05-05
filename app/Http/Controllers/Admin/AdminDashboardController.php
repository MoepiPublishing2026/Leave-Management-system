<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Support\PercentageDistribution;
use Illuminate\Support\Carbon;
use Yasumi\Yasumi;

class AdminDashboardController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $monthFilter = $request->query('month', 'this_year');

        $baseQuery = LeaveRequest::query()->whereRaw('LOWER(status) != ?', ['withdrawn']);
        if ($monthFilter) {
            $now = Carbon::now();
            if (ctype_digit((string) $monthFilter)) {
                $baseQuery->whereYear('start_date', (int) $monthFilter);
            } elseif ($monthFilter === 'this_month') {
                $baseQuery->whereYear('start_date', $now->year)
                    ->whereMonth('start_date', $now->month);
            } elseif ($monthFilter === 'this_quarter') {
                $quarter = (int) ceil($now->month / 3);
                $startMonth = ($quarter - 1) * 3 + 1;
                $endMonth = $startMonth + 2;
                $baseQuery->whereYear('start_date', $now->year)
                    ->whereBetween(DB::raw('MONTH(start_date)'), [$startMonth, $endMonth]);
            } elseif ($monthFilter === 'this_year') {
                $baseQuery->whereYear('start_date', $now->year);
            }
        }

        $allRequestsCount = (clone $baseQuery)->count();

        $leaveTypeCounts = (clone $baseQuery)->select('leave_type', DB::raw('count(*) as total'))
            ->groupBy('leave_type')
            ->pluck('total', 'leave_type')
            ->toArray();
        arsort($leaveTypeCounts, SORT_NUMERIC);
        $leaveTypePercentages = PercentageDistribution::oneDecimalPercents($leaveTypeCounts);

        $pendingTotalCount = (clone $baseQuery)->whereRaw('LOWER(status) = ?', ['pending'])->count();

        $overduePendingCount = (clone $baseQuery)->whereRaw('LOWER(status) = ?', ['pending'])
            ->whereDate('start_date', '<', Carbon::today())->count();
        $pendingRequestsCount = max(0, $pendingTotalCount - $overduePendingCount);

        $today = Carbon::today();
        $isTodayDisabled = $today->isWeekend();
        if (!$isTodayDisabled) {
            $holidays = Yasumi::create('SouthAfrica', (int) $today->year);
            $holidayDates = [];
            foreach ($holidays as $holiday) {
                $holidayDates[] = $holiday->format('Y-m-d');
            }
            $isTodayDisabled = in_array($today->toDateString(), $holidayDates, true);
        }

        $onLeaveTodayCount = 0;
        if (!$isTodayDisabled) {
            $onLeaveTodayCount = (clone $baseQuery)->whereRaw('LOWER(status) = ?', ['approved'])
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->distinct('user_id')
                ->count('user_id');
        }

        $activeEmployeesCount = User::where('is_active', 1)->count();

        // Monthly leave requests by start_date (same scope as manager: all non-withdrawn)
        $monthlyData = (clone $baseQuery)->select(
            DB::raw('YEAR(start_date) as year'),
            DB::raw('MONTH(start_date) as month'),
            DB::raw('COUNT(*) as total')
        )->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
        $barLabels = [];
        $barData = [];
        foreach ($monthlyData as $data) {
            $barLabels[] = date('M Y', mktime(0, 0, 0, $data->month, 1, $data->year));
            $barData[] = $data->total;
        }

        $rawStatusCounts = (clone $baseQuery)->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
        $statusCounts = ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'withdrawn' => 0];
        foreach ($rawStatusCounts as $status => $count) {
            $statusCounts[strtolower(trim((string) $status))] = $count;
        }

        $statusPercentages = PercentageDistribution::oneDecimalPercents([
            'Pending' => (int) ($statusCounts['pending'] ?? 0),
            'Rejected' => (int) ($statusCounts['rejected'] ?? 0),
            'Approved' => (int) ($statusCounts['approved'] ?? 0),
        ]);

        $departmentCountsRaw = (clone $baseQuery)->join('users', 'leave_requests.user_id', '=', 'users.id')
            ->select(DB::raw("COALESCE(users.department, 'Unknown') as department"), DB::raw('count(*) as total'))
            ->groupBy('department')
            ->pluck('total', 'department')
            ->toArray();

        // Polar chart: only departments with at least one request (no empty wedges)
        $departmentCounts = array_filter(
            array_map(static fn ($v) => (int) $v, $departmentCountsRaw),
            static fn (int $count) => $count > 0
        );
        arsort($departmentCounts, SORT_NUMERIC);
        $departmentLabels = array_keys($departmentCounts);
        $departmentData = array_values($departmentCounts);
        $departmentPercentages = [];
        if ($departmentLabels !== [] && count($departmentData) === count($departmentLabels)) {
            $departmentPercentages = PercentageDistribution::oneDecimalPercents(
                array_combine($departmentLabels, $departmentData)
            );
        }

        $avgLeaveDaysByType = (clone $baseQuery)
            ->whereRaw('LOWER(status) = ?', ['approved'])
            ->select('leave_type', DB::raw('AVG(DATEDIFF(end_date, start_date) + 1) as avg_days'))
            ->groupBy('leave_type')
            ->orderByDesc('avg_days')
            ->get();
        $avgLeaveTypeLabels = $avgLeaveDaysByType->pluck('leave_type')->toArray();
        $avgLeaveTypeData = $avgLeaveDaysByType->pluck('avg_days')->map(fn ($v) => round((float) $v, 1))->toArray();

        $weekdayRows = (clone $baseQuery)
            ->select(DB::raw('DAYOFWEEK(created_at) as day_num'), DB::raw('COUNT(*) as total'))
            ->groupBy('day_num')
            ->pluck('total', 'day_num')
            ->toArray();
        $weekdayMap = [1 => 'Sun', 2 => 'Mon', 3 => 'Tue', 4 => 'Wed', 5 => 'Thu', 6 => 'Fri', 7 => 'Sat'];
        $weekdayLabels = [];
        $weekdayData = [];
        foreach ($weekdayMap as $num => $label) {
            $weekdayLabels[] = $label;
            $weekdayData[] = (int) ($weekdayRows[$num] ?? 0);
        }

        return view('admin.dashboard', [
            'allRequestsCount' => $allRequestsCount,
            'leaveTypeCounts' => $leaveTypeCounts,
            'leaveTypePercentages' => $leaveTypePercentages,
            'pendingRequestsCount' => $pendingRequestsCount,
            'overduePendingCount' => $overduePendingCount,
            'onLeaveTodayCount' => $onLeaveTodayCount,
            'activeEmployeesCount' => $activeEmployeesCount,
            'barLabels' => $barLabels,
            'barData' => $barData,
            'statusCounts' => $statusCounts,
            'statusPercentages' => $statusPercentages,
            'departmentLabels' => $departmentLabels,
            'departmentData' => $departmentData,
            'departmentPercentages' => $departmentPercentages,
            'monthFilter' => $monthFilter,
            'avgLeaveTypeLabels' => $avgLeaveTypeLabels,
            'avgLeaveTypeData' => $avgLeaveTypeData,
            'weekdayLabels' => $weekdayLabels,
            'weekdayData' => $weekdayData,
        ]);

        
    }
    
}
