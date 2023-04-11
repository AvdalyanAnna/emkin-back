<?php

namespace App\Events\Chat;

use App\Contracts\MakeStaticInstance;
use App\Enums\Chat\ChatServiceNamesEnum;
use App\Models\Conversation;
use App\Traits\MakeStaticInstanceAble;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class MessageUnreadCounts
 * @package App\Events\Chat
 */
class MessageUnreadCounts implements ShouldBroadcastNow, MakeStaticInstance {

    use Dispatchable, InteractsWithSockets, SerializesModels, MakeStaticInstanceAble;

    /**
     * @var Model
     */
    private Model $participant;

    /**
     * ConversationUpdated constructor.
     * @param Conversation $conversation
     * @param int|null $recipient_id
     * @param Model|null $participant
     */
    public function __construct(Conversation $conversation, ?int $recipient_id = null, ?Model $participant = null) {
        if ($recipient_id) {
            $this->participant = \Chat::getInstance()::takeAnotherParticipant($conversation->getParticipants(), $recipient_id);
        } else {
            $this->participant = $participant;
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|PrivateChannel
     */
    public function broadcastOn(): Channel|PrivateChannel {
        \Log::info("chat-conversation-unread-messages-count.{$this->participant->id}");
        return new PrivateChannel("chat-conversation-unread-messages-count.{$this->participant->id}");
    }

    /**
     * @return string
     */
    public function broadcastAs(): string {
        return 'chat-conversation-unread-messages-count';
    }

    /**
     * @throws \App\Exceptions\Chat\IncorrectServiceInstanceException
     * @return array
     */
    #[ArrayShape(['unread_messages_count' => "int"])] public function broadcastWith(): array {
        return [
            'unread_messages_count' => \Chat::getInstance()
                ->serviceInstances(ChatServiceNamesEnum::MESSAGES_SERVICE)
                ->setParticipant($this->participant)
                ->unreadCount()
        ];
    }
}
