<?php

declare(strict_types=1);

namespace Modules\FlexMeal\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Ingredient\Http\Resources\IngredientResource;
use Modules\Ingredient\Services\IngredientConversionService;

/**
 * API resource of Flex Meal Ingredient
 *
 * @property-read \Modules\FlexMeal\Models\Flexmeal $resource
 *
 * @package App\Http\Resources
 */
final class FlexMealResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $amount = convertToNumber($this->resource->amount);
        return [
            'id'                             => $this->resource->id,
            'amount'                         => $amount,
            'ingredient'                     => new IngredientResource($this->resource->ingredient),
            IngredientConversionService::KEY => (object)app(IngredientConversionService::class)->generateData($this->resource->ingredient, $amount)
        ];
    }
}
