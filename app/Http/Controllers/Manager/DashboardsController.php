<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Notifications\LeaveStatusNotification;
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

    public function index()
    {
        $allRequestsCount = LeaveRequest::whereRaw('LOWER(status) != ?', ['withdrawn'])->count();

        $pendingTotalCount = LeaveRequest::whereRaw('LOWER(status) = ?', ['pending'])->count();
        $overduePendingCount = LeaveRequest::whereRaw('LOWER(status) = ?', ['pending'])
            ->whereDate('start_date', '<', Carbon::today())
            ->count();
        $pendingRequestsCount = max(0, $pendingTotalCount - $overduePendingCount);

        $approvedRequestsCount = LeaveRequest::whereRaw('LOWER(status) = ?', ['approved'])->count();
        $rejectedRequestsCount = LeaveRequest::whereRaw('LOWER(status) = ?', ['rejected'])->count();
        
        $leaveTypeCounts = LeaveRequest::select('leave_type')
            ->whereRaw('LOWER(status) != ?', ['withdrawn'])
            ->groupBy('leave_type')
            ->selectRaw('count(*) as total')
            ->pluck('total', 'leave_type');
        
        $statusCounts = [
            'Pending' => $pendingRequestsCount,
            'Rejected' => $rejectedRequestsCount,
            'Approved' => $approvedRequestsCount,
        ];
        
        // Monthly leave requests count grouped by year-month
        $monthlyData = LeaveRequest::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as total')
        )
        ->whereRaw('LOWER(status) != ?', ['withdrawn'])
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

        $departmentCounts = LeaveRequest::join('users', 'leave_requests.user_id', '=', 'users.id')
            ->whereRaw('LOWER(leave_requests.status) != ?', ['withdrawn'])
            ->select(DB::raw("COALESCE(users.department, 'Unknown') as department"), DB::raw('count(*) as total'))
            ->groupBy('department')
            ->orderBy('total', 'desc')
            ->pluck('total', 'department');

        $departmentLabels = $departmentCounts->keys()->toArray();
        $departmentData = $departmentCounts->values()->toArray();
        
        return view('manager.dashboard.index', compact(
            'allRequestsCount', 'pendingRequestsCount', 'approvedRequestsCount', 'rejectedRequestsCount',
            'overduePendingCount',
            'leaveTypeCounts', 'statusCounts', 'barLabels', 'barData', 'departmentLabels', 'departmentData'
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
