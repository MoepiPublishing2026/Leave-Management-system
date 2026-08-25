<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use App\Support\LeaveApplicant;
use App\Support\LeavePortalLinks;
use App\Support\NotifiesByMail;
use App\Support\SystemMailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveSubmittedNotification extends Notification
{
    use Queueable, NotifiesByMail;

    public function __construct(private LeaveRequest $leaveRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return $this->channelsFor($notifiable);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $approver = LeaveApplicant::approverLabel($notifiable);
        $type = $this->leaveRequest->leave_type ?: 'leave';

        return SystemMailMessage::for($notifiable)
            ->subject('Leave request submitted — #'.$this->leaveRequest->id)
            ->line("Your {$type} leave request #{$this->leaveRequest->id} has been submitted.")
            ->line('It is now waiting for '.$approver.' to review.')
            ->action('Open in app', LeavePortalLinks::myRequestsUrl($notifiable, $this->leaveRequest->id));
    }

    public function toDatabase(object $notifiable): array
    {
        $approver = LeaveApplicant::approverLabel($notifiable);
        $type = $this->leaveRequest->leave_type ?: 'leave';

        return [
            'leave_request_id' => $this->leaveRequest->id,
            'message' => "Your {$type} leave request #{$this->leaveRequest->id} was submitted and is waiting for {$approver}.",
            'date' => now(),
        ];
    }
}
