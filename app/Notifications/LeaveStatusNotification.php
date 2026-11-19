<?php

namespace App\Notifications;

use App\Support\LeavePortalLinks;
use App\Support\LeaveStatusLabels;
use App\Support\NotifiesByMail;
use App\Support\SystemMailMessage;
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
        $autoApproved = $this->isAutoApprovedBackdated();

        $mail = SystemMailMessage::for($notifiable)
            ->subject($isApplicant
                ? ($autoApproved ? 'Your leave request was auto-approved' : "Your leave request was {$statusLabel}")
                : ($autoApproved
                    ? "Leave request #{$this->leaveRequest->id} was auto-approved (backdated)"
                    : "Leave request #{$this->leaveRequest->id} was {$statusLabel}"));

        if ($isApplicant) {
            if ($autoApproved) {
                $mail->line("Your {$type} leave request #{$this->leaveRequest->id} was recorded as backdated leave and approved.");
            } else {
                $mail->line("Your {$type} leave request #{$this->leaveRequest->id} has been marked {$statusLabel} by {$this->approverLabel()}.");
            }
            if (strtolower((string) $this->leaveRequest->status) === 'rejected' && filled($this->leaveRequest->rejection_reason)) {
                $mail->line('Reason: '.$this->leaveRequest->rejection_reason);
            }
            if (strtolower((string) $this->leaveRequest->status) === 'approved' && filled($this->leaveRequest->approval_comment)) {
                $mail->line('Reason: '.$this->leaveRequest->approval_comment);
            }
            $mail->action('Open in app', LeavePortalLinks::myRequestsUrl($notifiable, $this->leaveRequest->id));
        } else {
            $applicant = $this->leaveRequest->user;
            $name = trim(($applicant->first_name ?? '').' '.($applicant->last_name ?? '')) ?: 'an employee';
            if ($autoApproved) {
                $mail->line("Leave request #{$this->leaveRequest->id} for {$name} was recorded as backdated leave and approved.");
            } else {
                $mail->line("Leave request #{$this->leaveRequest->id} for {$name} is now {$statusLabel}.");
            }
            if (filled($this->leaveRequest->approval_comment)) {
                $mail->line('Reason: '.$this->leaveRequest->approval_comment);
            }
            $mail->action('Open in app', LeavePortalLinks::teamRequestsUrl($this->leaveRequest->id));
        }

        return $mail;
    }

    public function toDatabase(object $notifiable): array
    {
        $statusLabel = LeaveStatusLabels::display($this->leaveRequest->status);
        $isApplicant = (int) ($notifiable->id ?? 0) === (int) ($this->leaveRequest->user_id ?? 0);
        $autoApproved = $this->isAutoApprovedBackdated();

        if ($isApplicant) {
            $message = $autoApproved
                ? 'Your leave request was recorded as backdated leave and approved (already approved, manager told, or emergency).'
                : "Your leave request has been marked {$statusLabel} by {$this->approverLabel()}.";
        } else {
            $applicant = $this->leaveRequest->user;
            $name = trim(($applicant->first_name ?? '').' '.($applicant->last_name ?? '')) ?: 'an employee';
            $message = $autoApproved
                ? "Leave request #{$this->leaveRequest->id} for {$name} was recorded as backdated leave and approved (already approved, manager told, or emergency)."
                : "Leave request #{$this->leaveRequest->id} for {$name} is now {$statusLabel}.";
        }

        return [
            'leave_request_id' => $this->leaveRequest->id,
            'message' => $message,
            'date' => now(),
        ];
    }

    private function isAutoApprovedBackdated(): bool
    {
        if (strtolower((string) ($this->leaveRequest->handled_action ?? '')) === 'auto_approved') {
            return true;
        }

        $comment = strtolower((string) ($this->leaveRequest->approval_comment ?? ''));

        return strtolower((string) ($this->leaveRequest->status ?? '')) === 'approved'
            && (str_contains($comment, 'backdated') || str_contains($comment, 'automatically approved'));
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
