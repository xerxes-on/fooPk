<?php

declare(strict_types=1);

namespace Modules\Ingredient\Http\Resources;

use App\Http\Resources\CategoryPreview;
use App\Http\Resources\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Detailed API representation of an ingredient.
 *
 * @property \Modules\Ingredient\Models\Ingredient $resource
 *
 * @package Modules\Ingredient\Http\Resources
 */
final class IngredientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->resource->id,
            'category'         => new CategoryPreview($this->resource->category),
            'main_category_id' => $this->resource->category->tree_information['main_category'],
            'proteins'         => $this->resource->proteins,
            'fats'             => $this->resource->fats,
            'carbohydrates'    => $this->resource->carbohydrates,
            'calories'         => $this->resource->calories,
            'unit'             => new Unit($this->resource->unit),
            'name'             => $this->resource->name,
        ];
    }
}
