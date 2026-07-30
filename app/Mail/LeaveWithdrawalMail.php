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

    public function __construct(LeaveRequest $leaveRequest, string $previousStatus = '')
    {
        $this->leaveRequest = $leaveRequest;
        $this->previousStatus = $previousStatus;
    }

    public function build()
    {
        return $this->subject('Leave Request Withdrawal Notification')
                    ->view('emails.leave_withdrawal');
    }
}
