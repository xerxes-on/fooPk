<?php

namespace App\Http\Resources\AOK;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->resource['id'],
            'title'        => $this->resource['title'],
            'image'        => $this->resource['image'],
            'meal_time'    => IngestionResource::collection($this->resource['meal_time']),
            'complexity'   => empty($this->resource['complexity']) ? null : new ComplexityResource($this->resource['complexity']),
            'price'        => empty($this->resource['price']) ? null : new PriceResource($this->resource['price']),
            'cooking_time' => ['value' => $this->resource['cooking_time'], 'unit' => $this->resource['unit_of_time']],
            'diets'        => DietResource::collection($this->resource['diets']),
            'seasons'      => $this->resource['seasons'],
            'steps'        => StepResource::collection($this->resource['steps'])
        ];
    }
}
