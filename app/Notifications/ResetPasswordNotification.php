<?php

namespace App\Notifications;

use App\Support\SystemMailMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    public function __construct(public string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $expire = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        return SystemMailMessage::for($notifiable)
            ->subject('Reset your password')
            ->line('We received a request to reset your password.')
            ->line("This link expires in {$expire} minutes. If you did not request this, you can ignore this email.")
            ->action('Open in app', $url);
    }
}
