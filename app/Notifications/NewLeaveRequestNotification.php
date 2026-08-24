<?php

namespace App\Notifications;

use App\Mail\SeniorLeaveDecisionMail;
use App\Models\LeaveRequest;
use App\Support\EmailGreeting;
use App\Support\LeaveApprovalWorkflow;
use App\Support\LeavePortalLinks;
use App\Support\LeaveStatusLabels;
use App\Support\NotifiesByMail;
use App\Support\SystemMailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLeaveRequestNotification extends Notification
{
    use Queueable, NotifiesByMail;

    public function __construct(public LeaveRequest $leaveRequest)
    {
        $this->leaveRequest->loadMissing(['user.manager']);
    }

    public function via(object $notifiable): array
    {
        return $this->channelsFor($notifiable);
    }

    public function toMail(object $notifiable): MailMessage|SeniorLeaveDecisionMail
    {
        $context = LeaveApprovalWorkflow::reportingContext($this->leaveRequest);
        $name = $context['applicant_name'];
        $managerName = $context['manager_name'];
        $type = $this->leaveRequest->leave_type ?: 'leave';
        $start = $this->leaveRequest->start_date;
        $end = $this->leaveRequest->end_date;
        $isSeniorQueue = strtolower(trim((string) ($this->leaveRequest->status ?? ''))) === 'pending_senior';

        if (strtolower((string) ($notifiable->role ?? '')) === 'admin') {
            if ($isSeniorQueue) {
                return (new SeniorLeaveDecisionMail(
                    $this->leaveRequest,
                    EmailGreeting::displayName($notifiable),
                    $notifiable instanceof \App\Models\User ? $notifiable : null
                ))->to($notifiable->email);
            }

            $mail = SystemMailMessage::for($notifiable)
                ->subject('Team leave submitted — #'.$this->leaveRequest->id.' — '.$name)
                ->line("{$name} submitted a {$type} leave request (#{$this->leaveRequest->id}).");

            if ($managerName !== '') {
                $mail->line("Their assigned manager is {$managerName}.");
            }

            return $mail
                ->line("Dates: {$start} to {$end}.")
                ->line('This is for your information. The assigned manager will review it first.')
                ->action('Open in app', LeavePortalLinks::seniorInAppUrl($this->leaveRequest->id));
        }

        return SystemMailMessage::for($notifiable)
            ->subject('New leave request — #'.$this->leaveRequest->id)
            ->line("{$name} submitted a {$type} leave request (#{$this->leaveRequest->id}).")
            ->line("Dates: {$start} to {$end}.")
            ->line('Please review it as soon as you can.')
            ->action('Open in app', LeavePortalLinks::teamRequestsUrl($this->leaveRequest->id));
    }

    public function toDatabase(object $notifiable): array
    {
        $context = LeaveApprovalWorkflow::reportingContext($this->leaveRequest);
        $name = $context['applicant_name'];
        $managerName = $context['manager_name'];
        $status = LeaveStatusLabels::display($this->leaveRequest->status);
        $isSeniorQueue = strtolower(trim((string) ($this->leaveRequest->status ?? ''))) === 'pending_senior';
        $isAdmin = strtolower((string) ($notifiable->role ?? '')) === 'admin';

        if ($isSeniorQueue) {
            $message = "Leave request #{$this->leaveRequest->id} from {$name} is awaiting your approval ({$status}).";
            if ($managerName !== '' && ! $context['is_manager_applicant']) {
                $message .= " Manager: {$managerName}.";
            }
        } elseif ($isAdmin && $managerName !== '') {
            $message = "{$name} submitted a leave request (manager: {$managerName}).";
        } else {
            $message = "New leave request from {$name}";
        }

        return [
            'message' => $message,
            'leave_request_id' => $this->leaveRequest->id,
            'date' => now(),
        ];
    }
}
