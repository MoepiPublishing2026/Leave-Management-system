<?php

namespace App\Notifications;

use App\Mail\OverdueLeaveMail;
use App\Models\LeaveRequest;
use App\Support\NotifiesByMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;

class LeaveRequestOverdueNotification extends Notification
{
    use Queueable, NotifiesByMail;

    public function __construct(private LeaveRequest $leaveRequest)
    {
        $this->leaveRequest->loadMissing('user');
    }

    public function via(object $notifiable): array
    {
        return $this->channelsFor($notifiable);
    }

    public function toMail(object $notifiable): Mailable
    {
        return (new OverdueLeaveMail($this->leaveRequest, $notifiable))
            ->to($notifiable->email);
    }

    public function toArray(object $notifiable): array
    {
        $mail = new OverdueLeaveMail($this->leaveRequest, $notifiable);

        return [
            'leave_request_id' => $this->leaveRequest->id,
            'message' => $mail->messageBody(),
            'overdue_at' => $this->leaveRequest->start_date,
        ];
    }
}
