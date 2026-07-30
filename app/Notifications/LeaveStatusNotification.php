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
        $statusLabel = \App\Support\LeaveStatusLabels::display($this->leaveRequest->status);
        $isApplicant = (int) ($notifiable->id ?? 0) === (int) ($this->leaveRequest->user_id ?? 0);

        if ($isApplicant) {
            $message = "Your leave request has been marked {$statusLabel} by {$this->approverLabel()}.";
        } else {
            $applicant = $this->leaveRequest->user;
            $name = trim(($applicant->first_name ?? '').' '.($applicant->last_name ?? '')) ?: 'an employee';
            $message = "Leave request #{$this->leaveRequest->id} for {$name} is now {$statusLabel}.";
        }

        return [
            'leave_request_id' => $this->leaveRequest->id,
            'message' => $message,
            'date' => now(),
        ];
    }

    private function approverLabel(): string
    {
        $role = strtolower((string) ($this->manager->role ?? ''));
        if ($role === 'admin') {
            return 'a senior manager';
        }

        $name = trim(($this->manager->first_name ?? '') . ' ' . ($this->manager->last_name ?? ''));

        return $name !== '' ? $name : 'your manager';
    }
}
