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

        $baseQuery = LeaveRequest::query()->byEmployeeApplicants()->whereRaw('LOWER(status) != ?', ['withdrawn']);
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

        $activeEmployeesCount = User::employees()->where('is_active', 1)->count();

        // Monthly charts: always Jan–Dec for one calendar year (submission month), like employee dashboard.
        $chartYear = ctype_digit((string) $monthFilter) ? (int) $monthFilter : (int) Carbon::now()->year;
        $barLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $barData = array_fill(0, 12, 0);

        $monthlyChartQuery = LeaveRequest::query()
            ->byEmployeeApplicants()
            ->whereRaw('LOWER(status) != ?', ['withdrawn'])
            ->whereYear('created_at', $chartYear);

        $monthlyData = (clone $monthlyChartQuery)
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as total'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        foreach ($monthlyData as $row) {
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
            ->where('users.role', 'employee')
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
            ->where('users.role', 'employee')
            ->select(DB::raw("COALESCE(users.designation, 'Unknown') as designation"), DB::raw('COUNT(*) as total'))
            ->groupBy('designation')
            ->pluck('total', 'designation')
            ->toArray();
        arsort($designationCounts, SORT_NUMERIC);
        $designationLabels = array_keys($designationCounts);
        $designationData = array_values($designationCounts);

        $monthlyStatusRows = (clone $monthlyChartQuery)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('LOWER(TRIM(status)) as status'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('month', 'status')
            ->orderBy('month')
            ->get();

        $monthlyApprovedData = array_fill(0, 12, 0);
        $monthlyPendingData = array_fill(0, 12, 0);
        $monthlyRejectedData = array_fill(0, 12, 0);
        foreach ($monthlyStatusRows as $row) {
            $idx = ((int) $row->month) - 1;
            if ($idx < 0 || $idx > 11) {
                continue;
            }
            $count = (int) $row->total;
            match ($row->status) {
                'approved' => $monthlyApprovedData[$idx] = $count,
                'pending' => $monthlyPendingData[$idx] = $count,
                'rejected' => $monthlyRejectedData[$idx] = $count,
                default => null,
            };
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
            'chartYear' => $chartYear,
            'statusCounts' => $statusCounts,
            'statusPercentages' => $statusPercentages,
            'departmentLabels' => $departmentLabels,
            'departmentData' => $departmentData,
            'departmentPercentages' => $departmentPercentages,
            'monthFilter' => $monthFilter,
            'designationLabels' => $designationLabels,
            'designationData' => $designationData,
            'monthlyApprovedData' => $monthlyApprovedData,
            'monthlyPendingData' => $monthlyPendingData,
            'monthlyRejectedData' => $monthlyRejectedData,
        ]);

        
    }
    
}
