<?php

namespace App\Http\Resources\V1\Chat;

use App\Http\Resources\V1\MediaResource;
use App\Http\Resources\V1\RefusalResource;
use App\Http\Resources\V1\User\UserResource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class UserResource
 * @package App\Http\Resources\V1\User
 *
 * @property mixed id
 * @property mixed email
 * @property mixed created_at
 * @property mixed updated_at
 * @property mixed description
 * @property mixed files
 * @property mixed refusal
 * @property mixed user
 */
class ComplaintResource extends JsonResource {

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
            'description' => $this->whenNotNull($this->description),
            'created_at' => $this->whenNotNull($this->created_at),
            'updated_at' => $this->whenNotNull($this->updated_at),
            'user' => UserResource::make($this->whenLoaded('user')),
            'refusal' => RefusalResource::make($this->whenLoaded('refusal')),
            'files' => MediaResource::collection($this->whenLoaded('files')),
        ];
    }
}
