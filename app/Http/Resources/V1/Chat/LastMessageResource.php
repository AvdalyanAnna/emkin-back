<?php

namespace App\Http\Resources\V1\Chat;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class LastMessageResource
 * @property mixed body
 * @property mixed created_at
 * @property mixed is_sender
 * @property mixed is_seen
 * @package App\Http\Resources\V1\Chat
 */
class LastMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request): array|\JsonSerializable|Arrayable {
        return [
          'body' => $this->body,
          'is_seen' => $this->whenNotNull($this->is_seen),
          'is_sender' => $this->whenNotNull($this->is_sender),
          'created_at' => \Carbon\Carbon::parse($this->created_at)->format('h:i a'),
        ];
    }
}
