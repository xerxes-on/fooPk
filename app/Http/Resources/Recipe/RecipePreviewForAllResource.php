<?php

declare(strict_types=1);

namespace App\Http\Resources\Recipe;

use App\Http\Resources\Complexity;
use App\Http\Resources\Diet;
use App\Http\Resources\IngestionResource;
use App\Http\Resources\Price;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Recipe preview for All Recipes list.
 *
 * @note properties are gathered custom and differ from Recipe model
 * @property-read int $is_new
 * @property-read int $calc_invalid
 * @property-read null|int $excluded
 *
 * @property  \App\Models\Recipe $resource
 * @used-by \App\Http\Controllers\Api\RecipesApiController::getAllRecipes()
 * @package App\Http\Resources
 */
final class RecipePreviewForAllResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->resource->id,
            'is_new'       => (bool)$this->is_new,
            'calc_invalid' => (bool)$this->calc_invalid,
            'image'        => asset($this->resource->image->url('small_all')),
            'title'        => $this->resource->title,
            'ingestions'   => IngestionResource::collection($this->resource->ingestions),
            'cooking_time' => $this->resource->cooking_time,
            'unit_of_time' => trans("common.{$this->resource->unit_of_time}"),
            'complexity'   => is_null($this->resource?->complexity) ? null : new Complexity($this->resource->complexity),
            'price'        => is_null($this->resource?->price) ? null : new Price($this->resource->price),
            'diets'        => Diet::collection($this->resource->diets),
            'excluded'     => !is_null($this->excluded)
        ];
    }
}
