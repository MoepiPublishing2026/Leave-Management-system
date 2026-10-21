<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Support\LeaveTypeChartCounts;
use App\Support\PercentageDistribution;
use App\Support\LeaveRequestPeriodFilter;
use App\Support\LeaveYear;
use App\Support\ManagerTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticController extends Controller
{
    public function index(Request $request)
    {
          $monthFilter = $request->query('month', 'this_year');
          $staff = $request->query('staff', 'all');

    

        $leaveQuery = LeaveRequest::query()
            ->byEmployeeApplicants()
            ->tap(fn ($q) => ManagerTeam::scopeLeaveRequests($q))
            ->whereRaw('LOWER(status) != ?', ['withdrawn']);  // Exclude withdrawn

        if (ctype_digit((string) $staff)) {
            $leaveQuery->where('user_id', (int) $staff);
        }

        if ($monthFilter) {
            if (preg_match('/^\d{4}-\d{2}$/', (string) $monthFilter)) {
                $leaveQuery->whereYear('start_date', substr($monthFilter, 0, 4))
                           ->whereMonth('start_date', substr($monthFilter, 5, 2));
            } else {
                LeaveRequestPeriodFilter::apply($leaveQuery, $monthFilter, 'start_date');
            }
        }

        $totalLeaves = $leaveQuery->count();
        $pendingTotal = (clone $leaveQuery)->whereRaw('LOWER(TRIM(status)) in (?, ?)', ['pending', 'pending_senior'])->count();
        $overduePending = (clone $leaveQuery)->overdue()->count();
        $pendingRequests = max(0, $pendingTotal - $overduePending);

        $activeEmployees = User::employees()->where('is_active', 1)->whereIn('id', ManagerTeam::employeeIdsFor())->count();
        $totalEmployees = User::employees()->whereIn('id', ManagerTeam::employeeIdsFor())->count();
        $employeesWhoApplied = (clone $leaveQuery)->distinct('user_id')->count('user_id');

        $approvedCount = (clone $leaveQuery)->whereRaw('LOWER(status) = ?', ['approved'])->count();
        $approvalRate = $totalLeaves > 0 ? round(($approvedCount / $totalLeaves) * 100, 1) : 0;

        $typeCounts = LeaveTypeChartCounts::fromQuery($leaveQuery);
        $leavesByType = LeaveTypeChartCounts::toRows($typeCounts);

        // Count distinct employees (not requests) per designation for this filter window
        $leavesByDesignation = (clone $leaveQuery)
            ->join('users', 'leave_requests.user_id', '=', 'users.id')
            ->where('users.role', 'employee')
            ->select(DB::raw("COALESCE(users.department, 'Unknown') as designation"), DB::raw('COUNT(DISTINCT leave_requests.user_id) as total'))
            ->groupBy(DB::raw("COALESCE(users.department, 'Unknown')"))
            ->get();

        $designationLabels = $leavesByDesignation->pluck('designation')->toArray();
        $designationData = $leavesByDesignation->pluck('total')->toArray();

        $topLeaveTakers = (clone $leaveQuery)
            ->select('user_id', DB::raw('count(*) as total'))
            ->groupBy('user_id')
            ->with('user')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $totalForTop = $topLeaveTakers->sum('total');
        if ($totalForTop > 0 && $topLeaveTakers->count() > 0) {
            $topCounts = $topLeaveTakers->mapWithKeys(fn ($t) => [(int) $t->user_id => (int) $t->total])->toArray();
            $topPct = PercentageDistribution::oneDecimalPercents($topCounts);
            foreach ($topLeaveTakers as $taker) {
                $taker->percentage = $topPct[(int) $taker->user_id] ?? 0.0;
            }
        } else {
            foreach ($topLeaveTakers as $taker) {
                $taker->percentage = 0.0;
            }
        }

        $months = LeaveRequest::selectRaw("DATE_FORMAT(start_date, '%Y-%m') as month")
            ->distinct()
            ->orderBy('month', 'desc')
            ->pluck('month');

        $nowYear = LeaveYear::current();
        $years = collect(range($nowYear - 1, $nowYear - 5));

        $staffOptions = User::employees()
            ->whereIn('id', ManagerTeam::employeeIdsFor())
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        if ($staffOptions->isNotEmpty()) {
            $staffOptions = $staffOptions->unique(function ($u) {
                return strtolower(trim((string) ($u->email ?? ''))) ?: (string) $u->id;
            })->values();
        }

        $typePercentages = $typeCounts !== []
            ? PercentageDistribution::oneDecimalPercents($typeCounts)
            : [];

        $designationPercentages = [];
        if (!empty($designationLabels) && !empty($designationData)) {
            $designationCounts = [];
            foreach ($designationLabels as $i => $label) {
                $designationCounts[(string) $label] = (int) ($designationData[$i] ?? 0);
            }
            $designationPercentages = PercentageDistribution::oneDecimalPercents($designationCounts);
        }

        return view('manager.analytics.index', compact(
            'totalLeaves',
            'pendingRequests',
            'activeEmployees',
            'totalEmployees',
            'employeesWhoApplied',
            'approvedCount',
            'approvalRate',
            'leavesByType',
            'typePercentages',
            'topLeaveTakers',
            'totalForTop',
            'designationLabels',
            'designationData',
            'designationPercentages',
            'months',
            'years',
            'monthFilter',
            'staffOptions',
            'staff'
        ));
    }
}
