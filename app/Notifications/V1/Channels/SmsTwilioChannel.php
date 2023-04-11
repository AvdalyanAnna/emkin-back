<?php

namespace App\Notifications\V1\Channels;

use Illuminate\Notifications\Notification;

/**
 * Class SmsTwilioChannel
 * @package App\Notifications\V1\Channels
 */
class SmsTwilioChannel
{
    /**
     * @param $notifiable
     * @param Notification $notification
     */
    public function send($notifiable, Notification $notification): void
    {
        $notification->{'toSmsTwilio'}($notifiable);
    }
}
