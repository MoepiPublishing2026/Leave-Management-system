<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveWithdrawnNotification extends Notification
{
    use Queueable;

    public function __construct(
        private LeaveRequest $leaveRequest,
        private string $previousStatus
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $applicantName = trim(
            ($this->leaveRequest->user->first_name ?? '').' '.
            ($this->leaveRequest->user->last_name ?? '')
        );
        $applicantName = $applicantName !== '' ? $applicantName : 'Staff member';
        $wasApproved = strtolower(trim($this->previousStatus)) === 'approved';

        return [
            'leave_request_id' => $this->leaveRequest->id,
            'message' => $applicantName.' withdrew '.
                ($wasApproved ? 'an approved ' : 'a ').
                $this->leaveRequest->leave_type.' leave request.',
            'previous_status' => $this->previousStatus,
        ];
    }
}
