<?php

namespace App\Mail;

use App\Models\LeaveRequest;
use App\Support\LeaveProofRules;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LeaveConvertedToUnpaidMail extends Mailable
{
    use Queueable, SerializesModels;

    public LeaveRequest $leaveRequest;

    public ?string $proofDeadline;

    public string $recipientFirstName = '';

    public string $portalUrl = '';

    public function __construct(LeaveRequest $leaveRequest, ?string $recipientFirstName = null, ?string $portalUrl = null)
    {
        $this->leaveRequest = $leaveRequest->loadMissing('user');
        $this->proofDeadline = LeaveProofRules::proofDeadlineAt($leaveRequest)?->format('d M Y H:i');
        $this->recipientFirstName = trim((string) $recipientFirstName);
        $this->portalUrl = trim((string) $portalUrl) ?: url('/login');
    }

    public function build()
    {
        return $this->subject('Leave request #'.$this->leaveRequest->id.' changed to Unpaid')
            ->view('emails.leave_converted_to_unpaid');
    }
}
