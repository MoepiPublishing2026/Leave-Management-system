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

        // Monthly leave requests grouped by application month (created_at), not leave month.
        // This matches the employee dashboard behavior (apply in May for September counts in May).
        $monthlyQuery = LeaveRequest::query()->whereRaw('LOWER(status) != ?', ['withdrawn']);
        if ($monthFilter) {
            $now = Carbon::now();
            if (ctype_digit((string) $monthFilter)) {
                $monthlyQuery->whereYear('created_at', (int) $monthFilter);
            } elseif ($monthFilter === 'this_month') {
                $monthlyQuery->whereYear('created_at', $now->year)
                    ->whereMonth('created_at', $now->month);
            } elseif ($monthFilter === 'this_quarter') {
                $quarter = (int) ceil($now->month / 3);
                $startMonth = ($quarter - 1) * 3 + 1;
                $endMonth = $startMonth + 2;
                $monthlyQuery->whereYear('created_at', $now->year)
                    ->whereBetween(DB::raw('MONTH(created_at)'), [$startMonth, $endMonth]);
            } elseif ($monthFilter === 'this_year') {
                $monthlyQuery->whereYear('created_at', $now->year);
            }
        }

        $monthlyData = $monthlyQuery->select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as total')
        )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Always return Jan..Dec (no skipped months). Months with no data are 0.
        $selectedYear = ctype_digit((string) $monthFilter) ? (int) $monthFilter : (int) Carbon::now()->year;
        $barLabels = [];
        $barData = array_fill(0, 12, 0);
        for ($m = 1; $m <= 12; $m++) {
            $barLabels[] = date('M Y', mktime(0, 0, 0, $m, 1, $selectedYear));
        }
        foreach ($monthlyData as $row) {
            if ((int) $row->year !== $selectedYear) {
                continue;
            }
            $idx = ((int) $row->month) - 1;
            if ($idx >= 0 && $idx < 12) {
                $barData[$idx] = (int) $row->total;
            }
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

        // Requests by designation (for dashboard chart)
        $designationCounts = (clone $baseQuery)
            ->join('users', 'leave_requests.user_id', '=', 'users.id')
            ->select(DB::raw("COALESCE(users.designation, 'Unknown') as designation"), DB::raw('COUNT(*) as total'))
            ->groupBy('designation')
            ->pluck('total', 'designation')
            ->toArray();
        arsort($designationCounts, SORT_NUMERIC);
        $designationLabels = array_keys($designationCounts);
        $designationData = array_values($designationCounts);

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
            'designationLabels' => $designationLabels,
            'designationData' => $designationData,
            'weekdayLabels' => $weekdayLabels,
            'weekdayData' => $weekdayData,
        ]);

        
    }
    
}
