<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource for sorting grouped recipes in a Shopping list.
 *
 * @property-read \Illuminate\Support\Collection $resource
 *
 * @package App\Http\Resources\Purchases
 */
final class ShoppingListRecipeFormatterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $response = [];
        foreach ($this->resource as $group => $collection) {
            $response[] = [
                'meal_day' => $group,
                'items'    => ShoppingListRecipeResource::collection($collection)
            ];
        }
        return $response;
    }
}
