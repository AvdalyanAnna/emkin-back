<?php

namespace App\Http\Resources\V1\Admin\Commercial;

use App\Http\Resources\V1\MediaResource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * Class ClientResource
 * @package App\Http\Resources\V1\User
 *
 * @property mixed id
 * @property mixed first_name
 * @property mixed last_name
 * @property mixed full_name
 * @property mixed email
 * @property mixed created_at
 * @property mixed updated_at
 * @property mixed roles
 * @property mixed phone
 * @property object avatar
 * @property mixed businesses_count
 * @property mixed canceled_publications_count
 * @property mixed publications_count
 * @property mixed company
 * @property mixed phone_view
 */
class ClientResource extends JsonResource
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
            'first_name' => $this->whenNotNull($this->first_name),
            'last_name' => $this->whenNotNull($this->last_name),
            'full_name' => $this->whenNotNull($this->full_name),
            'phone' => $this->whenNotNull($this->phone),
            'phone_view' => $this->whenNotNull($this->phone_view),
            'email' => $this->whenNotNull($this->email),
            'company' => $this->whenNotNull($this->company),
            'businesses_count' => $this->whenNotNull($this->businesses_count),
            'canceled_publications_count' => $this->whenNotNull($this->canceled_publications_count),
            'publications_count' => $this->whenNotNull($this->publications_count),
            'created_at' => $this->whenNotNull($this->created_at),
            'updated_at' => $this->whenNotNull($this->updated_at),
            'avatar' => MediaResource::make($this->whenLoaded('avatar')),
        ];
    }
}
