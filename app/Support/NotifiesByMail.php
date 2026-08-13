<?php

namespace App\Support;

trait NotifiesByMail
{
    protected function channelsFor(object $notifiable, bool $sendMail = true): array
    {
        $channels = ['database'];

        if ($sendMail && filled($notifiable->email ?? null)) {
            $channels[] = 'mail';
        }

        return $channels;
    }
}
