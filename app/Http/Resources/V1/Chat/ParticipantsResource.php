<?php

namespace App\Http\Resources\V1\Chat;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ParticipantsResource
 * @property mixed messageable_id
 * @package App\Http\Resources\V1\Chat
 */
class ParticipantsResource extends JsonResource
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
           'messageable_id' => $this->messageable_id,
           'messageable' => MessageAbleResource::make($this->whenLoaded('messageable'))
        ];
    }
}
