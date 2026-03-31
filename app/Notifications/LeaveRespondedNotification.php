<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

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
        $url = url('/leave/myRequests'); // or wherever the user checks leave status

        return (new MailMessage)
                    ->subject("Your Leave Request Has Been $status")
                    ->greeting("Hello {$notifiable->first_name},")
                    ->line("Your leave request has been {$status}.")
                    ->action('Check your leave status', $url)
                    ->line('Thank you for using our leave management system!');
    }
}
