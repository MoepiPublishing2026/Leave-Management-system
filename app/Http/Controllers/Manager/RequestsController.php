<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Services\UserDeletionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\LeaveProofRules;
use App\Support\LeaveRequestSearch;
use App\Support\LeaveRequestPeriodFilter;
use App\Support\LeavePayFilter;
use App\Support\DashboardEmployeeFilter;
use App\Support\OverdueLeaveEscalation;
use App\Support\OverdueLeaveNotifier;
use App\Support\ManagerTeam;
use App\Support\LeaveApprovalWorkflow;
use App\Support\MissingProofUnpaidConverter;
use Illuminate\Support\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\LeaveRequestConversation;
use App\Models\LeaveRequestMessage;



class RequestsController extends Controller
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
        MissingProofUnpaidConverter::convertDue();
        try {
            OverdueLeaveNotifier::notifyDue();
        } catch (\Throwable $e) {
            report($e);
        }

        $status = strtolower((string) $request->query('status', 'all'));
        $payFilter = LeavePayFilter::normalize($request->query('pay', 'all'));
        $search = trim((string) $request->query('search', ''));
        $highlightRequestId = (int) $request->query('request_id', 0);
        if ($highlightRequestId > 0 && $search === '') {
            $search = (string) $highlightRequestId;
        }
        $searchKey = strtolower($search);
        $monthFilter = $request->query('month');
        $employeeFilter = DashboardEmployeeFilter::selectedId($request->query('employee'));
        $query = LeaveRequest::query()->byEmployeeApplicants()->with('user');
        ManagerTeam::scopeLeaveRequests($query);

        LeaveRequestPeriodFilter::apply($query, is_string($monthFilter) ? $monthFilter : null, 'created_at');
        DashboardEmployeeFilter::apply($query, $employeeFilter);
        LeavePayFilter::apply($query, $payFilter);

        if ($status === 'overdue') {
            $query->overdue();
        } elseif ($status === 'pending_senior') {
            $query->whereRaw('LOWER(TRIM(status)) = ?', ['pending_senior'])
                ->whereDate('start_date', '>=', Carbon::today());
        } elseif ($status === 'pending') {
            $query->whereRaw('LOWER(TRIM(status)) in (?, ?)', ['pending', 'pending_senior'])
                ->whereDate('start_date', '>=', Carbon::today());
        } elseif ($status !== 'all') {
            $query->whereRaw('LOWER(TRIM(status)) = ?', [$status]);
        } else {
            $query->whereRaw('LOWER(TRIM(status)) != ?', ['withdrawn']);
        }

        $effectiveSearch = $search;
        if ($status === 'all' && $searchKey === 'overdue') {
            $query->overdue();
            $effectiveSearch = '';
        } elseif ($status === 'all' && $searchKey === 'pending') {
            $query->whereRaw('LOWER(TRIM(status)) in (?, ?)', ['pending', 'pending_senior'])
                ->whereDate('start_date', '>=', Carbon::today());
            $effectiveSearch = '';
        }

        if ($effectiveSearch !== '') {
            LeaveRequestSearch::apply($query, $effectiveSearch);
        }

        $requests = $query->orderByDesc('created_at')->paginate(10)->withQueryString();
        $overdueNeedingReason = OverdueLeaveEscalation::pendingReasonQueue();

        return view('manager.requests.index', compact(
            'requests',
            'status',
            'payFilter',
            'search',
            'monthFilter',
            'employeeFilter',
            'overdueNeedingReason'
        ));
    }

  


public function recommend(Request $r, $id)
{
    $leave = LeaveRequest::with('user')->findOrFail($id);

    if ($leave->isManagerApplicant()) {
        return redirect()->back()->with('error', 'Manager leave requests are decided by a senior manager.');
    }

    if (! ManagerTeam::employeeIdsFor()->contains((int) $leave->user_id)) {
        return redirect()->back()->with('error', 'You can only manage leave for employees assigned to you.');
    }

    if (strtolower($leave->status) !== 'pending') {
        return redirect()->back()->with('error', 'Only requests awaiting manager review can be recommended.');
    }

    if ($leave->isOverdue()) {
        return redirect()->back()->with('error', 'Overdue requests cannot be recommended. Submit a late response reason instead.');
    }

    $recommendation = strtolower((string) $r->input('recommendation', 'approve'));
    if (! in_array($recommendation, ['approve', 'reject'], true)) {
        $recommendation = 'approve';
    }

    if ($recommendation === 'approve' && ! LeaveProofRules::canApprove($leave)) {
        return redirect()->back()->with('error', LeaveProofRules::approvalBlockedReason($leave) ?? 'Supporting proof is required before recommending approval.');
    }

    if ($recommendation === 'reject') {
        $r->validate([
            'manager_recommendation_comment' => 'nullable|string|max:1000',
            'rejection_reason' => 'nullable|string|max:1000',
        ]);
    } else {
        $r->validate(['manager_recommendation_comment' => 'nullable|string|max:1000']);
    }

    $comment = $r->input('manager_recommendation_comment');
    if (($comment === null || trim((string) $comment) === '') && $r->filled('rejection_reason')) {
        $comment = $r->input('rejection_reason');
    }

    try {
        LeaveApprovalWorkflow::recommend($leave, Auth::user(), $recommendation, $comment);
    } catch (\Throwable $e) {
        report($e);

        return redirect()->back()->with('error', 'Unable to submit recommendation. Please try again.');
    }

    return redirect()->back()->with('success', 'Recommendation sent to Senior Manager. They can approve or reject from email or the app.');
}

