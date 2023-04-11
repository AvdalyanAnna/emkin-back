<?php

namespace App\Http\Resources\V1\Admin\Commercial;

use App\Http\Resources\V1\Admin\Category\CategoryResource;
use App\Http\Resources\V1\MediaResource;
use App\Http\Resources\V1\User\UserResource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * Class NotificationResource
 * @package App\Http\Resources\V1\Admin\Category
 *
 * @property mixed id
 * @property mixed created_at
 * @property mixed updated_at
 * @property mixed type
 * @property mixed link
 * @property mixed details
 * @property mixed text
 * @property mixed title
 * @property mixed description
 * @property mixed status
 */
class NotificationResource extends JsonResource
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
            'text' => $this->whenNotNull($this->text),
            'title' => $this->whenNotNull($this->title),
            'description' => $this->whenNotNull($this->description),
            'link' => $this->whenNotNull($this->link),
            'status' => $this->whenNotNull($this->status),
            'details' => $this->whenNotNull($this->details),
            'created_at' => $this->whenNotNull($this->created_at),
            'updated_at' => $this->whenNotNull($this->updated_at),
            'banner_image' => MediaResource::make($this->whenLoaded('banner_image')),
        ];
    }
}
