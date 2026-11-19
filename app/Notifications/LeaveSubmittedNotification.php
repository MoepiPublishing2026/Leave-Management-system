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
        $autoApproved = strtolower((string) ($this->leaveRequest->handled_action ?? '')) === 'auto_approved';

        $mail = SystemMailMessage::for($notifiable)
            ->subject($autoApproved
                ? 'Leave request auto-approved — #'.$this->leaveRequest->id
                : 'Leave request submitted — #'.$this->leaveRequest->id)
            ->line("Your {$type} leave request #{$this->leaveRequest->id} has been submitted.");

        if ($autoApproved) {
            $mail->line('It was recorded as backdated leave and approved.');
            if (filled($this->leaveRequest->approval_comment)) {
                $mail->line('Reason: '.$this->leaveRequest->approval_comment);
            }
        } else {
            $mail->line('It is now waiting for '.$approver.' to review.');
        }

        return $mail->action('Open in app', LeavePortalLinks::myRequestsUrl($notifiable, $this->leaveRequest->id));
    }

    public function toDatabase(object $notifiable): array
    {
        $approver = LeaveApplicant::approverLabel($notifiable);
        $type = $this->leaveRequest->leave_type ?: 'leave';
        $autoApproved = strtolower((string) ($this->leaveRequest->handled_action ?? '')) === 'auto_approved'
            || (
                strtolower((string) ($this->leaveRequest->status ?? '')) === 'approved'
                && filled($this->leaveRequest->approval_comment)
                && str_contains(strtolower((string) $this->leaveRequest->approval_comment), 'backdated')
            );

        $message = $autoApproved
            ? "Your {$type} leave request #{$this->leaveRequest->id} was recorded as backdated leave and approved (already approved, manager told, or emergency)."
            : "Your {$type} leave request #{$this->leaveRequest->id} was submitted and is waiting for {$approver}.";

        return [
            'leave_request_id' => $this->leaveRequest->id,
            'message' => $message,
            'date' => now(),
        ];
    }
}
