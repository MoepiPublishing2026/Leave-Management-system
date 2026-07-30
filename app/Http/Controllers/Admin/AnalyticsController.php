<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Support\PercentageDistribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
         $monthFilter = $request->query('month', 'this_year');
        $staff = $request->query('staff', 'all');

        $leaveQuery = LeaveRequest::query()
            ->byStaffApplicants()
            ->whereRaw('LOWER(status) != ?', ['withdrawn']);

        if (ctype_digit((string) $staff)) {
            $leaveQuery->where('user_id', (int) $staff);
        }

        if ($monthFilter) {
            $now = Carbon::now();

            if (ctype_digit((string) $monthFilter)) {
                $leaveQuery->whereYear('start_date', (int) $monthFilter);
            } elseif ($monthFilter === 'this_month') {
                $leaveQuery->whereYear('start_date', $now->year)
                           ->whereMonth('start_date', $now->month);
            } elseif ($monthFilter === 'this_quarter') {
                $quarter = ceil($now->month / 3);
                $startMonth = ($quarter - 1) * 3 + 1;
                $endMonth = $startMonth + 2;

                $leaveQuery->whereYear('start_date', $now->year)
                           ->whereBetween(DB::raw('MONTH(start_date)'), [$startMonth, $endMonth]);
            } elseif ($monthFilter === 'this_year') {
                $leaveQuery->whereYear('start_date', $now->year);
            } else {
                // Specific month filter YYYY-MM
                $leaveQuery->whereYear('start_date', substr($monthFilter, 0, 4))
                           ->whereMonth('start_date', substr($monthFilter, 5, 2));
            }
        }

        $totalLeaves = $leaveQuery->count();

        $pendingTotal = (clone $leaveQuery)
            ->whereIn(DB::raw('LOWER(TRIM(status))'), ['pending', 'pending_senior'])
            ->count();
        $overduePending = (clone $leaveQuery)->whereRaw('LOWER(status) = ?', ['pending'])
            ->whereDate('start_date', '<', Carbon::today())
            ->count();
        $pendingRequests = max(0, $pendingTotal - $overduePending);

        $activeEmployees = User::whereIn('role', ['employee', 'manager'])->where('is_active', 1)->count();
        $totalEmployees = User::whereIn('role', ['employee', 'manager'])->count();
        $employeesWhoApplied = (clone $leaveQuery)->distinct('user_id')->count('user_id');

        $approvedCount = (clone $leaveQuery)->whereRaw('LOWER(status) = ?', ['approved'])->count();
        $approvalRate = $totalLeaves > 0 ? round(($approvedCount / $totalLeaves) * 100, 1) : 0;

        $leavesByType = (clone $leaveQuery)
            ->select('leave_type', DB::raw('count(*) as total'))
            ->groupBy('leave_type')
            ->orderByDesc('total')
            ->get();

        $leavesByDesignation = (clone $leaveQuery)
            ->join('users', 'leave_requests.user_id', '=', 'users.id')
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

        $months = LeaveRequest::query()
            ->byStaffApplicants()
            ->selectRaw("DATE_FORMAT(start_date, '%Y-%m') as month")
            ->distinct()
            ->orderBy('month', 'desc')
            ->pluck('month');

        $nowYear = (int) Carbon::now()->year;
        $years = collect(range($nowYear - 1, $nowYear - 5));

        $staffOptions = User::whereIn('role', ['employee', 'manager'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email', 'role']);

        if ($staffOptions->isNotEmpty()) {
            $staffOptions = $staffOptions->unique(function ($u) {
                return strtolower(trim((string) ($u->email ?? ''))) ?: (string) $u->id;
            })->values();
        }

        $typePercentages = [];
        if ($totalLeaves > 0 && $leavesByType->isNotEmpty()) {
            $typeCounts = $leavesByType->pluck('total', 'leave_type')->map(fn ($v) => (int) $v)->toArray();
            $typePercentages = PercentageDistribution::oneDecimalPercents($typeCounts);
        }

        $designationPercentages = [];
        if (!empty($designationLabels) && !empty($designationData)) {
            $designationCounts = [];
            foreach ($designationLabels as $i => $label) {
                $designationCounts[(string) $label] = (int) ($designationData[$i] ?? 0);
            }
            $designationPercentages = PercentageDistribution::oneDecimalPercents($designationCounts);
        }

        return view('admin.analytics.index', compact(
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
            'staffOptions',
            'monthFilter',
            'staff'
        ));
    }
}
