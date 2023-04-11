<?php

namespace App\Events\Chat;

use App\Contracts\MakeStaticInstance;
use App\Http\Resources\V1\MediaResource;
use App\Models\Message;
use App\Traits\MakeStaticInstanceAble;
use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class MessageWasSent
 * @package App\Events
 */
class MessageWasSent implements ShouldBroadcastNow, MakeStaticInstance {

    use Dispatchable, InteractsWithSockets, SerializesModels, MakeStaticInstanceAble;

    /**
     * MessageWasSent constructor.
     * @param Message $message
     */
    public function __construct(public Message $message) { }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|PrivateChannel
     */
    public function broadcastOn(): Channel|PrivateChannel {
        return new PrivateChannel("send-message.{$this->message->conversation_id}");
    }

    /**
     * @return string
     */
    public function broadcastAs(): string {
        return 'private-send-message';
    }

    /**
     * @return array[]
     */
    #[ArrayShape(['message' => "array"])] public function broadcastWith(): array {
        $selects = Message::$selectedRelationsConversation;
        unset($selects[0]);
        $message = $this->message->load(Message::$selectedRelationsConversation);

        return [
            'message' => [
                'id' => $message->getKey(),
                'body' => $message->body,
                'conversation_id' => $message->conversation_id,
                'type' => $message->type,
                'send_time' => Carbon::parse($message->created_at)->format('h:i a'),
                'participation' => $message->participation,
                'date' => Carbon::parse($message->created_at)->format('Y F d'),
                'files' => MediaResource::collection($message->files),
                'read_at' => null,
            ],
        ];
    }
}
