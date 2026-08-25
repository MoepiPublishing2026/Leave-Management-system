<?php

namespace App\Notifications;

use App\Support\LeavePortalLinks;
use App\Support\SystemMailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveRespondedNotification extends Notification
{
    use Queueable;

    protected $leaveRequest;

    public function __construct($leaveRequest)
    {
        $this->leaveRequest = $leaveRequest;
    }

    public function via($notifiable)
    {
        return ['mail']; // send email
    }

    public function toMail($notifiable)
    {
        $status = ucfirst($this->leaveRequest->status);

        return SystemMailMessage::for($notifiable)
                    ->subject("Your Leave Request Has Been $status")
                    ->line("Your leave request has been {$status}.")
                    ->action('Open in app', LeavePortalLinks::myRequestsUrl($notifiable, $this->leaveRequest->id ?? null));
    }
}
