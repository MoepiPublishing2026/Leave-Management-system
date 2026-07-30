<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Support\LeaveApprovalWorkflow;
use App\Support\LeaveProofRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManagerLeaveRequestController extends Controller
{
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
        $this->assertAdmin();

        $status = strtolower(trim((string) ($leave->status ?? '')));
        $awaitingSenior = $status === 'pending_senior'
            || ($status === 'pending' && $leave->isManagerApplicant());

        if (! $awaitingSenior) {
            return redirect()->back()->with('error', 'Only requests awaiting Senior Manager decision can be approved.');
        }

        if ($status === 'pending' && $leave->isOverdue()) {
            return redirect()->back()->with('error', 'Overdue manager leave still on pending cannot be approved here.');
        }

        if (! LeaveProofRules::canApprove($leave)) {
            return redirect()->back()->with('error', LeaveProofRules::approvalBlockedReason($leave) ?? 'Supporting proof is required before approval.');
        }

        try {
            LeaveApprovalWorkflow::finalize($leave, Auth::user(), 'approved');
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Leave request approved. Employee and manager have been notified.');
    }

    public function reject(Request $request, $id)
    {
        $leave = LeaveRequest::with('user')->findOrFail($id);
        $this->assertAdmin();

        $status = strtolower(trim((string) ($leave->status ?? '')));
        $awaitingSenior = $status === 'pending_senior'
            || ($status === 'pending' && $leave->isManagerApplicant());

        if (! $awaitingSenior) {
            return redirect()->back()->with('error', 'Only requests awaiting Senior Manager decision can be rejected.');
        }

        $request->validate(['rejection_reason' => 'required|string|max:500']);

        try {
            LeaveApprovalWorkflow::finalize($leave, Auth::user(), 'rejected', $request->input('rejection_reason'));
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', 'Leave request rejected. Employee and manager have been notified.');
    }

    public function showRejectionReason($id)
    {
        $this->assertAdmin();
        $request = LeaveRequest::with('user')->findOrFail($id);

        if (strtolower($request->status) !== 'rejected') {
            return redirect()->route('admin.requests', ['status' => 'rejected'])->with('error', 'No rejection reason available.');
        }

        return view('admin.manager-requests.rejection_reason', compact('request'));
    }

    private function assertAdmin(): void
    {
        if (strtolower((string) (Auth::user()->role ?? '')) !== 'admin') {
            abort(403);
        }
    }
}
