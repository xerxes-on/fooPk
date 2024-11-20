<?php

declare(strict_types=1);

namespace Modules\Ingredient\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Detailed API representation of an ingredient hint.
 *
 * @property \Modules\Ingredient\Models\Ingredient $resource
 *
 * @note Resource should be passed as Ingredient model.
 *
 * @used-by FlexMealIngredientResource::toArray()
 * @package Modules\Ingredient\Http\Resources
 */
final class IngredientHintResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $hint = $this->resource?->hint?->translations->where('locale', $request->user()->lang)->first();
        return $hint !== null ? [
            'title'     => $this->resource->name,
            'content'   => $hint->content,
            'link_url'  => $hint->link_url,
            'link_text' => $hint->link_text,
        ] : [];
    }
}
