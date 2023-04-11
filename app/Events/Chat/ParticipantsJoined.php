<?php

namespace App\Events\Chat;

use App\Http\Resources\V1\Chat\ConversationsResource;
use App\Models\Conversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class ParticipantsJoined
 * @package App\Events\Chat
 */
class ParticipantsJoined implements ShouldBroadcastNow {

    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * ParticipantsJoined constructor.
     * @param Conversation $conversation
     * @param int $user_id
     */
    public function __construct(
        public Conversation $conversation,
        public int $user_id,
    ) { }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|PrivateChannel
     */
    public function broadcastOn(): Channel|PrivateChannel {
        \Log::info("chat-participant-joined.$this->user_id");
        return new PrivateChannel("chat-participant-joined.$this->user_id");
    }

    /**
     * @return string
     */
    public function broadcastAs(): string {
        return 'private-chat-participant-joined';
    }

    /**
     * @return array
     */
    #[ArrayShape(['conversation' => "\App\Http\Resources\V1\Chat\ConversationsResource"])]
    public function broadcastWith(): array {
        return [
            'conversation' => new ConversationsResource(
                $this->conversation->load(Conversation::$selectedRelationsConversation)
            )
        ];
    }
}
