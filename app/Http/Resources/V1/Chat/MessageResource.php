<?php

namespace App\Http\Resources\V1\Chat;

use App\Http\Resources\V1\MediaResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class MessageResource
 * @property mixed id
 * @property mixed body
 * @property mixed read_at
 * @property mixed created_at
 * @property mixed participation
 * @property mixed send_time
 * @package App\Http\Resources\V1\Chat
 */
class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function toArray($request): array {
        if ($this->resource instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            return parent::toArray($request);
        }

        return [
           'id' => $this->whenNotNull($this->id),
           'body' => $this->whenNotNull($this->body),
           'read_at' => $this->whenNotNull($this->read_at),
           'send_time' => $this->whenNotNull($this->send_time),
           'created_at' => $this->whenNotNull(Carbon::parse($this->created_at)->format('h:i a')),
           'participation' => ParticipantsResource::make($this->whenLoaded('participation')),
           'files' => MediaResource::collection($this->whenLoaded('files')),
        ];
    }
}
