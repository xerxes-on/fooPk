<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Ingredient\Services\IngredientConversionService;

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
            $response                                   = $this->getResponseData($item);
            $response['hint']                           = $item['hint'] ?? [];
            $response[IngredientConversionService::KEY] = (object)($item[IngredientConversionService::KEY] ?? []);
            $return[]                                   = $response;
        }
        return $return;
    }

    private function getResponseData(array $item): array
    {
        // If we do not have ingredient ID meaning we have user added ingredient
        return isset($item['ingredient_id']) ? [
            'id'            => $item['id'],
            'ingredient_id' => $item['ingredient_id'],
            'name'          => $item['name'],
            'amount'        => $item['amount'],
            'unit'          => $item['unit'],
            'completed'     => $item['completed'],
            'is_custom'     => false
        ] : [
            'id'            => $item['id'],
            'ingredient_id' => null,
            'name'          => $item['custom_title'],
            'amount'        => null,
            'unit'          => null,
            'completed'     => $item['completed'],
            'is_custom'     => true
        ];
    }
}
