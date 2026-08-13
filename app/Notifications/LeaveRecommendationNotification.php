<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use App\Support\LeavePortalLinks;
use App\Support\NotifiesByMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRecommendationNotification extends Notification
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
        $label = $this->recommendationLabel();

        return (new MailMessage)
            ->subject('Your leave request was recommended — #'.$this->leaveRequest->id)
            ->greeting('Hello '.trim((string) ($notifiable->first_name ?? '')).',')
            ->line("Your manager has recorded: {$label} for leave request #{$this->leaveRequest->id}.")
            ->line('A senior manager will make the final decision. You will be notified when that happens.')
            ->action('View my requests', LeavePortalLinks::myRequestsUrl($notifiable, $this->leaveRequest->id));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'leave_request_id' => $this->leaveRequest->id,
            'message' => 'Your manager recorded '.$this->recommendationLabel().' for leave request #'.$this->leaveRequest->id.'. A senior manager will decide next.',
            'date' => now(),
        ];
    }

    private function recommendationLabel(): string
    {
        return strtolower((string) ($this->leaveRequest->manager_recommendation ?? '')) === 'reject'
            ? 'Recommend Reject'
            : 'Recommend Approve';
    }
}
