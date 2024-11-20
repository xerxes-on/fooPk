<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of ingestion.
 *
 * Usually ingestion is a meal of the day.
 *
 * @property-read \App\Models\Ingestion $resource
 *
 * @package App\Http\Resources
 */
final class IngestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'     => $this->resource->id,
            'title'  => $this->resource->title,
            'key'    => $this->resource->key,
            'active' => (bool)$this->resource->active
        ];
    }
}
