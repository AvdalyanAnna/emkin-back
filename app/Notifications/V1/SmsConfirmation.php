<?php

namespace App\Notifications\V1;

use App\Notifications\V1\Channels\SmsTwilioChannel;
use Illuminate\Notifications\Notification;

/**
 * Class SmsConfirmation
 * @package App\Notifications\V1
 */
class SmsConfirmation extends Notification {

    /**
     * @var
     */
    private $code;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct() {
        $this->code = mt_rand(100000, 999999);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable) {
        return [SmsTwilioChannel::class, 'database'];
    }

    /**
     * @param $notifiable
     */
    public function toSmsTwilio($notifiable): void {
        \Twilio::message($notifiable->phone, $this->code);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable) {
        return [
            'code' => $this->code
        ];
    }
}
