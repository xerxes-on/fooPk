<?php

namespace Modules\FlexMeal\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Ingredient\Http\Resources\IngredientHintResource;

/**
 * API representation of an ingredient of a flexmeal in a planned meal.
 *
 * @property \Modules\FlexMeal\Models\Flexmeal $resource
 *
 * @package Modules\Flexmeal\Http\Resources
 */
final class FlexMealIngredientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'ingredient_id'     => $this->resource->ingredient->id,
            'ingredient_type'   => 'fixed',
            'main_category'     => $this->resource->ingredient->category->tree_information['main_category'],
            'ingredient_amount' => (int)$this->resource->amount,
            'ingredient_text'   => $this->resource->ingredient->unit->short_name . ' ' . $this->resource->ingredient->name,
            'ingredient_name'   => $this->resource->ingredient->name,
            'ingredient_unit'   => $this->resource->ingredient->unit->short_name,
            'allow_replacement' => false,
            'hint'              => new IngredientHintResource($this->resource->ingredient)
        ];
    }
}