public function recommendReject(Request $r, $id)
{
    $r->merge(['recommendation' => 'reject']);

    return $this->recommend($r, $id);
}

public function escalate(Request $r, $id)
{
    $leave = LeaveRequest::with('user')->findOrFail($id);

    if (!$leave->isOverdue()) {
        return redirect()->back()->with('error', 'Only overdue pending requests can be escalated.');
    }

    if (! ManagerTeam::employeeIdsFor()->contains((int) $leave->user_id)) {
        return redirect()->back()->with('error', 'You can only manage leave for employees assigned to you.');
    }

    $r->validate(['overdue_escalation_reason' => 'required|string|max:1000']);

    if (!Schema::hasColumn('leave_requests', 'overdue_escalation_reason')) {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->text('overdue_escalation_reason')->nullable();
            $table->timestamp('overdue_escalated_at')->nullable();
            $table->unsignedBigInteger('overdue_escalated_by')->nullable();
        });
    }

    $leave->overdue_escalation_reason = $r->input('overdue_escalation_reason');
    $leave->overdue_escalated_at = Carbon::now();
    $leave->overdue_escalated_by = Auth::id();
    $this->ensureHandledColumnsExist();
    $leave->handled_by = Auth::id();
    $leave->handled_at = Carbon::now();
    $leave->handled_action = 'unattended';
    $leave->status = 'pending_senior';
    $leave->pending_review_reminder_stage = 0;
    $leave->last_pending_review_reminder_at = null;
    $leave->save();

    $conversation = LeaveRequestConversation::firstOrCreate([
        'leave_request_id' => $leave->id,
        'manager_id' => Auth::id(),
    ]);
    LeaveRequestMessage::create([
        'conversation_id' => $conversation->id,
        'sender_id' => Auth::id(),
        'body' => 'Unattended request. Late response reason: ' . $leave->overdue_escalation_reason,
    ]);
    $conversation->touch();

    $managerName = trim((Auth::user()->first_name ?? '') . ' ' . (Auth::user()->last_name ?? ''));
    if ($managerName === '') {
        $managerName = 'Manager';
    }
    LeaveApprovalWorkflow::notifySeniorsOfOverdueEscalation($leave, $managerName);

    if ($r->expectsJson()) {
        return response()->json([
            'success' => true,
            'message' => 'Late response reason sent to senior manager.',
            'remaining' => OverdueLeaveEscalation::pendingReasonQueue()->count(),
        ]);
    }

    return redirect()->back()->with('success', 'Late response reason sent to senior manager.');
}


    public function showRejectionReason($id)
    {
        $request = LeaveRequest::findOrFail($id);
        if (strtolower($request->status) !== 'rejected') {
            return redirect()->route('manager.requests.index')->with('error', 'No rejection reason available.');
        }
        return view('manager.requests.rejection_reason', compact('request'));
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        $role = strtolower((string) (auth()->user()->role ?? ''));
        if (!in_array($role, ['manager', 'admin'], true)) {
            abort(403);
        }

        $status = strtolower(trim((string) ($leaveRequest->status ?? '')));
        $currentYear = \App\Support\LeaveYear::current();
        $requestYear = $leaveRequest->start_date
            ? \App\Support\LeaveYear::forDate($leaveRequest->start_date)
            : $currentYear;
        $isPreviousYear = $requestYear === ($currentYear - 1);
        if (! $isPreviousYear) {
            return redirect()
                ->back()
                ->with('error', 'Only requests from the previous year can be deleted.');
        }

        try {
            $requestId = $leaveRequest->id;
            app(UserDeletionService::class)->deleteLeaveRequestRecord($leaveRequest);

            return redirect()
                ->back()
                ->with('success', 'Leave request #' . $requestId . ' deleted permanently.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->with('error', 'Unable to delete this leave request. Please try again.');
        }
    }
}
