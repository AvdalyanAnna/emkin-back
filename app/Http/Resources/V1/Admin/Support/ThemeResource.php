<?php

namespace App\Http\Resources\V1\Admin\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class UserResource
 * @package App\Http\Resources\V1\User
 *
 * @property mixed id
 * @property mixed title
 * @property mixed status
 * @property mixed created_at
 * @property mixed updated_at
 * @property mixed order
 * @property mixed tickets_count
 */
class ThemeResource extends JsonResource {

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
            'title' => $this->whenNotNull($this->title),
            'order' => $this->whenNotNull($this->order),
            'tickets_count' => $this->whenNotNull($this->tickets_count),
            'status' => $this->whenNotNull($this->status),
            'created_at' => $this->whenNotNull($this->created_at),
            'updated_at' => $this->whenNotNull($this->updated_at),
        ];
    }
}
