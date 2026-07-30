<?php

namespace App\Mail;

use App\Models\LeaveRequest;
use App\Support\LeaveDaysFormatter;
use App\Support\LeaveWorkingDays;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PendingReviewReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public LeaveRequest $leaveRequest;

    public string $stageLabel;

    public float $workingDays;

    public function __construct(LeaveRequest $leaveRequest, string $stageLabel)
    {
        $this->leaveRequest = $leaveRequest->loadMissing('user');
        $this->stageLabel = $stageLabel;
        $this->workingDays = LeaveWorkingDays::count($this->leaveRequest);
    }

    public function build()
    {
        return $this->subject('Reminder: Leave request #'.$this->leaveRequest->id.' still awaiting your review')
            ->view('emails.pending_review_reminder');
    }
}
