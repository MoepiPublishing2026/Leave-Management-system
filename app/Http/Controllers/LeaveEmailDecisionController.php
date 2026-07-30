<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Support\LeaveApprovalWorkflow;
use App\Support\LeaveAttachmentLinks;
use App\Support\LeaveDaysFormatter;
use App\Support\LeaveProofRules;
use App\Support\LeaveStatusLabels;
use App\Support\LeaveWorkingDays;
use App\Support\PersonalLeaveDashboardData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class LeaveEmailDecisionController extends Controller
{
    private function actingSenior(?LeaveRequest $leave = null): User
    {
        if (auth()->check() && strtolower((string) (auth()->user()->role ?? '')) === 'admin') {
            return auth()->user();
        }

        $admin = User::query()
            ->where('role', 'admin')
            ->where('is_active', true)
            ->orderBy('id')
            ->first();

        if (! $admin) {
            abort(503, 'No Senior Manager account is available to record this decision.');
        }

        return $admin;
    }

    private function isAwaitingSenior(LeaveRequest $leave): bool
    {
        $status = strtolower(trim((string) ($leave->status ?? '')));

        return $status === 'pending_senior'
            || ($status === 'pending' && $leave->isManagerApplicant());
    }

    private function alreadySubmittedResponse(LeaveRequest $leave)
    {
        $status = LeaveStatusLabels::display($leave->status);
        $decision = strtolower(trim((string) ($leave->status ?? '')));

        if (in_array($decision, ['approved', 'rejected'], true)) {
            return response()->view('emails.leave_email_decision_result', [
                'title' => 'Decision already submitted',
                'message' => 'Request #'.$leave->id.' was already marked as '.$status.'. No further action is needed.',
                'ok' => true,
                'submitted' => true,
                'finalStatus' => $status,
            ]);
        }

        return response()->view('emails.leave_email_decision_result', [
            'title' => 'Decision already submitted',
            'message' => 'This leave request is no longer awaiting a Senior Manager decision (current status: '.$status.').',
            'ok' => true,
            'submitted' => true,
            'finalStatus' => $status,
        ]);
    }

    private function remainingBalanceLabel(LeaveRequest $leave): string
    {
        $user = $leave->user;
        if (! $user) {
            return 'N/A';
        }

        try {
            $data = PersonalLeaveDashboardData::build((int) $user->id);
            $type = (string) $leave->leave_type;
            foreach (($data['leaveBalances'] ?? []) as $balanceType => $remaining) {
                if (strcasecmp((string) $balanceType, $type) === 0) {
                    return LeaveDaysFormatter::format((float) $remaining).' days';
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return 'See details below';
    }

    private function recommenderName(LeaveRequest $leave): string
    {
        if (! $leave->recommended_by) {
            return '—';
        }
        $user = User::query()->find($leave->recommended_by);
        if (! $user) {
            return '—';
        }

        return trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: 'Manager';
    }

    public function review(Request $request, LeaveRequest $leaveRequest)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'This review link is invalid or has expired.');
        }

        $leaveRequest->load('user');
        $awaiting = $this->isAwaitingSenior($leaveRequest);
        $attachments = LeaveAttachmentLinks::forEmailRequest($leaveRequest);
        $approveUrl = null;
        $rejectUrl = null;

        if ($awaiting) {
            $approveUrl = URL::temporarySignedRoute(
                'leave.email.approve',
                now()->addDays(7),
                ['leaveRequest' => $leaveRequest->id]
            );
            $rejectUrl = URL::temporarySignedRoute(
                'leave.email.reject.form',
                now()->addDays(7),
                ['leaveRequest' => $leaveRequest->id]
            );
        }

        $recommendation = strtolower((string) ($leaveRequest->manager_recommendation ?? ''));
        $recLabel = match ($recommendation) {
            'approve' => 'Recommend Approve',
            'reject' => 'Recommend Reject',
            default => '—',
        };

        return view('emails.leave_email_review', [
            'leaveRequest' => $leaveRequest,
            'awaiting' => $awaiting,
            'attachments' => $attachments,
            'approveUrl' => $approveUrl,
            'rejectUrl' => $rejectUrl,
            'workingDays' => LeaveWorkingDays::count($leaveRequest),
            'remainingBalance' => $this->remainingBalanceLabel($leaveRequest),
            'recommenderName' => $this->recommenderName($leaveRequest),
            'recLabel' => $recLabel,
            'canApproveWithProof' => LeaveProofRules::canApprove($leaveRequest),
            'proofBlockReason' => LeaveProofRules::approvalBlockedReason($leaveRequest),
            'statusLabel' => LeaveStatusLabels::display($leaveRequest->status),
        ]);
    }

    public function attachment(Request $request, LeaveRequest $leaveRequest, int $attachment)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'This attachment link is invalid or has expired.');
        }

        $files = LeaveAttachmentLinks::paths($leaveRequest);
        $idx = max(1, (int) $attachment) - 1;
        $path = $files[$idx] ?? null;
        if (! is_string($path) || trim($path) === '') {
            abort(404);
        }

        $disk = Storage::disk('public');
        $path = ltrim($path, '/');
        if (! $disk->exists($path)) {
            abort(404);
        }

        $fullPath = $disk->path($path);
        $downloadName = basename($path) ?: ('attachment_'.($idx + 1));
        $mime = $disk->mimeType($path) ?: 'application/octet-stream';
        $previewable = LeaveAttachmentLinks::isPreviewablePath($path);
        $forceDownload = $request->boolean('download');

        if ($forceDownload || ! $previewable) {
            return response()->download($fullPath, $downloadName, ['Content-Type' => $mime]);
        }

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.str_replace('"', '', $downloadName).'"',
        ]);
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'This approval link is invalid or has expired.');
        }

        $leaveRequest->load('user');

        if (! $this->isAwaitingSenior($leaveRequest)) {
            return $this->alreadySubmittedResponse($leaveRequest);
        }

        if (! LeaveProofRules::canApprove($leaveRequest)) {
            return response()->view('emails.leave_email_decision_result', [
                'title' => 'Cannot approve yet',
                'message' => LeaveProofRules::approvalBlockedReason($leaveRequest) ?? 'Supporting proof is required before approval.',
                'ok' => false,
                'submitted' => false,
            ], 422);
        }

        try {
            LeaveApprovalWorkflow::finalize($leaveRequest, $this->actingSenior($leaveRequest), 'approved');
        } catch (\Throwable $e) {
            report($e);

            $leaveRequest->refresh();
            if (! $this->isAwaitingSenior($leaveRequest)) {
                return $this->alreadySubmittedResponse($leaveRequest);
            }

            return response()->view('emails.leave_email_decision_result', [
                'title' => 'Unable to approve',
                'message' => $e->getMessage(),
                'ok' => false,
                'submitted' => false,
            ], 422);
        }

        return response()->view('emails.leave_email_decision_result', [
            'title' => 'Decision submitted',
            'message' => 'Request #'.$leaveRequest->id.' has been approved. The employee and manager have been notified.',
            'ok' => true,
            'submitted' => true,
            'finalStatus' => 'Approved',
        ]);
    }

    public function rejectForm(Request $request, LeaveRequest $leaveRequest)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'This rejection link is invalid or has expired.');
        }

        $leaveRequest->load('user');

        if (! $this->isAwaitingSenior($leaveRequest)) {
            return $this->alreadySubmittedResponse($leaveRequest);
        }

        $submitUrl = URL::temporarySignedRoute(
            'leave.email.reject.submit',
            now()->addDays(7),
            ['leaveRequest' => $leaveRequest->id]
        );

        $attachments = LeaveAttachmentLinks::forEmailRequest($leaveRequest);

        return view('emails.leave_email_reject_form', [
            'leaveRequest' => $leaveRequest,
            'submitUrl' => $submitUrl,
            'attachments' => $attachments,
            'workingDays' => LeaveWorkingDays::count($leaveRequest),
            'remainingBalance' => $this->remainingBalanceLabel($leaveRequest),
            'recommenderName' => $this->recommenderName($leaveRequest),
        ]);
    }

    public function rejectSubmit(Request $request, LeaveRequest $leaveRequest)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'This rejection link is invalid or has expired.');
        }

        $leaveRequest->load('user');

        if (! $this->isAwaitingSenior($leaveRequest)) {
            return $this->alreadySubmittedResponse($leaveRequest);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        try {
            LeaveApprovalWorkflow::finalize(
                $leaveRequest,
                $this->actingSenior($leaveRequest),
                'rejected',
                $validated['rejection_reason']
            );
        } catch (\Throwable $e) {
            report($e);

            $leaveRequest->refresh();
            if (! $this->isAwaitingSenior($leaveRequest)) {
                return $this->alreadySubmittedResponse($leaveRequest);
            }

            return response()->view('emails.leave_email_decision_result', [
                'title' => 'Unable to reject',
                'message' => $e->getMessage(),
                'ok' => false,
                'submitted' => false,
            ], 422);
        }

        return response()->view('emails.leave_email_decision_result', [
            'title' => 'Decision submitted',
            'message' => 'Request #'.$leaveRequest->id.' has been rejected. The employee and manager have been notified.',
            'ok' => true,
            'submitted' => true,
            'finalStatus' => 'Rejected',
        ]);
    }
}
