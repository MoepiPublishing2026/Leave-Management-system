<?php

namespace App\Notifications;

use App\Support\LeavePortalLinks;
use App\Support\LeaveStatusLabels;
use App\Support\NotifiesByMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveStatusNotification extends Notification
{
    use Queueable, NotifiesByMail;

    protected $leaveRequest;
    protected $manager;

    public function __construct($leaveRequest, $manager)
    {
        $this->leaveRequest = $leaveRequest;
        $this->manager = $manager;
    }

    public function via(object $notifiable): array
    {
        return $this->channelsFor($notifiable);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusLabel = LeaveStatusLabels::display($this->leaveRequest->status);
        $isApplicant = (int) ($notifiable->id ?? 0) === (int) ($this->leaveRequest->user_id ?? 0);
        $type = $this->leaveRequest->leave_type ?: 'leave';

        $mail = (new MailMessage)
            ->subject($isApplicant
                ? "Your leave request was {$statusLabel}"
                : "Leave request #{$this->leaveRequest->id} was {$statusLabel}")
            ->greeting('Hello '.trim((string) ($notifiable->first_name ?? '')).',');

        if ($isApplicant) {
            $mail->line("Your {$type} leave request #{$this->leaveRequest->id} has been marked {$statusLabel} by {$this->approverLabel()}.");
            if (strtolower((string) $this->leaveRequest->status) === 'rejected' && filled($this->leaveRequest->rejection_reason)) {
                $mail->line('Reason: '.$this->leaveRequest->rejection_reason);
            }
            $mail->action('View my requests', LeavePortalLinks::myRequestsUrl($notifiable, $this->leaveRequest->id));
        } else {
            $applicant = $this->leaveRequest->user;
            $name = trim(($applicant->first_name ?? '').' '.($applicant->last_name ?? '')) ?: 'an employee';
            $mail->line("Leave request #{$this->leaveRequest->id} for {$name} is now {$statusLabel}.");
            $mail->action('Open team requests', LeavePortalLinks::teamRequestsUrl($this->leaveRequest->id));
        }

        return $mail;
    }

    public function toDatabase(object $notifiable): array
    {
        $statusLabel = LeaveStatusLabels::display($this->leaveRequest->status);
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

        $name = trim(($this->manager->first_name ?? '').' '.($this->manager->last_name ?? ''));

        return $name !== '' ? $name : 'your manager';
    }
}
