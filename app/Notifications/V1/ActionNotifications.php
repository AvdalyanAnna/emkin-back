<?php

namespace App\Notifications\V1;

use App\Contracts\MakeStaticInstance;
use App\Traits\MakeStaticInstanceAble;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

/**
 * Class ActionNotifications
 * @package App\Notifications
 */
class ActionNotifications extends Notification implements ShouldQueue, MakeStaticInstance
{
   use Queueable, MakeStaticInstanceAble;

   /**
    * ActionNotifications constructor.
    * @param array $data
    */
   public function __construct(private array $data) { }

   /**
    * Get the notification's delivery channels.
    *
    * @param mixed $notifiable
    * @return array
    */
   public function via($notifiable): array
   {
      return ['database'];
   }

   /**
    * Get the array representation of the notification.
    *
    * @param mixed $notifiable
    * @return array
    */
   public function toDatabase($notifiable): array
   {
      return $this->data;
   }

   /**
    * Получить содержимое транслируемого уведомления.
    *
    * @param mixed $notifiable
    * @return BroadcastMessage
    */
   public function toBroadcast($notifiable): BroadcastMessage
   {
      return new BroadcastMessage($notifiable->action_notifications);
   }
}
