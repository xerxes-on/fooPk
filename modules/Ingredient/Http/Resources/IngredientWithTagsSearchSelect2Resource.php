<?php

declare(strict_types=1);

namespace Modules\Ingredient\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Designated api resource for ingredient and ingredient tag search over Select2 vendor.
 *
 * @property array $resource
 *
 * @package Modules\Ingredient\Http\Resources
 */
final class IngredientWithTagsSearchSelect2Resource extends JsonResource
{
    private array $responseData = [];

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $this->prepareTags();
        $this->prepareIngredients();

        return ['results' => $this->responseData];
    }

    private function prepareIngredients(): void
    {
        foreach ($this->resource['ingredients'] as $item) {
            if (empty($item)) {
                continue;
            }
            $this->responseData[] = [
                'id'   => $item['id'],
                'text' => $item['text'],
            ];
        }
    }

    private function prepareTags(): void
    {
        foreach ($this->resource['tags'] as $item) {
            if (empty($item)) {
                continue;
            }
            $this->responseData[] = [
                'id'          => $item['id'],
                'text'        => $item['text'],
                'ingredients' => $item['ingredients']
            ];
        }
    }
}
