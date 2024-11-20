<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of a custom ingredient added by user to purchase list.
 *
 * @property-read \Modules\ShoppingList\Models\ShoppingListIngredient $resource
 * @package App\Http\Resources\Purchases
 */
final class ShoppingListCustomIngredientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->resource->id,
            'list_id'      => $this->resource->list_id,
            'custom_title' => $this->resource->custom_title,
        ];
    }
}
