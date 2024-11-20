<?php

namespace Modules\FlexMeal\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Ingredient\Http\Resources\IngredientResource;

/**
 * API resource of Flex Meal Ingredient
 *
 * @property-read string|int $id
 * @property-read string|int amount
 * @property-read string|int ingredient
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
        return [
            'id'         => $this->id,
            'amount'     => convertToNumber($this->amount),
            'ingredient' => new IngredientResource($this->ingredient),
        ];
    }
}
