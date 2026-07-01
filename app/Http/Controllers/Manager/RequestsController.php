<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Services\UserDeletionService;
use Illuminate\Http\Request;
use App\Mail\LeaveResponseMail;
use Illuminate\Support\Facades\Mail;
use App\Notifications\LeaveStatusNotification;
use Illuminate\Support\Facades\Auth;
use App\Support\LeaveProofRules;
use App\Support\LeaveRequestSearch;
use App\Support\LeaveRequestPeriodFilter;
use App\Support\DashboardEmployeeFilter;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Notifications\OverdueLeaveEscalatedNotification;
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
        $status = strtolower((string) $request->query('status', 'all'));
        $search = trim((string) $request->query('search', ''));
        $highlightRequestId = (int) $request->query('request_id', 0);
        if ($highlightRequestId > 0 && $search === '') {
            $search = (string) $highlightRequestId;
        }
        $searchKey = strtolower($search);
        $monthFilter = $request->query('month');
        $employeeFilter = DashboardEmployeeFilter::selectedId($request->query('employee'));
        $query = LeaveRequest::query()->byEmployeeApplicants()->with('user');

        LeaveRequestPeriodFilter::apply($query, is_string($monthFilter) ? $monthFilter : null, 'created_at');
        DashboardEmployeeFilter::apply($query, $employeeFilter);

        if ($status === 'overdue') {
            $query->whereRaw('LOWER(TRIM(status)) = ?', ['pending'])
                ->whereDate('start_date', '<', Carbon::today());
        } elseif ($status === 'pending') {
            $query->whereRaw('LOWER(TRIM(status)) = ?', ['pending'])
                ->whereDate('start_date', '>=', Carbon::today());
        } elseif ($status !== 'all') {
            $query->whereRaw('LOWER(TRIM(status)) = ?', [$status]);
        } else {
            $query->whereRaw('LOWER(TRIM(status)) != ?', ['withdrawn']);
        }

        $effectiveSearch = $search;
        if ($status === 'all' && $searchKey === 'overdue') {
            $query->whereRaw('LOWER(TRIM(status)) = ?', ['pending'])
                ->whereDate('start_date', '<', Carbon::today());
            $effectiveSearch = '';
        } elseif ($status === 'all' && $searchKey === 'pending') {
            $query->whereRaw('LOWER(TRIM(status)) = ?', ['pending'])
                ->whereDate('start_date', '>=', Carbon::today());
            $effectiveSearch = '';
        }

        if ($effectiveSearch !== '') {
            LeaveRequestSearch::apply($query, $effectiveSearch);
        }

        $requests = $query->orderByDesc('created_at')->paginate(10)->withQueryString();
        return view('manager.requests.index', compact('requests', 'status', 'search', 'monthFilter', 'employeeFilter'));
    }

  


public function approve($id)
{
    $leave = LeaveRequest::findOrFail($id);

    if (strtolower($leave->status) !== 'pending') {
        return redirect()->back()->with('error', 'Only pending requests can be approved.');
    }

    if ($leave->isOverdue()) {
        return redirect()->back()->with('error', 'Overdue requests cannot be approved. Submit a late response reason instead.');
    }

    if (! LeaveProofRules::canApprove($leave)) {
        return redirect()->back()->with('error', LeaveProofRules::approvalBlockedReason($leave) ?? 'Supporting proof is required before approval.');
    }

    $this->ensureHandledColumnsExist();
    $leave->status = 'approved';
    $leave->handled_by = Auth::id();
    $leave->handled_at = Carbon::now();
    $leave->handled_action = 'approved';
    $leave->save();

    $conversation = LeaveRequestConversation::firstOrCreate([
        'leave_request_id' => $leave->id,
        'manager_id' => Auth::id(),
    ]);
    LeaveRequestMessage::create([
        'conversation_id' => $conversation->id,
        'sender_id' => Auth::id(),
        'body' => 'Decision: Approved.',
    ]);
    $conversation->touch();

    // Notify user in-app (database)
    $manager = Auth::user();
    try {
        $leave->user->notify(new LeaveStatusNotification($leave, $manager));
    } catch (\Throwable $e) {
        report($e);
    }

    // Send email, but don't fail the approval if mail is misconfigured
    try {
        Mail::to($leave->user->email)->send(new LeaveResponseMail($leave));
    } catch (\Throwable $e) {
        report($e);
    }

    return redirect()->back()->with('success', 'Request approved and employee notified.');
}

public function reject(Request $r, $id)
{
    $leave = LeaveRequest::findOrFail($id);

    if (strtolower($leave->status) !== 'pending') {
        return redirect()->back()->with('error', 'Only pending requests can be rejected.');
    }

    if ($leave->isOverdue()) {
        return redirect()->back()->with('error', 'Overdue requests cannot be rejected. Submit a late response reason instead.');
    }

    $r->validate(['rejection_reason' => 'required|string|max:500']);

    $this->ensureHandledColumnsExist();
    $leave->status = 'rejected';
    $leave->rejection_reason = $r->input('rejection_reason');
    $leave->handled_by = Auth::id();
    $leave->handled_at = Carbon::now();
    $leave->handled_action = 'rejected';
    $leave->save();

    $conversation = LeaveRequestConversation::firstOrCreate([
        'leave_request_id' => $leave->id,
        'manager_id' => Auth::id(),
    ]);
    LeaveRequestMessage::create([
        'conversation_id' => $conversation->id,
        'sender_id' => Auth::id(),
        'body' => 'Decision: Rejected. Reason: ' . $leave->rejection_reason,
    ]);
    $conversation->touch();

    // Notify user in-app (database)
    $manager = Auth::user();
    try {
        $leave->user->notify(new LeaveStatusNotification($leave, $manager));
    } catch (\Throwable $e) {
        report($e);
    }

    // Send email, but don't fail the rejection if mail is misconfigured
    try {
        Mail::to($leave->user->email)->send(new LeaveResponseMail($leave));
    } catch (\Throwable $e) {
        report($e);
    }

    return redirect()->back()->with('success', 'Request rejected and employee notified.');
}

public function escalate(Request $r, $id)
{
    $leave = LeaveRequest::with('user')->findOrFail($id);

    if (!$leave->isOverdue()) {
        return redirect()->back()->with('error', 'Only overdue pending requests can be escalated.');
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

    $admins = User::where('role', 'admin')->get();
    $managerName = trim((Auth::user()->first_name ?? '') . ' ' . (Auth::user()->last_name ?? ''));
    if ($managerName === '') {
        $managerName = 'Manager';
    }
    foreach ($admins as $admin) {
        $admin->notify(new OverdueLeaveEscalatedNotification($leave, $managerName));
    }

    return redirect()->back()->with('success', 'Late response reason sent to admin.');
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
        $currentYear = (int) \Carbon\Carbon::now()->year;
        $requestYear = $leaveRequest->start_date ? (int) \Carbon\Carbon::parse($leaveRequest->start_date)->year : $currentYear;
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
