<?php

namespace App\Http\Resources\V1;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class StateResource
 * @package App\Http\Resources\V1
 *
 * @property mixed id
 * @property mixed refusal
 * @property mixed type
 */
class RefusalResource extends JsonResource
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
          'id' => $this->whenNotNull($this->id),
          'refusal' => $this->whenNotNull($this->refusal),
          'type' => $this->whenNotNull($this->type),
        ];
    }
}
