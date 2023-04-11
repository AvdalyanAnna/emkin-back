<?php

namespace App\Http\Resources\V1\Chat;

use App\Http\Resources\V1\AdvertiseResource;
use App\Http\Resources\V1\Admin\Support\TicketResource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class ConversationsResource
 * @property mixed updated_at
 * @property mixed advertise_id
 * @property mixed ticket_id
 * @property mixed id
 * @property int unread_messages_count
 * @property mixed status
 * @package App\Http\Resources\V1
 */
class ConversationsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request): array|\JsonSerializable|Arrayable {

        if ($this->resource instanceof LengthAwarePaginator) {
            return parent::toArray($request);
        }

        return [
            'conversation_id' => $this->whenNotNull($this->id),
            'ticket_id' => $this->whenNotNull($this->ticket_id),
            'advertise_id' => $this->whenNotNull($this->advertise_id),
            'status' => $this->whenNotNull($this->status),
            'updated_at' => $this->whenNotNull($this->updated_at),
            'unread_messages_count' => $this->whenNotNull($this->unread_messages_count),
            'ticket' => TicketResource::make($this->whenLoaded('ticket')),
            'advertise' => AdvertiseResource::make($this->whenLoaded('advertise')),
            'participants' => ParticipantsResource::collection($this->whenLoaded('participants')),
            'last_message' => LastMessageResource::make($this->whenLoaded('last_message'))
        ];
    }

    /**
     * @param Request $request
     * @return array[]
     */
    #[ArrayShape(['meta' => "array"])] public function with($request): array
    {
        return [
            'meta' => [
                'type' => $request->get('conversation_type', [])
            ],
        ];
    }
}
