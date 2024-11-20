<?php

declare(strict_types=1);

namespace Modules\Ingredient\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Designated api resource for ingredient and ingredient tag search.
 *
 * @property array $resource
 *
 * @package Modules\Ingredient\Http\Resources
 */
final class IngredientWithTagsSearchApiResource extends JsonResource
{
    private array $responseData = [];

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $this->prepareTags();
        $this->prepareIngredients();
        return $this->responseData;
    }

    private function prepareIngredients(): void
    {
        foreach ($this->resource['ingredients'] as $item) {
            if (empty($item)) {
                continue;
            }
            $this->responseData[] = [
                'key'   => $item['id'],
                'value' => $item['text'],
            ];
        }
    }

    private function prepareTags(): void
    {
        foreach ($this->resource['tags'] as $item) {
            if (empty($item)) {
                continue;
            }

            $ingredients = [];
            foreach ($item['ingredients'] as $ingredient) {
                $ingredients[] = [
                    'key'   => $ingredient['id'],
                    'value' => $ingredient['text'],
                ];
            }

            $this->responseData[] = [
                'key'         => $item['id'],
                'value'       => $item['text'],
                'ingredients' => $ingredients
            ];
        }
    }
}
