<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of an ingredient used inside Purchase list.
 *
 * @property-read array $resource
 * @package App\Http\Resources\Purchases
 */
final class ShoppingListIngredientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        // We usually receive here an array
        $return = [];
        foreach ($this->resource as $item) {
            // If we do not have ingredient ID meaning we have user added ingredient
            if (isset($item['ingredient_id'])) {
                $response = [
                    'id'            => $item['id'],
                    'ingredient_id' => $item['ingredient_id'],
                    'name'          => $item['name'],
                    'amount'        => $item['amount'],
                    'unit'          => $item['unit'],
                    'completed'     => $item['completed'],
                    'is_custom'     => false
                ];
            } else {
                $response = [
                    'id'            => $item['id'],
                    'ingredient_id' => null,
                    'name'          => $item['custom_title'],
                    'amount'        => null,
                    'unit'          => null,
                    'completed'     => $item['completed'],
                    'is_custom'     => true
                ];
            }
            $response['hint'] = $item['hint'] ?? [];
            $return[]         = $response;
        }
        return $return;
    }
}
