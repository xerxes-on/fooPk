<?php

declare(strict_types=1);

namespace Modules\FlexMeal\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Ingredient\Enums\IngredientTypeEnum;
use Modules\Ingredient\Http\Resources\IngredientHintResource;
use Modules\Ingredient\Services\IngredientConversionService;

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
            'ingredient_id'                  => $this->resource->ingredient->id,
            'ingredient_type'                => IngredientTypeEnum::FIXED->value,
            'main_category'                  => $this->resource->ingredient->category->tree_information['main_category'],
            'ingredient_amount'              => (int)$this->resource->amount,
            'ingredient_text'                => $this->resource->ingredient->unit->visibility ? "{$this->resource->ingredient->unit->short_name} {$this->resource->ingredient->name}" : $this->resource->ingredient->name,
            'ingredient_name'                => $this->resource->ingredient->name,
            'ingredient_unit'                => $this->resource->ingredient->unit->visibility ? $this->resource->ingredient->unit->short_name : '',
            'allow_replacement'              => false,
            'hint'                           => new IngredientHintResource($this->resource->ingredient),
            IngredientConversionService::KEY => (object)app(IngredientConversionService::class)->generateData($this->resource->ingredient, (int)$this->resource->amount)
        ];
    }
}
