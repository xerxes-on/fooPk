<?php

namespace Modules\Ingredient\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Short API representation of an ingredient.
 *
 * @property \Modules\Ingredient\Models\Ingredient $resource
 *
 * @package Modules\Ingredient\Http\Resources
 */
final class ShortIngredientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'   => $this->resource->id,
            'name' => $this->resource->name,
        ];
    }
}
