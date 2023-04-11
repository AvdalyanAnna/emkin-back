<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class LanguageResource
 * @property mixed id
 * @property mixed name
 * @property mixed native
 * @property mixed code
 * @property mixed regional
 * @property mixed default
 * @property mixed created_at
 * @package App\Http\Resources\V1\
 */
class LanguageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->whenNotNull($this->id),
            'name' => $this->whenNotNull($this->name),
            'native' => $this->whenNotNull($this->native),
            'code' => $this->whenNotNull($this->code),
            'regional' => $this->whenNotNull($this->regional),
            'default' => $this->whenNotNull($this->default),
            'created_at' => $this->whenNotNull($this->created_at),
        ];
    }
}
