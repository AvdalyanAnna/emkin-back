<?php

namespace App\Notifications\V1;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Class EmailConfirmation
 * @package App\Notifications
 */
class EmailConfirmation extends Notification /*implements ShouldQueue*/
{
//    use Queueable;

    /**
     * @var int $code
     */
    private int $code;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->code = mt_rand(100000, 999999);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(mixed $notifiable): array {
        return ['mail', 'database'];
    }

    /**
     * @param $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage {
        return (new MailMessage)
            ->greeting('Привет!')
            ->line("Ваш код Подтверждения $this->code");
    }

    /**
     * @param $notifiable
     * @return array
     */
    public function toArray($notifiable): array {
        return ['code' => $this->code];
    }
}
