<?php

namespace App\Http\Resources\V1\Admin\Commercial;

use App\Http\Resources\V1\MediaResource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * Class CommercialUserResource
 * @package App\Http\Resources\V1\Admin\Category
 *
 * @property mixed id
 * @property mixed created_at
 * @property mixed updated_at
 * @property mixed title
 * @property mixed status
 * @property mixed order
 * @property mixed count_days
 */
class PeriodOfStayResource extends JsonResource
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
            'title' => $this->whenNotNull($this->title),
            'count_days' => $this->whenNotNull($this->count_days),
            'order' => $this->whenNotNull($this->order),
            'status' => $this->whenNotNull($this->status),
        ];
    }
}
