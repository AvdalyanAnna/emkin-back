<?php

namespace App\Events\Chat;

use App\Contracts\MakeStaticInstance;
use App\Enums\Chat\ChatServiceNamesEnum;
use App\Http\Resources\V1\Chat\ConversationsResource;
use App\Models\Conversation;
use App\Traits\MakeStaticInstanceAble;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class ConversationMessageReadAll
 * @package App\Events\Chat
 */
class ConversationMessageReadAll implements ShouldBroadcastNow, MakeStaticInstance {

    use Dispatchable, InteractsWithSockets, SerializesModels, MakeStaticInstanceAble;

    /**
     * @var Conversation
     */
    public Conversation $conversation;
    public Conversation $participants;

    /**
     * @var int
     */
    public int $recipient_id;

    /**
     * ConversationUpdated constructor.
     * @param Conversation $conversation
     * @param int $recipient_id
     */
    public function __construct(Conversation $conversation, int $recipient_id) {
        $this->recipient_id = \Chat::getInstance()::takeAnotherParticipant($conversation->getParticipants(), $recipient_id)->id;
        $this->conversation = $conversation;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|PrivateChannel
     */
    public function broadcastOn(): Channel|PrivateChannel {
        \Log::info("chat-conversation-readAt-all.{$this->conversation->id}-$this->recipient_id");
        return new PrivateChannel("chat-conversation-readAt-all.{$this->conversation->id}-$this->recipient_id");
    }

    /**
     * @return string
     */
    public function broadcastAs(): string {
        return 'chat-conversation-readAt-all';
    }

    public function broadcastWith(): array {
        return [
            'conversation' => new ConversationsResource(
                $this->conversation
                    ->load(['last_message' => function ($query) {
                        $query->join('chat_message_notifications', 'chat_message_notifications.message_id', '=', 'chat_messages.id')
                            ->select('chat_message_notifications.*', 'chat_messages.*')
                            ->where('chat_message_notifications.messageable_id', '<>', $this->recipient_id)
                            ->whereNull('chat_message_notifications.deleted_at');
                    }])
                    ->loadCount(['unread_messages' => function($q) {
                        $q->join('chat_participation as part', function ($q) {
                            $q->on('part.id', '=', 'chat_message_notifications.participation_id')
                                ->where('part.messageable_id', '=', $this->recipient_id);
                        });
                    }])
                    ->load(Conversation::$selectedRelationsConversation)
            ),
        ];
    }
}
