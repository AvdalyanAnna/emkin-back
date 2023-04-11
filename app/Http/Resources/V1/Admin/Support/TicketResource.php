<?php

namespace App\Http\Resources\V1\Admin\Support;

use App\Http\Resources\V1\Chat\ConversationsResource;
use App\Http\Resources\V1\MediaResource;
use App\Http\Resources\V1\User\UserResource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class UserResource
 * @package App\Http\Resources\V1\User
 *
 * @property mixed id
 * @property mixed name
 * @property mixed email
 * @property mixed status
 * @property mixed created_at
 * @property mixed updated_at
 * @property object files
 * @property mixed description
 * @property mixed user
 * @property mixed moderator
 * @property mixed conversation
 */
class TicketResource extends JsonResource {

    /**
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request): array|\JsonSerializable|\Illuminate\Contracts\Support\Arrayable {

        if ($this->resource instanceof LengthAwarePaginator) {
            return parent::toArray($request);
        }

        return [
            'id' => $this->whenNotNull($this->id),
            'name' => $this->whenNotNull($this->name),
            'email' => $this->whenNotNull($this->email),
            'description' => $this->whenNotNull($this->description),
            'created_at' => $this->whenNotNull($this->created_at),
            'created_time_ago' => $this->whenNotNull($this->humans_time),
            'updated_at' => $this->whenNotNull($this->updated_at),
            'status' => $this->whenNotNull($this->status),
            'files' => MediaResource::collection($this->files),
            'user' => UserResource::make($this->user),
            'moderator' => UserResource::make($this->moderator),
            'conversation' => ConversationsResource::make($this->conversation),
        ];
    }
}
