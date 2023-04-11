<?php

namespace App\Http\Resources\V1\Chat;

use App\Http\Resources\V1\MediaResource;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class MessageAbleResource
 * @property mixed first_name
 * @property mixed last_name
 * @package App\Http\Resources\V1\Chat
 */
class MessageAbleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request): array|\JsonSerializable|Arrayable {
        return [
           'full_name' => $this->first_name . ' ' . $this->last_name,
           'first_name' => $this->first_name,
           'blocked_list' => $this->blockedChatFromAnotherUser(),
           'avatar' => MediaResource::make($this->whenLoaded('avatar')),
        ];
    }
}
