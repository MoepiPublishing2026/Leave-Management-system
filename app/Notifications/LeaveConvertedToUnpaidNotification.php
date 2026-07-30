<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use App\Support\LeaveProofRules;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveConvertedToUnpaidNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly LeaveRequest $leaveRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $applicant = $this->leaveRequest->user;
        $name = trim(($applicant->first_name ?? '').' '.($applicant->last_name ?? '')) ?: 'Staff member';
        $deadline = LeaveProofRules::proofDeadlineAt($this->leaveRequest);

        return [
            'leave_request_id' => $this->leaveRequest->id,
            'message' => sprintf(
                '%s leave request #%d changed from %s to Unpaid because supporting documents were not submitted by %s.',
                $name,
                $this->leaveRequest->id,
                $this->leaveRequest->original_leave_type ?: 'Sick',
                $deadline?->format('d M Y H:i') ?? 'the required deadline'
            ),
            'date' => now(),
        ];
    }
}
