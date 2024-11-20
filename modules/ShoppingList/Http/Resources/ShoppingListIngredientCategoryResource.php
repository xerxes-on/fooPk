<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of an ingredients category with related ingredients used in Purchases list.
 *
 * @package App\Http\Resources\Purchases
 */
final class ShoppingListIngredientCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'category_id'   => $this->resource['category']['id'],
            'category_name' => $this->resource['category']['name'],
            'ingredients'   => new ShoppingListIngredientResource($this->resource['ingredients'])
        ];
    }
}
