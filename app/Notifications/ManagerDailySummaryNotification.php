<?php

namespace App\Notifications;

use App\Support\LeavePortalLinks;
use App\Support\NotifiesByMail;
use App\Support\SystemMailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class ManagerDailySummaryNotification extends Notification
{
    use Queueable, NotifiesByMail;

    /**
     * @param  Collection<int, object{id:int|string, employee:string, leave_type:string, start_date:string, end_date:string, hours_pending:int}>  $pendingItems
     */
    public function __construct(
        public Collection $pendingItems,
        public int $overdueCount = 0
    ) {
    }

    public function via(object $notifiable): array
    {
        // Email-focused optional summary; also store in-app for the bell.
        return $this->channelsFor($notifiable);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $count = $this->pendingItems->count();
        $mail = SystemMailMessage::for($notifiable)
            ->subject("Daily leave summary — {$count} request(s) awaiting review")
            ->line("You have {$count} leave request(s) still awaiting your review.");

        if ($this->overdueCount > 0) {
            $mail->line("{$this->overdueCount} of these are overdue (leave start date has passed).");
        }

        foreach ($this->pendingItems->take(10) as $item) {
            $mail->line(
                "#{$item->id} — {$item->employee} — {$item->leave_type} ({$item->start_date} to {$item->end_date}, {$item->hours_pending}h pending)"
            );
        }

        if ($count > 10) {
            $mail->line('…and '.($count - 10).' more.');
        }

        return $mail->action('Open in app', LeavePortalLinks::teamRequestsUrl());
    }

    public function toDatabase(object $notifiable): array
    {
        $count = $this->pendingItems->count();

        return [
            'pending_count' => $count,
            'overdue_count' => $this->overdueCount,
            'message' => "Daily summary: {$count} leave request(s) awaiting your review"
                .($this->overdueCount > 0 ? " ({$this->overdueCount} overdue)." : '.'),
            'date' => now(),
        ];
    }
}
