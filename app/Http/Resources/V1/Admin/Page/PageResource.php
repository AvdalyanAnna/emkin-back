<?php

namespace App\Http\Resources\V1\Admin\Page;

use App\Http\Resources\V1\Admin\Category\CategoryResource;
use App\Http\Resources\V1\MediaResource;
use App\Http\Resources\V1\User\UserResource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * Class PageResource
 * @package App\Http\Resources\V1\Admin\Category
 *
 * @property mixed id
 * @property mixed created_at
 * @property mixed updated_at
 * @property mixed name
 * @property mixed type
 * @property mixed status
 * @property mixed content
 * @property mixed locale
 * @property mixed page_key
 */
class PageResource extends JsonResource
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
            'locale' => $this->whenNotNull($this->locale),
            'type' => $this->whenNotNull($this->type),
            'content' => $this->whenNotNull($this->content),
            'status' => $this->whenNotNull($this->status),
            'page_key' => $this->whenNotNull($this->page_key),
            'created_at' => $this->whenNotNull($this->created_at),
            'updated_at' => $this->whenNotNull($this->updated_at),
            'background_images' => MediaResource::collection($this->whenLoaded('backgrounds')->keyBy('image_key')),
        ];
    }
}
