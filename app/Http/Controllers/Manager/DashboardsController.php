<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Notifications\LeaveStatusNotification;
use App\Support\MonthlyLeaveStatusBreakdown;
use App\Support\PercentageDistribution;
use App\Support\LeaveRequestPeriodFilter;
use App\Support\DashboardEmployeeFilter;
use App\Support\PersonalLeaveDashboardData;
use App\Support\LeaveReminderService;
use App\Support\ManagerTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Yasumi\Yasumi;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
class DashboardsController extends Controller
{
    private function ensureHandledColumnsExist(): void
    {
        if (Schema::hasColumn('leave_requests', 'handled_by')) {
            return;
        }

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('handled_by')->nullable();
            $table->timestamp('handled_at')->nullable();
            $table->string('handled_action', 40)->nullable();
        });
    }

    public function personal(Request $request)
    {
        LeaveReminderService::syncForUser(Auth::user());

        return view('dashboard', array_merge(
            PersonalLeaveDashboardData::build((int) Auth::id()),
            [
                'layout' => 'layouts.manager',
                'pageTitle' => 'My Leave Dashboard',
                'teamDashboardUrl' => route('manager.dashboard'),
            ]
        ));
    }

    public function team(Request $request)
    {
        LeaveReminderService::syncForUser(Auth::user());

        $monthFilter = $request->query('month', 'this_year');
        $employeeFilter = DashboardEmployeeFilter::selectedId($request->query('employee'));
        $employees = DashboardEmployeeFilter::employees();
        $dashboardFilters = DashboardEmployeeFilter::queryParams($monthFilter, $employeeFilter);

        $baseQuery = LeaveRequest::query()->byEmployeeApplicants()->whereRaw('LOWER(TRIM(status)) != ?', ['withdrawn']);
        ManagerTeam::scopeLeaveRequests($baseQuery);
        LeaveRequestPeriodFilter::apply($baseQuery, $monthFilter, 'created_at');
        DashboardEmployeeFilter::apply($baseQuery, $employeeFilter);

        $allRequestsCount = (clone $baseQuery)->count();

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
                ->tap(fn ($q) => ManagerTeam::scopeLeaveRequests($q))
                ->when($employeeFilter, fn ($q) => $q->where('user_id', $employeeFilter))
                ->whereRaw('LOWER(TRIM(status)) = ?', ['approved'])
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->distinct('user_id')
                ->count('user_id');
        }

        $leaveTypeCounts = (clone $baseQuery)->select('leave_type')
            ->groupBy('leave_type')
            ->selectRaw('count(*) as total')
            ->pluck('total', 'leave_type')
            ->sortDesc();
        $leaveTypePercentages = PercentageDistribution::oneDecimalPercents($leaveTypeCounts->toArray());
        $payStatusCounts = [
            'Paid' => (clone $baseQuery)
                ->whereRaw("LOWER(TRIM(COALESCE(leave_type, ''))) != ?", ['unpaid'])
                ->count(),
            'Unpaid' => (clone $baseQuery)
                ->whereRaw('LOWER(TRIM(leave_type)) = ?', ['unpaid'])
                ->count(),
        ];
        $payStatusPercentages = PercentageDistribution::oneDecimalPercents($payStatusCounts);

        $statusCounts = [
            'Pending' => $pendingRequestsCount,
            'Overdue' => $overduePendingCount,
            'Rejected' => $rejectedRequestsCount,
            'Approved' => $approvedRequestsCount,
        ];
        $statusPercentages = PercentageDistribution::oneDecimalPercents([
            'Pending' => (int) $pendingRequestsCount,
            'Overdue' => (int) $overduePendingCount,
            'Rejected' => (int) $rejectedRequestsCount,
            'Approved' => (int) $approvedRequestsCount,
        ]);

        // Monthly applications line chart: when requests were submitted (created_at).
        $chartYear = ctype_digit((string) $monthFilter) ? (int) $monthFilter : (int) Carbon::now()->year;
        $barLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $barData = array_fill(0, 12, 0);

        $monthlyApplicationsQuery = LeaveRequest::query()
            ->byEmployeeApplicants()
            ->tap(fn ($q) => ManagerTeam::scopeLeaveRequests($q))
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

        $departmentCountsRaw = (clone $baseQuery)
            ->join('users', 'leave_requests.user_id', '=', 'users.id')
            ->where('users.role', 'employee')
            ->select(DB::raw("COALESCE(users.department, 'Unknown') as department"), DB::raw('count(leave_requests.id) as total'))
            ->groupBy(DB::raw("COALESCE(users.department, 'Unknown')"))
            ->pluck('total', 'department');

        // Department bar chart: only departments with at least one request.
        $departmentCounts = $departmentCountsRaw
            ->map(static fn ($v) => (int) $v)
            ->filter(static fn (int $count) => $count > 0)
            ->sortDesc();

        $departmentLabels = $departmentCounts->keys()->toArray();
        $departmentData = $departmentCounts->values()->toArray();

        return view('manager.team-dashboard.index', [
            'allRequestsCount' => $allRequestsCount,
            'pendingRequestsCount' => $pendingRequestsCount,
            'approvedRequestsCount' => $approvedRequestsCount,
            'rejectedRequestsCount' => $rejectedRequestsCount,
            'overduePendingCount' => $overduePendingCount,
            'onLeaveTodayCount' => $onLeaveTodayCount,
            'leaveTypeCounts' => $leaveTypeCounts,
            'leaveTypePercentages' => $leaveTypePercentages,
            'payStatusCounts' => $payStatusCounts,
            'payStatusPercentages' => $payStatusPercentages,
            'statusCounts' => $statusCounts,
            'statusPercentages' => $statusPercentages,
            'barLabels' => $barLabels,
            'barData' => $barData,
            'chartYear' => $chartYear,
            'departmentLabels' => $departmentLabels,
            'departmentData' => $departmentData,
            'monthFilter' => $monthFilter,
            'employeeFilter' => $employeeFilter,
            'employees' => $employees,
            'dashboardFilters' => $dashboardFilters,
            'monthlyApprovedData' => $monthlyApprovedData,
            'monthlyPendingData' => $monthlyPendingData,
            'monthlyOverdueData' => $monthlyOverdueData,
            'monthlyRejectedData' => $monthlyRejectedData,
        ]);
    }



    public function approve($id)
{
    $leaveRequest = LeaveRequest::findOrFail($id);

    if (strtolower($leaveRequest->status) !== 'pending') {
        return redirect()->back()->with('error', 'Only pending requests can be approved.');
    }

    $this->ensureHandledColumnsExist();
    $leaveRequest->status = 'approved';
    $leaveRequest->handled_by = Auth::id();
    $leaveRequest->handled_at = Carbon::now();
    $leaveRequest->handled_action = 'approved';
    $leaveRequest->save();

    $manager = Auth::user();

    // This is where you notify the employee that their leave was approved
    $leaveRequest->user->notify(new LeaveStatusNotification($leaveRequest, $manager));

    return redirect()->route('manager.dashboard')->with('success', 'Request approved.');
}

