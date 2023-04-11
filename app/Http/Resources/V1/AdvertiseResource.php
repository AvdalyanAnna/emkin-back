<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\V1\Admin\Category\AnswerResource;
use App\Http\Resources\V1\Admin\Category\CategoryResource;
use App\Http\Resources\V1\User\UserResource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * Class AdvertiseResource
 * @package App\Http\Resources\V1\Admin\Category
 *
 * @property mixed id
 * @property mixed created_at
 * @property mixed updated_at
 * @property mixed name
 * @property object category
 * @property array answers
 * @property mixed slug
 * @property mixed description
 * @property mixed price
 * @property mixed link
 * @property mixed latitude
 * @property mixed longitude
 * @property mixed contacts
 * @property mixed price_policy
 * @property mixed refusal_comment
 * @property mixed show_phone
 * @property mixed contact_phone
 * @property mixed show_details
 * @property mixed added_favorites
 * @property mixed auto_renewal
 * @property mixed type
 * @property mixed status
 * @property mixed available_cost
 * @property mixed renewal
 * @property mixed address
 * @property mixed inactively_date
 * @property mixed contact_phone_numeric
 */
class AdvertiseResource extends JsonResource
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
            'description' => $this->whenNotNull($this->description),
            'slug' => $this->whenNotNull($this->slug),
            'price' => $this->whenNotNull($this->price),
            'type' => $this->whenNotNull($this->type),
            'status' => $this->whenNotNull($this->status),
            'link' => $this->whenNotNull($this->link),
            'latitude' => $this->whenNotNull($this->latitude),
            'longitude' => $this->whenNotNull($this->longitude),
            'contacts' => $this->whenNotNull($this->contacts),
            'contact_phone' => $this->whenNotNull($this->contact_phone),
            'contact_phone_numeric' => $this->whenNotNull($this->contact_phone_numeric),
            'inactively_date' => $this->whenNotNull($this->inactively_date),
            'address' => $this->whenNotNull($this->address),
            'auto_renewal' => $this->whenNotNull($this->auto_renewal),
            'price_policy' => $this->whenNotNull($this->price_policy),
            'available_cost' => $this->whenNotNull($this->available_cost),
            'refusal_comment' => $this->whenNotNull($this->refusal_comment),
            'show_phone' => $this->whenNotNull($this->show_phone),
            'show_details' => $this->whenNotNull($this->show_details),
            'added_favorites' => $this->whenNotNull($this->added_favorites),
            'created_at' => $this->whenNotNull($this->created_at),
            'updated_at' => $this->whenNotNull($this->updated_at),
            'author' => UserResource::make($this->whenLoaded('author')),
            'city' => CityResource::make($this->whenLoaded('city')),
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'gallery' => MediaResource::collection($this->whenLoaded('gallery')),
            'answers' => AnswerResource::collection($this->whenLoaded('answers'))
        ];
    }
}
