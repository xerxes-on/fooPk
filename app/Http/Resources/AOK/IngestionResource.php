<?php

namespace App\Http\Resources\AOK;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IngestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */


    public function toArray(Request $request): array
    {
        return [
            'title'       => $this->resource['title'],
            'key'         => $this->resource['key'],
            'ingredients' => IngredientResource::collection($this->resource['ingredients']),
            'nutrition'   => $this->resource['nutrition']
        ];
    }
}
