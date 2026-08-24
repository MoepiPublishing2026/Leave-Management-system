<?php

namespace App\Notifications;

use App\Support\NotifiesByMail;
use App\Support\SystemMailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MessageReceivedNotification extends Notification
{
    use Queueable, NotifiesByMail;

    private int $conversationId;
    private int $leaveRequestId;
    private string $senderName;

    public function __construct(int $conversationId, int $leaveRequestId, string $senderName)
    {
        $this->conversationId = $conversationId;
        $this->leaveRequestId = $leaveRequestId;
        $this->senderName = $senderName;
    }

    public function via(object $notifiable): array
    {
        return $this->channelsFor($notifiable);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return SystemMailMessage::for($notifiable)
            ->subject('New message from '.$this->senderName)
            ->line('You have a new message from '.$this->senderName.' about leave request #'.$this->leaveRequestId.'.')
            ->action('Open in app', route('messages.index', ['conversation' => $this->conversationId]));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'conversation_id' => $this->conversationId,
            'leave_request_id' => $this->leaveRequestId,
            'message' => 'New message from ' . $this->senderName,
        ];
    }
}

