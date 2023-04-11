<?php

namespace App\Models;

use App\Traits\TimezoneChangeAble;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Chat\ConfigurationManager;

/**
 * Class MessageNotification
 *
 * @package App\Models
 * @property int $id
 * @property int $message_id
 * @property int $messageable_id
 * @property string $messageable_type
 * @property int $conversation_id
 * @property int $participation_id
 * @property int $is_seen
 * @property int $is_sender
 * @property int $flagged
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|MessageNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MessageNotification newQuery()
 * @method static \Illuminate\Database\Query\Builder|MessageNotification onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|MessageNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder|MessageNotification whereConversationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageNotification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageNotification whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageNotification whereFlagged($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageNotification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageNotification whereIsSeen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageNotification whereIsSender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageNotification whereMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageNotification whereMessageableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageNotification whereMessageableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageNotification whereParticipationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MessageNotification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|MessageNotification withTrashed()
 * @method static \Illuminate\Database\Query\Builder|MessageNotification withoutTrashed()
 * @mixin \Eloquent
 */
class MessageNotification extends BaseModel
{
    use SoftDeletes, TimezoneChangeAble;

    /**
     * @var string
     */
    protected $table = ConfigurationManager::MESSAGE_NOTIFICATIONS_TABLE;

    /**
     * @var string[]
     */
    protected $fillable = ['messageable_id', 'messageable_type', 'message_id', 'conversation_id'];

    /**
     * Creates a new notification.
     *
     * @param Message      $message
     * @param Conversation $conversation
     */
    public static function make(Message $message, Conversation $conversation)
    {
        self::createCustomNotifications($message, $conversation);
    }

    /**
     * @param Model $participant
     * @return mixed
     */
    public function unReadNotifications(Model $participant): mixed {
        return self::where([
            ['messageable_id', '=', $participant->getKey()],
            ['messageable_type', '=', $participant->getMorphClass()],
            ['is_seen', '=', 0],
        ])->get();
    }

    /**
     * @param $message
     * @param $conversation
     */
    public static function createCustomNotifications($message, $conversation)
    {
        $notification = [];
        $i = 0;

        foreach ($conversation->participants()->get() as $participation) {
            $is_sender = ($message->participation_id == $participation->id) ? 1 : 0;

            $notification[] = [
              'messageable_id'   => $participation->messageable_id,
              'messageable_type' => $participation->messageable_type,
              'message_id'       => $message->id,
              'participation_id' => $participation->id,
              'conversation_id'  => $conversation->id,
              'is_seen'          => $is_sender,
              'is_sender'        => $is_sender,
              'created_at'       => $message->created_at,
            ];

            $i++;
            if ($i > 1000) {
                self::insert($notification);
                $i = 0;
                $notification = [];
            }
        }

        self::insert($notification);
    }

    /**
     * @return void
     */
    public function markAsRead(): void
    {
        $this->update(['is_seen' => 1]);
    }
}
