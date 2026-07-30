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

    public function __construct(LeaveRequest $leaveRequest)
    {
        $this->leaveRequest = $leaveRequest->loadMissing('user');
        $this->proofDeadline = LeaveProofRules::proofDeadlineAt($leaveRequest)?->format('d M Y H:i');
    }

    public function build()
    {
        return $this->subject('Leave request #'.$this->leaveRequest->id.' changed to Unpaid')
            ->view('emails.leave_converted_to_unpaid');
    }
}
