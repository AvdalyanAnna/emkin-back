<?php

namespace App\Http\Resources\V1\Admin\Commercial;

use App\Http\Resources\V1\MediaResource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * Class BusinessResource
 * @package App\Http\Resources\V1\Admin\Category
 *
 * @property mixed id
 * @property mixed created_at
 * @property mixed updated_at
 * @property mixed name
 * @property mixed status
 * @property mixed location
 * @property mixed link
 * @property mixed details
 * @property mixed type
 * @property mixed banner_images
 */
class BusinessResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request): array|JsonSerializable|Arrayable {
        if ($this->resource instanceof LengthAwarePaginator) {
            return parent::toArray($request);
        }

        return [
            'id' => $this->whenNotNull($this->id),
            'name' => $this->whenNotNull($this->name),
            'location' => $this->whenNotNull($this->location),
            'link' => $this->whenNotNull($this->link),
            'type' => $this->whenNotNull($this->type),
            'status' => $this->whenNotNull($this->status),
            'details' => $this->whenNotNull($this->details),
            'created_at' => $this->whenNotNull($this->created_at),
            'updated_at' => $this->whenNotNull($this->updated_at),
            'client' => ClientResource::make($this->whenLoaded('client')),
            'banner_images' => MediaResource::collection($this->whenLoaded('banner_images')),
        ];
    }
}
