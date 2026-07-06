<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use App\Support\LeaveReminderMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class LeaveReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public LeaveRequest $leaveRequest,
        public string $kind,
        public Carbon $referenceDate
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function reminderKey(): string
    {
        return sprintf(
            'leave_%s_%s_%s',
            $this->leaveRequest->id,
            $this->kind,
            $this->referenceDate->toDateString()
        );
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'leave_request_id' => $this->leaveRequest->id,
            'reminder_key' => $this->reminderKey(),
            'kind' => $this->kind,
            'message' => LeaveReminderMessage::build($notifiable, $this->leaveRequest, $this->kind, $this->referenceDate),
            'date' => now(),
        ];
    }
}
