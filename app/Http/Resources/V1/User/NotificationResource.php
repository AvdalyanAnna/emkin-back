<?php

namespace App\Http\Resources\V1\User;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class NotificationResource
 * @package App\Http\Resources\V1\User
 *
 * @property mixed id
 * @property mixed created_at
 * @property object avatar
 * @property mixed type
 * @property mixed notifiable_id
 * @property mixed data
 * @property mixed read_at
 * @property mixed notifiable_type
 */
class NotificationResource extends JsonResource {

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
            'type' => $this->whenNotNull($this->type),
            'notifiable_id' => $this->whenNotNull($this->notifiable_id),
            'notifiable_type' => $this->whenNotNull($this->notifiable_type),
            'read_at' => $this->whenNotNull($this->read_at),
            'created_at' => $this->whenNotNull($this->created_at),
            'data' => $this->whenNotNull($this->data),
        ];
    }
}
