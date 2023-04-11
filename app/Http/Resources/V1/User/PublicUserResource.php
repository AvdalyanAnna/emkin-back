<?php

namespace App\Http\Resources\V1\User;

use App\Http\Resources\V1\AdvertiseResource;
use App\Http\Resources\V1\MediaResource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class UserResource
 * @package App\Http\Resources\V1\User
 *
 * @property mixed id
 * @property mixed first_name
 * @property mixed last_name
 * @property mixed full_name
 * @property mixed email
 * @property mixed phone
 * @property object avatar
 * @property string phone_view
 * @property mixed advertises_count
 * @property mixed canceled_advertises_count
 */
class PublicUserResource extends JsonResource {

    /**
     * @var bool
     */
    public static $wrap = false;

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
            'first_name' => $this->whenNotNull($this->first_name),
            'last_name' => $this->whenNotNull($this->last_name),
            'full_name' => $this->whenNotNull($this->full_name),
            'phone' => $this->whenNotNull($this->phone),
            'email' => $this->whenNotNull($this->email),
            'phone_view' => $this->whenNotNull($this->phone_view),
            'advertises_count' => $this->whenNotNull($this->advertises_count),
            'canceled_advertises_count' => $this->whenNotNull($this->canceled_advertises_count),
            'advertises' => AdvertiseResource::collection($this->whenLoaded('advertises')),
            'avatar' => $this->whenLoaded('avatar', function () {
                return MediaResource::make($this->avatar);
            }),
        ];
    }
}