public function reject(Request $request, $id)
{
    $leaveRequest = LeaveRequest::findOrFail($id);

    if (strtolower($leaveRequest->status) !== 'pending') {
        return redirect()->back()->with('error', 'Only pending requests can be rejected.');
    }

    if (strtolower((string) $leaveRequest->leave_type) === 'maternity') {
        return redirect()->back()->with('error', 'Maternity leave must be granted and cannot be rejected.');
    }

    $request->validate([
        'rejection_reason' => 'required|string|max:500',
    ]);

    $this->ensureHandledColumnsExist();
    $leaveRequest->status = 'rejected';
    $leaveRequest->rejection_reason = $request->input('rejection_reason');
    $leaveRequest->handled_by = Auth::id();
    $leaveRequest->handled_at = Carbon::now();
    $leaveRequest->handled_action = 'rejected';
    $leaveRequest->save();

    $manager = Auth::user();

    // Notify employee of rejection here
    $leaveRequest->user->notify(new LeaveStatusNotification($leaveRequest, $manager));

    return redirect()->route('manager.dashboard')->with('success', 'Request rejected.');
}


    public function showRejectionReason($id)
    {
        $request = LeaveRequest::findOrFail($id);
        return view('manager.requests.rejection_reason', compact('request'));
    }
}
