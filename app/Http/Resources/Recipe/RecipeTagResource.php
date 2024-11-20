<?php

namespace App\Http\Resources\Recipe;

use App\Models\RecipeTag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of a recipe tag.
 *
 * @property RecipeTag $resource
 * @package App\Http\Resources\Recipe
 */
final class RecipeTagResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->resource->id,
            'title' => $this->resource->title,
            // TODO:: review title/name
            'name' => $this->resource->title,
        ];
    }
}
