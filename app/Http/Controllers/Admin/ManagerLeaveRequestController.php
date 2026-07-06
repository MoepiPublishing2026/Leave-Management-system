<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\LeaveResponseMail;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestConversation;
use App\Models\LeaveRequestMessage;
use App\Notifications\LeaveStatusNotification;
use App\Support\LeaveProofRules;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ManagerLeaveRequestController extends Controller
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
        if (strtolower((string) (auth()->user()->role ?? '')) !== 'admin') {
            abort(403);
        }

        return redirect()->route('admin.requests', $request->query());
    }

    public function approve($id)
    {
        $leave = LeaveRequest::with('user')->findOrFail($id);

        if (! $leave->isManagerApplicant()) {
            abort(404);
        }

        if (strtolower($leave->status) !== 'pending') {
            return redirect()->back()->with('error', 'Only pending requests can be approved.');
        }

        if ($leave->isOverdue()) {
            return redirect()->back()->with('error', 'Overdue requests cannot be approved.');
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

        $conversation = LeaveRequestConversation::firstOrCreate(
            ['leave_request_id' => $leave->id, 'manager_id' => $leave->user_id],
            ['admin_id' => Auth::id()]
        );
        if (! $conversation->admin_id) {
            $conversation->admin_id = Auth::id();
            $conversation->save();
        }
        LeaveRequestMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'body' => 'Decision: Approved.',
        ]);
        $conversation->touch();

        $admin = Auth::user();
        try {
            $leave->user->notify(new LeaveStatusNotification($leave, $admin));
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            Mail::to($leave->user->email)->send(new LeaveResponseMail($leave));
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->back()->with('success', 'Manager leave request approved.');
    }

    public function reject(Request $request, $id)
    {
        $leave = LeaveRequest::with('user')->findOrFail($id);

        if (! $leave->isManagerApplicant()) {
            abort(404);
        }

        if (strtolower($leave->status) !== 'pending') {
            return redirect()->back()->with('error', 'Only pending requests can be rejected.');
        }

        if ($leave->isOverdue()) {
            return redirect()->back()->with('error', 'Overdue requests cannot be rejected.');
        }

        $request->validate(['rejection_reason' => 'required|string|max:500']);

        $this->ensureHandledColumnsExist();
        $leave->status = 'rejected';
        $leave->rejection_reason = $request->input('rejection_reason');
        $leave->handled_by = Auth::id();
        $leave->handled_at = Carbon::now();
        $leave->handled_action = 'rejected';
        $leave->save();

        $conversation = LeaveRequestConversation::firstOrCreate(
            ['leave_request_id' => $leave->id, 'manager_id' => $leave->user_id],
            ['admin_id' => Auth::id()]
        );
        if (! $conversation->admin_id) {
            $conversation->admin_id = Auth::id();
            $conversation->save();
        }
        LeaveRequestMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'body' => 'Decision: Rejected. Reason: '.$leave->rejection_reason,
        ]);
        $conversation->touch();

        $admin = Auth::user();
        try {
            $leave->user->notify(new LeaveStatusNotification($leave, $admin));
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            Mail::to($leave->user->email)->send(new LeaveResponseMail($leave));
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->back()->with('success', 'Manager leave request rejected.');
    }

    public function showRejectionReason($id)
    {
        $request = LeaveRequest::with('user')->findOrFail($id);

        if (! $request->isManagerApplicant()) {
            abort(404);
        }

        if (strtolower($request->status) !== 'rejected') {
            return redirect()->route('admin.requests', ['status' => 'rejected'])->with('error', 'No rejection reason available.');
        }

        return view('admin.manager-requests.rejection_reason', compact('request'));
    }
}
