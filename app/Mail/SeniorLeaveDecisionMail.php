<?php

namespace App\Mail;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Support\LeaveAttachmentLinks;
use App\Support\LeaveDaysFormatter;
use App\Support\LeaveProofRules;
use App\Support\LeaveWorkingDays;
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

    public string $approveUrl;

    public string $rejectUrl;

    public string $reviewUrl;

    public string $viewUrl;

    /** @var list<array{label: string, viewUrl: string, downloadUrl: string, extension: string, name: string, previewable: bool}> */
    public array $documentLinks = [];

    public function __construct(LeaveRequest $leaveRequest)
    {
        $this->leaveRequest = $leaveRequest->loadMissing(['user', 'user.manager']);
        $this->workingDays = LeaveWorkingDays::count($this->leaveRequest);
        $this->documentLinks = LeaveAttachmentLinks::forEmailRequest($this->leaveRequest);

        $expires = now()->addDays(7);
        $this->approveUrl = URL::temporarySignedRoute(
            'leave.email.approve',
            $expires,
            ['leaveRequest' => $this->leaveRequest->id]
        );
        $this->rejectUrl = URL::temporarySignedRoute(
            'leave.email.reject.form',
            $expires,
            ['leaveRequest' => $this->leaveRequest->id]
        );
        $this->reviewUrl = URL::temporarySignedRoute(
            'leave.email.review',
            $expires,
            ['leaveRequest' => $this->leaveRequest->id]
        );
        $this->viewUrl = url('/admin/requests?status=pending&search='.$this->leaveRequest->id);
    }

    public function build()
    {
        $applicant = $this->leaveRequest->user;
        $name = trim(($applicant->first_name ?? '').' '.($applicant->last_name ?? '')) ?: 'Employee';

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

        return trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: 'Manager';
    }
}
