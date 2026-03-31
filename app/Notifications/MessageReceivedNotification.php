<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MessageReceivedNotification extends Notification
{
    use Queueable;

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
        return ['database'];
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

