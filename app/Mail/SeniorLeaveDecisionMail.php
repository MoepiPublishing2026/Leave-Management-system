<?php

namespace App\Mail;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Support\LeaveAttachmentLinks;
use App\Support\LeaveDaysFormatter;
use App\Support\LeavePortalLinks;
use App\Support\LeaveProofRules;
use App\Support\LeaveWorkingDays;
use App\Support\EmailGreeting;
use App\Support\LeaveApprovalWorkflow;
use App\Support\PersonalLeaveDashboardData;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class SeniorLeaveDecisionMail extends Mailable
{
    use Queueable, SerializesModels;

    public LeaveRequest $leaveRequest;

    public float $workingDays;

    public string $reviewUrl;

    public string $approveUrl;

    public string $rejectUrl;

    public string $viewUrl;

    /** @var list<array{label: string, viewUrl: string, downloadUrl: string, extension: string, name: string, previewable: bool}> */
    public array $documentLinks = [];

    public string $recipientFirstName = '';

    public string $applicantName = '';

    public string $managerName = '';

    public bool $isManagerApplicant = false;

    public function __construct(LeaveRequest $leaveRequest, ?string $recipientFirstName = null, ?User $recipient = null)
    {
        $this->leaveRequest = $leaveRequest->loadMissing(['user', 'user.manager']);
        $this->workingDays = LeaveWorkingDays::count($this->leaveRequest);
        $this->documentLinks = LeaveAttachmentLinks::forEmailRequest($this->leaveRequest);
        $this->recipientFirstName = trim((string) $recipientFirstName);

        $context = LeaveApprovalWorkflow::reportingContext($this->leaveRequest);
        $this->applicantName = $context['applicant_name'];
        $this->managerName = $context['manager_name'];
        $this->isManagerApplicant = $context['is_manager_applicant'];

        $params = LeaveApprovalWorkflow::emailDecisionRouteParams($this->leaveRequest, $recipient);
        $expires = now()->addDays(7);
        $this->reviewUrl = URL::temporarySignedRoute(
            'leave.email.review',
            $expires,
            $params
        );
        $this->approveUrl = URL::temporarySignedRoute(
            'leave.email.approve.get',
            $expires,
            $params
        );
        $this->rejectUrl = URL::temporarySignedRoute(
            'leave.email.reject.form',
            $expires,
            $params
        );
        $this->viewUrl = LeavePortalLinks::seniorInAppUrl($this->leaveRequest->id);
    }

    public function build()
    {
        $applicant = $this->leaveRequest->user;
        $name = EmailGreeting::displayName($applicant, 'Employee');

        return $this->subject('Action required: Leave request #'.$this->leaveRequest->id.' — '.$name)
            ->view('emails.senior_leave_decision')
            ->with([
                'remainingBalance' => $this->remainingBalanceLabel(),
                'recommenderName' => $this->recommenderName(),
                'canApproveWithProof' => LeaveProofRules::canApprove($this->leaveRequest),
                'proofBlockReason' => LeaveProofRules::approvalBlockedReason($this->leaveRequest),
            ]);
    }

    public function remainingBalanceLabel(): string
    {
        $user = $this->leaveRequest->user;
        if (! $user) {
            return 'N/A';
        }

        try {
            $data = PersonalLeaveDashboardData::build((int) $user->id);
            $type = (string) $this->leaveRequest->leave_type;
            $balances = $data['leaveBalances'] ?? [];
            foreach ($balances as $balanceType => $remaining) {
                if (strcasecmp((string) $balanceType, $type) === 0) {
                    return LeaveDaysFormatter::format((float) $remaining).' days';
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return 'See app for balance';
    }

    public function recommenderName(): string
    {
        if (! $this->leaveRequest->recommended_by) {
            return '—';
        }
        $user = User::query()->find($this->leaveRequest->recommended_by);
        if (! $user) {
            return '—';
        }

        return EmailGreeting::displayName($user, 'Manager');
    }
}
