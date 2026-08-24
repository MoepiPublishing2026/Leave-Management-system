<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\LeaveRequest;

class LeaveWithdrawalMail extends Mailable
{
    use Queueable, SerializesModels;

    public $leaveRequest;
    public $previousStatus;
    public string $recipientFirstName = '';
    public string $portalUrl = '';

    public function __construct(
        LeaveRequest $leaveRequest,
        string $previousStatus = '',
        ?string $recipientFirstName = null,
        ?string $portalUrl = null
    ) {
        $this->leaveRequest = $leaveRequest;
        $this->previousStatus = $previousStatus;
        $this->recipientFirstName = trim((string) $recipientFirstName);
        $this->portalUrl = trim((string) $portalUrl) ?: url('/login');
    }

    public function build()
    {
        return $this->subject('Leave Request Withdrawal Notification')
                    ->view('emails.leave_withdrawal');
    }
}
