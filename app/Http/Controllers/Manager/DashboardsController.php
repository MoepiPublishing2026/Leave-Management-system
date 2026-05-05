<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Notifications\LeaveStatusNotification;
use App\Support\PercentageDistribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
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

        $pendingTotalCount = (clone $baseQuery)->whereRaw('LOWER(status) = ?', ['pending'])->count();
        $overduePendingCount = (clone $baseQuery)->whereRaw('LOWER(status) = ?', ['pending'])
            ->whereDate('start_date', '<', Carbon::today())->count();
        $pendingRequestsCount = max(0, $pendingTotalCount - $overduePendingCount);

        $approvedRequestsCount = (clone $baseQuery)->whereRaw('LOWER(status) = ?', ['approved'])->count();
        $rejectedRequestsCount = (clone $baseQuery)->whereRaw('LOWER(status) = ?', ['rejected'])->count();
        
        $leaveTypeCounts = (clone $baseQuery)->select('leave_type')
            ->groupBy('leave_type')
            ->selectRaw('count(*) as total')
            ->pluck('total', 'leave_type')
            ->sortDesc();
        $leaveTypePercentages = PercentageDistribution::oneDecimalPercents($leaveTypeCounts->toArray());

        $statusCounts = [
            'Pending' => $pendingRequestsCount,
            'Rejected' => $rejectedRequestsCount,
            'Approved' => $approvedRequestsCount,
        ];
        $statusPercentages = PercentageDistribution::oneDecimalPercents([
            'Pending' => (int) $pendingRequestsCount,
            'Rejected' => (int) $rejectedRequestsCount,
            'Approved' => (int) $approvedRequestsCount,
        ]);

        // Monthly leave requests grouped by leave start_date (aligns with calendar)
        $monthlyData = (clone $baseQuery)->select(
            DB::raw('YEAR(start_date) as year'),
            DB::raw('MONTH(start_date) as month'),
            DB::raw('COUNT(*) as total')
        )
        ->groupBy('year', 'month')
        ->orderBy('year')
        ->orderBy('month')
        ->get();

        $barLabels = [];
        $barData = [];
        foreach ($monthlyData as $data) {
            $barLabels[] = date('M Y', mktime(0, 0, 0, $data->month, 1, $data->year));
            $barData[] = $data->total;
        }

        $departmentCountsRaw = (clone $baseQuery)
            ->join('users', 'leave_requests.user_id', '=', 'users.id')
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
        
        return view('manager.dashboard.index', compact(
            'allRequestsCount', 'pendingRequestsCount', 'approvedRequestsCount', 'rejectedRequestsCount',
            'overduePendingCount',
            'leaveTypeCounts', 'leaveTypePercentages', 'statusCounts', 'statusPercentages',
            'barLabels', 'barData', 'departmentLabels', 'departmentData', 'departmentPercentages',
            'monthFilter', 'avgLeaveTypeLabels', 'avgLeaveTypeData', 'weekdayLabels', 'weekdayData'
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
