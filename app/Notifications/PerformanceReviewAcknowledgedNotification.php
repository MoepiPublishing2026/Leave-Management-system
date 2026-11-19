<?php

namespace App\Notifications;

use App\Models\PerformanceReview;
use App\Support\NotifiesByMail;
use App\Support\PerformanceReviews;
use App\Support\SystemMailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PerformanceReviewAcknowledgedNotification extends Notification
{
    use Queueable, NotifiesByMail;

    public function __construct(private PerformanceReview $review)
    {
    }

    public function via(object $notifiable): array
    {
        return $this->channelsFor($notifiable);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $employee = $this->review->liveEmployeeName() ?: 'the employee';

        $mail = SystemMailMessage::for($notifiable)
            ->subject('Performance review marked as reviewed — '.$employee)
            ->line("The monthly performance review for {$employee} has been marked as reviewed.");

        if (filled($this->review->senior_comment)) {
            $mail->line('Comment: '.$this->review->senior_comment);
        }

        return $mail->action('Open in app', route(PerformanceReviews::routeName($notifiable, 'show'), $this->review));
    }

    public function toDatabase(object $notifiable): array
    {
        $employee = $this->review->liveEmployeeName() ?: 'the employee';

        return [
            'performance_review_id' => $this->review->id,
            'message' => "The monthly performance review for {$employee} was marked as reviewed.",
            'date' => now(),
        ];
    }
}
