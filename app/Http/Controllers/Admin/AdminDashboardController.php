<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Support\Carbon;
use Yasumi\Yasumi;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $allRequestsCount = LeaveRequest::whereRaw('LOWER(status) != ?', ['withdrawn'])->count();

        $leaveTypeCounts = LeaveRequest::select('leave_type', DB::raw('count(*) as total'))
            ->whereRaw('LOWER(status) = ?', ['approved'])
            ->groupBy('leave_type')
            ->pluck('total', 'leave_type')
            ->toArray();

        $pendingTotalCount = LeaveRequest::whereRaw('LOWER(status) = ?', ['pending'])->count();

        $overduePendingCount = LeaveRequest::whereRaw('LOWER(status) = ?', ['pending'])
            ->whereDate('start_date', '<', Carbon::today())
            ->count();
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
            $onLeaveTodayCount = LeaveRequest::whereRaw('LOWER(status) = ?', ['approved'])
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->distinct('user_id')
                ->count('user_id');
        }

        $activeEmployeesCount = User::where('is_active', 1)->count();

        $barLabels = [];
        $barData = [];
        for ($i = 1; $i <= 12; $i++) {
            $barLabels[] = date('F', mktime(0, 0, 0, $i, 10));
            $barData[] = LeaveRequest::whereRaw('LOWER(status) = ?', ['approved'])
                ->whereMonth('created_at', $i)
                ->count();
        }

        $rawStatusCounts = LeaveRequest::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
        $statusCounts = ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'withdrawn' => 0];
        foreach ($rawStatusCounts as $status => $count) {
            $statusCounts[strtolower(trim((string) $status))] = $count;
        }

        $departmentCounts = LeaveRequest::join('users', 'leave_requests.user_id', '=', 'users.id')
            ->whereRaw('LOWER(leave_requests.status) = ?', ['approved'])
            ->select(DB::raw("COALESCE(users.department, 'Unknown') as department"), DB::raw('count(*) as total'))
            ->groupBy('department')
            ->orderBy('total', 'desc')
            ->pluck('total', 'department')
            ->toArray();

        $departmentLabels = array_keys($departmentCounts);
        $departmentData = array_values($departmentCounts);

        return view('admin.dashboard', [
            'allRequestsCount' => $allRequestsCount,
            'leaveTypeCounts' => $leaveTypeCounts,
            'pendingRequestsCount' => $pendingRequestsCount,
            'overduePendingCount' => $overduePendingCount,
            'onLeaveTodayCount' => $onLeaveTodayCount,
            'activeEmployeesCount' => $activeEmployeesCount,
            'barLabels' => $barLabels,
            'barData' => $barData,
            'statusCounts' => $statusCounts,
            'departmentLabels' => $departmentLabels,
            'departmentData' => $departmentData,
        ]);

        
    }
    
}
