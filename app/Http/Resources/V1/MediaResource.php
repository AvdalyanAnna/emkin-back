<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class MediaResource
 * @package App\Http\Resources\V1
 * @property mixed id
 * @property mixed collection_name
 * @property mixed name
 * @property mixed file_name
 * @property array custom_properties
 * @property mixed generated_conversions
 * @property mixed created_at
 * @property array conversion_urls
 * @property mixed mime_type
 * @property mixed size
 * @property mixed custom_details
 * @property mixed order_column
 */
class MediaResource extends JsonResource {

    /**
     * @param \Illuminate\Http\Request $request
     */
    public function toArray($request) {
        return [
            'id' => $this->whenNotNull($this->id),
            'collection_name' => $this->whenNotNull($this->collection_name),
            'name' => $this->whenNotNull($this->name),
            'mime_type' => $this->whenNotNull($this->mime_type),
            'file_name' => $this->whenNotNull($this->file_name),
            'size' => $this->whenNotNull($this->size),
            'original_full_url' => $this->whenNotNull($this->getFullUrl()),
            'order_column' => $this->whenNotNull($this->order_column),
            'custom_properties' => $this->whenNotNull($this->custom_properties),
            'custom_details' => $this->whenNotNull($this->custom_details),
            'conversion_urls' => $this->whenNotNull($this->conversion_urls),
            'created_at' => $this->whenNotNull($this->created_at),
            'auditModel' => AuditableResource::make($this->whenLoaded('auditModel')),
        ];
    }
}
