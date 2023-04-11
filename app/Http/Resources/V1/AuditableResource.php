<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\V1\User\UserResource;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class StateResource
 * @property mixed id
 * @property mixed user_id
 * @package App\Http\Resources\V1
 */
class AuditableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request): array|\JsonSerializable|Arrayable {
        return [
            'id' => $this->whenNotNull($this->id),
            'user_id' => $this->whenNotNull($this->user_id),
            'user' => UserResource::make($this->whenLoaded('user'))
        ];
    }
}
