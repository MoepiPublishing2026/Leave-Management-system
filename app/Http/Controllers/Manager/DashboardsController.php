<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Notifications\LeaveStatusNotification;
use App\Support\MonthlyLeaveStatusBreakdown;
use App\Support\PercentageDistribution;
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

    public function index(Request $request)
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

        $pendingTotalCount = (clone $baseQuery)->whereRaw('LOWER(status) = ?', ['pending'])->count();
        $overduePendingCount = (clone $baseQuery)->whereRaw('LOWER(status) = ?', ['pending'])
            ->whereDate('start_date', '<', Carbon::today())->count();
        $pendingRequestsCount = max(0, $pendingTotalCount - $overduePendingCount);

        $approvedRequestsCount = (clone $baseQuery)->whereRaw('LOWER(status) = ?', ['approved'])->count();
        $rejectedRequestsCount = (clone $baseQuery)->whereRaw('LOWER(status) = ?', ['rejected'])->count();

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

        $leaveTypeCounts = (clone $baseQuery)->select('leave_type')
            ->groupBy('leave_type')
            ->selectRaw('count(*) as total')
            ->pluck('total', 'leave_type')
            ->sortDesc();
        $leaveTypePercentages = PercentageDistribution::oneDecimalPercents($leaveTypeCounts->toArray());

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
            ->whereRaw('LOWER(status) != ?', ['withdrawn'])
            ->whereYear('created_at', $chartYear);

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
            ->groupBy('department')
            ->pluck('total', 'department');

        // Polar chart: only departments with at least one request (no empty wedges)
        $departmentCounts = $departmentCountsRaw
            ->map(static fn ($v) => (int) $v)
            ->filter(static fn (int $count) => $count > 0)
            ->sortDesc();

        $departmentLabels = $departmentCounts->keys()->toArray();
        $departmentData = $departmentCounts->values()->toArray();
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
            ->select(DB::raw("COALESCE(users.designation, 'Unknown') as designation"), DB::raw('COUNT(leave_requests.id) as total'))
            ->groupBy('designation')
            ->pluck('total', 'designation')
            ->toArray();
        arsort($designationCounts, SORT_NUMERIC);
        $designationLabels = array_keys($designationCounts);
        $designationData = array_values($designationCounts);

        return view('manager.dashboard.index', compact(
            'allRequestsCount', 'pendingRequestsCount', 'approvedRequestsCount', 'rejectedRequestsCount',
            'overduePendingCount', 'onLeaveTodayCount',
            'leaveTypeCounts', 'leaveTypePercentages', 'statusCounts', 'statusPercentages',
            'barLabels', 'barData', 'chartYear', 'departmentLabels', 'departmentData', 'departmentPercentages',
            'monthFilter', 'designationLabels', 'designationData',
            'monthlyApprovedData', 'monthlyPendingData', 'monthlyOverdueData', 'monthlyRejectedData'
        ));
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
