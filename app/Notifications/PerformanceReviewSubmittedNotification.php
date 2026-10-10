<?php

namespace App\Notifications;

use App\Models\PerformanceReview;
use App\Support\EmailGreeting;
use App\Support\NotifiesByMail;
use App\Support\PerformanceReviews;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PerformanceReviewSubmittedNotification extends Notification
{
    use Queueable, NotifiesByMail;

    public function __construct(private PerformanceReview $review)
    {
        $this->review->loadMissing(['employee', 'employee.manager', 'reviewer']);
    }

    public function via(object $notifiable): array
    {
        return $this->channelsFor($notifiable);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $employee = $this->review->employee_name ?: 'an employee';
        $managerName = PerformanceReviews::displayName($this->review->employee?->manager);

        return (new MailMessage)
            ->subject('Monthly performance review — '.$employee)
            ->view('emails.performance_review_submitted', [
                'review' => $this->review,
                'reviewerName' => PerformanceReviews::displayName(PerformanceReviews::finalRater($this->review->employee))
                    ?: $this->review->reviewer_name,
                'reviewUrl' => PerformanceReviews::signedReviewUrl($this->review),
                'appUrl' => route(PerformanceReviews::routeName($notifiable instanceof \App\Models\User ? $notifiable : null, 'show'), $this->review),
                'recipientFirstName' => EmailGreeting::displayName($notifiable),
                'managerName' => $managerName,
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        $employee = $this->review->employee_name ?: 'an employee';

        return [
            'performance_review_id' => $this->review->id,
            'message' => "Monthly performance review for {$employee} is awaiting your review.",
            'date' => now(),
        ];
    }
}
