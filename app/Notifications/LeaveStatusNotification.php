<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LeaveStatusNotification extends Notification
{
    use Queueable;

    protected $leaveRequest;
    protected $manager;

    public function __construct($leaveRequest, $manager)
    {
        $this->leaveRequest = $leaveRequest;
        $this->manager = $manager;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toMail($notifiable)
    {
        $status = ucfirst($this->leaveRequest->status);
        $approver = $this->approverLabel();

        return (new MailMessage)
            ->subject("Leave Request $status")
            ->greeting("Hello {$notifiable->first_name},")
            ->line("Your leave request has been $status by {$approver}.")
            ->action('View Leave Requests', url('/leave/myRequests'))
            ->line('Thank you for using our system!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'leave_request_id' => $this->leaveRequest->id,
            'message' => "Your leave request has been {$this->leaveRequest->status} by {$this->approverLabel()}.",
            'date' => now(),
        ];
    }

    private function approverLabel(): string
    {
        $role = strtolower((string) ($this->manager->role ?? ''));
        if ($role === 'admin') {
            return 'an administrator';
        }

        $name = trim(($this->manager->first_name ?? '') . ' ' . ($this->manager->last_name ?? ''));

        return $name !== '' ? $name : 'your manager';
    }
}
