<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\LeaveRequest;

use App\Support\LeaveWorkingDays;

class LeaveRequestSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $leaveRequest;
    public $workingDays;
    public string $recipientFirstName = '';

    public function __construct(LeaveRequest $leaveRequest, ?string $recipientFirstName = null)
    {
        $this->leaveRequest = $leaveRequest;
        $this->workingDays = $this->calculateWorkingDays($leaveRequest);
        $this->recipientFirstName = trim((string) $recipientFirstName);
    }

    private function calculateWorkingDays(LeaveRequest $leaveRequest)
    {
        return LeaveWorkingDays::count($leaveRequest);
    }

    public function build()
    {
        return $this->subject('New Leave Request Submitted')
                    ->view('emails.leave_request_submitted');
    }
}
