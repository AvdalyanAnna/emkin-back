<?php

namespace App\Events\Chat;

use App\Contracts\MakeStaticInstance;
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
 * Class ConversationUpdated
 * @package App\Events\Chat
 */
class ConversationUpdated implements ShouldBroadcastNow, MakeStaticInstance {

    use Dispatchable, InteractsWithSockets, SerializesModels, MakeStaticInstanceAble;

    /**
     * @var Conversation
     */
    public Conversation $conversation;

    /**
     * @var int
     */
    public int $recipient_id;

    public int $sender_id;

    /**
     * ConversationUpdated constructor.
     * @param Conversation $conversation
     * @param int|null $recipient_id
     */
    public function __construct(Conversation $conversation, int $recipient_id = null) {
        if($recipient_id) {
            $this->recipient_id = $recipient_id;
        } else {
//            $id = $conversation?->last_message?->participation?->messageable->id ?? user()->id;
            $this->recipient_id = \Chat::getInstance()::takeAnotherParticipant($conversation->getParticipants(), user()->id)->id;
        }

        $this->conversation = $conversation;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|PrivateChannel
     */
    public function broadcastOn(): Channel|PrivateChannel {
        \Log::info("chat-conversation-updated.$this->recipient_id");
        return new PrivateChannel("chat-conversation-updated.$this->recipient_id");
    }

    /**
     * @return string
     */
    public function broadcastAs(): string {
        return 'private-chat-conversation-updated';
    }

    /**
     * @return array
     */
    #[ArrayShape([
        'conversation' => "\App\Http\Resources\V1\Chat\ConversationsResource",
        'conversation_type' => "mixed"
    ])] public function broadcastWith(): array {
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
            'conversation_type' => request('conversation_type','')
        ];
    }
}
