<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\LeaveRequest;
use App\Support\MonthlyLeaveStatusBreakdown;
use App\Support\PercentageDistribution;
use App\Support\LeaveRequestPeriodFilter;
use App\Support\DashboardEmployeeFilter;
use Illuminate\Support\Carbon;
use Yasumi\Yasumi;

class AdminDashboardController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $monthFilter = $request->query('month', 'this_year');
        $employeeFilter = DashboardEmployeeFilter::selectedId($request->query('employee'));
        $employees = DashboardEmployeeFilter::employees();
        $dashboardFilters = DashboardEmployeeFilter::queryParams($monthFilter, $employeeFilter);

        $baseQuery = LeaveRequest::query()->byEmployeeApplicants()->whereRaw('LOWER(TRIM(status)) != ?', ['withdrawn']);
        LeaveRequestPeriodFilter::apply($baseQuery, $monthFilter, 'created_at');
        DashboardEmployeeFilter::apply($baseQuery, $employeeFilter);

        $allRequestsCount = (clone $baseQuery)->count();

        $leaveTypeCounts = (clone $baseQuery)->select('leave_type', DB::raw('count(*) as total'))
            ->groupBy('leave_type')
            ->pluck('total', 'leave_type')
            ->toArray();
        arsort($leaveTypeCounts, SORT_NUMERIC);
        $leaveTypePercentages = PercentageDistribution::oneDecimalPercents($leaveTypeCounts);

        $pendingTotalCount = (clone $baseQuery)->whereRaw('LOWER(TRIM(status)) = ?', ['pending'])->count();

        $overduePendingCount = (clone $baseQuery)->whereRaw('LOWER(TRIM(status)) = ?', ['pending'])
            ->whereDate('start_date', '<', Carbon::today())->count();
        $pendingRequestsCount = max(0, $pendingTotalCount - $overduePendingCount);

        $approvedRequestsCount = (clone $baseQuery)->whereRaw('LOWER(TRIM(status)) = ?', ['approved'])->count();
        $rejectedRequestsCount = (clone $baseQuery)->whereRaw('LOWER(TRIM(status)) = ?', ['rejected'])->count();

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
            $onLeaveTodayCount = LeaveRequest::query()
                ->byEmployeeApplicants()
                ->when($employeeFilter, fn ($q) => $q->where('user_id', $employeeFilter))
                ->whereRaw('LOWER(TRIM(status)) = ?', ['approved'])
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->distinct('user_id')
                ->count('user_id');
        }

        // Monthly applications line chart: when requests were submitted (created_at).
        $chartYear = ctype_digit((string) $monthFilter) ? (int) $monthFilter : (int) Carbon::now()->year;
        $barLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $barData = array_fill(0, 12, 0);

        $monthlyApplicationsQuery = LeaveRequest::query()
            ->byEmployeeApplicants()
            ->whereRaw('LOWER(status) != ?', ['withdrawn'])
            ->whereYear('leave_requests.created_at', $chartYear);
        DashboardEmployeeFilter::apply($monthlyApplicationsQuery, $employeeFilter);

        $monthlyData = (clone $monthlyApplicationsQuery)
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

        // Monthly status breakdown: when requests were submitted (created_at), same scope as applications chart.
        $monthlyBreakdown = MonthlyLeaveStatusBreakdown::fromQuery(clone $monthlyApplicationsQuery, 'created_at');
        $monthlyApprovedData = $monthlyBreakdown['monthlyApprovedData'];
        $monthlyPendingData = $monthlyBreakdown['monthlyPendingData'];
        $monthlyOverdueData = $monthlyBreakdown['monthlyOverdueData'];
        $monthlyRejectedData = $monthlyBreakdown['monthlyRejectedData'];

        $rawStatusCounts = (clone $baseQuery)->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
        $statusCounts = ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'withdrawn' => 0];
        foreach ($rawStatusCounts as $status => $count) {
            $statusCounts[strtolower(trim((string) $status))] = $count;
        }

        $statusPercentages = PercentageDistribution::oneDecimalPercents([
            'Pending' => (int) $pendingRequestsCount,
            'Overdue' => (int) $overduePendingCount,
            'Rejected' => (int) ($statusCounts['rejected'] ?? 0),
            'Approved' => (int) ($statusCounts['approved'] ?? 0),
        ]);

        $statusPieCounts = [
            'Pending' => (int) $pendingRequestsCount,
            'Overdue' => (int) $overduePendingCount,
            'Rejected' => (int) ($statusCounts['rejected'] ?? 0),
            'Approved' => (int) ($statusCounts['approved'] ?? 0),
        ];

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

        return view('admin.dashboard', [
            'allRequestsCount' => $allRequestsCount,
            'leaveTypeCounts' => $leaveTypeCounts,
            'leaveTypePercentages' => $leaveTypePercentages,
            'pendingRequestsCount' => $pendingRequestsCount,
            'overduePendingCount' => $overduePendingCount,
            'approvedRequestsCount' => $approvedRequestsCount,
            'rejectedRequestsCount' => $rejectedRequestsCount,
            'onLeaveTodayCount' => $onLeaveTodayCount,
            'barLabels' => $barLabels,
            'barData' => $barData,
            'chartYear' => $chartYear,
            'statusCounts' => $statusCounts,
            'statusPercentages' => $statusPercentages,
            'statusPieCounts' => $statusPieCounts,
            'departmentLabels' => $departmentLabels,
            'departmentData' => $departmentData,
            'departmentPercentages' => $departmentPercentages,
            'monthFilter' => $monthFilter,
            'employeeFilter' => $employeeFilter,
            'employees' => $employees,
            'dashboardFilters' => $dashboardFilters,
            'designationLabels' => $designationLabels,
            'designationData' => $designationData,
            'monthlyApprovedData' => $monthlyApprovedData,
            'monthlyPendingData' => $monthlyPendingData,
            'monthlyOverdueData' => $monthlyOverdueData,
            'monthlyRejectedData' => $monthlyRejectedData,
        ]);

        
    }
    
}
