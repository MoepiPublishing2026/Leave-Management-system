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
use Illuminate\Support\Facades\Validator;

class LeaveEmailDecisionController extends Controller
{
    private function actingSenior(LeaveRequest $leave): User
    {
        return LeaveApprovalWorkflow::resolveActingSenior($leave);
    }

    private function signedRouteParams(LeaveRequest $leave): array
    {
        return LeaveApprovalWorkflow::emailDecisionRouteParams($leave);
    }

    private function isAwaitingSenior(LeaveRequest $leave): bool
    {
        $status = strtolower(trim((string) ($leave->status ?? '')));

        return $status === 'pending_senior'
            || ($status === 'pending' && $leave->isManagerApplicant());
    }

    private function signedReviewUrl(LeaveRequest $leave): string
    {
        return URL::temporarySignedRoute(
            'leave.email.review',
            now()->addDays(7),
            $this->signedRouteParams($leave)
        );
    }

    private function redirectToReview(LeaveRequest $leave, ?string $flashKey = null, ?string $flashMessage = null)
    {
        $redirect = redirect()->to($this->signedReviewUrl($leave));

        if ($flashKey && $flashMessage) {
            $redirect->with($flashKey, $flashMessage);
        }

        return $redirect;
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
                $this->signedRouteParams($leaveRequest)
            );
            $rejectUrl = URL::temporarySignedRoute(
                'leave.email.reject.submit',
                now()->addDays(7),
                $this->signedRouteParams($leaveRequest)
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
            'flashSuccess' => session('decision_success'),
            'flashError' => session('decision_error'),
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
        $extension = strtolower(pathinfo($downloadName, PATHINFO_EXTENSION));
        $mime = $disk->mimeType($path) ?: null;
        if (! is_string($mime) || $mime === '' || $mime === 'application/octet-stream') {
            $mime = LeaveAttachmentLinks::mimeForExtension($extension) ?: 'application/octet-stream';
        }
        $previewable = LeaveAttachmentLinks::isPreviewablePath($path);
        $forceDownload = $request->boolean('download');

        if ($forceDownload || ! $previewable) {
            return response()->download($fullPath, $downloadName, ['Content-Type' => $mime]);
        }

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.str_replace('"', '', $downloadName).'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=60',
        ]);
    }

    /**
     * Leftover Approve links from older Action Required emails open the full decision page.
     */
    public function approveGet(Request $request, LeaveRequest $leaveRequest)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'This approval link is invalid or has expired.');
        }

        return $this->redirectToReview($leaveRequest);
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'This approval link is invalid or has expired.');
        }

        $leaveRequest->load('user');

        if (! $this->isAwaitingSenior($leaveRequest)) {
            return $this->redirectToReview(
                $leaveRequest,
                'decision_success',
                'This decision was already submitted. No further action is needed.'
            );
        }

        if (! LeaveProofRules::canApprove($leaveRequest)) {
            return $this->redirectToReview(
                $leaveRequest,
                'decision_error',
                LeaveProofRules::approvalBlockedReason($leaveRequest) ?? 'Supporting proof is required before approval.'
            );
        }

        try {
            LeaveApprovalWorkflow::finalize($leaveRequest, $this->actingSenior($leaveRequest), 'approved');
        } catch (\Throwable $e) {
            report($e);

            $leaveRequest->refresh();
            if (! $this->isAwaitingSenior($leaveRequest)) {
                return $this->redirectToReview(
                    $leaveRequest,
                    'decision_success',
                    'This decision was already submitted. No further action is needed.'
                );
            }

            return $this->redirectToReview($leaveRequest, 'decision_error', $e->getMessage());
        }

        return $this->redirectToReview(
            $leaveRequest,
            'decision_success',
            'Request #'.$leaveRequest->id.' has been approved. The employee and manager have been notified.'
        );
    }

    /**
     * Leftover Reject links from older Action Required emails open the full decision page.
     */
    public function rejectForm(Request $request, LeaveRequest $leaveRequest)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'This rejection link is invalid or has expired.');
        }

        return $this->redirectToReview($leaveRequest);
    }

    public function rejectSubmit(Request $request, LeaveRequest $leaveRequest)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'This rejection link is invalid or has expired.');
        }

        $leaveRequest->load('user');

        if (! $this->isAwaitingSenior($leaveRequest)) {
            return $this->redirectToReview(
                $leaveRequest,
                'decision_success',
                'This decision was already submitted. No further action is needed.'
            );
        }

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->redirectToReview($leaveRequest)
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();

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
                return $this->redirectToReview(
                    $leaveRequest,
                    'decision_success',
                    'This decision was already submitted. No further action is needed.'
                );
            }

            return $this->redirectToReview($leaveRequest, 'decision_error', $e->getMessage());
        }

        return $this->redirectToReview(
            $leaveRequest,
            'decision_success',
            'Request #'.$leaveRequest->id.' has been rejected. The employee and manager have been notified.'
        );
    }
}
