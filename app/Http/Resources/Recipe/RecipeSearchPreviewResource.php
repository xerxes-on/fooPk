<?php

namespace App\Http\Resources\Recipe;

use App\Http\Resources\Complexity;
use App\Http\Resources\Diet;
use App\Http\Resources\IngestionResource;
use App\Http\Resources\PaginatedJsonResource;
use App\Http\Resources\Price;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

/**
 * Modify data for recipe search previews as they can be paginated.
 *
 * @property Collection|Recipe[] $resource
 * @used-by \App\Http\Controllers\Recipes\RecipeController::getRecipesByRationFood()
 *
 * @package App\Http\Resources
 */
final class RecipeSearchPreviewResource extends PaginatedJsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof Collection) {
            $data = $this
                ->resource
                ->map(
                    fn(Recipe $recipe): array => [
                        'id'        => $recipe->id,
                        'title'     => $recipe->title,
                        'image'     => asset($recipe->image->url('thumb')),
                        'meal_time' => $recipe->ingestions instanceof Collection ?
                            IngestionResource::collection($recipe->ingestions) :
                            new IngestionResource($recipe->ingestions),
                        'cooking_time' => $recipe->cooking_time,
                        'unit_of_time' => $recipe->unit_of_time,
                        'complexity'   => is_null($recipe?->complexity) ? null : new Complexity($recipe->complexity),
                        'price'        => is_null($recipe?->price) ? null : new Price($recipe->price),
                        'diets'        => $recipe->diets instanceof Collection ?
                            Diet::collection($recipe->diets) :
                            new Diet($recipe->diets),
                        'favourite' => $recipe->favorited(),
                    ]
                );
        } else {
            $data = [
                'id'        => $this->id,
                'title'     => $this->title,
                'image'     => asset($this->image->url('thumb')),
                'meal_time' => $this->ingestions instanceof Collection ?
                    IngestionResource::collection($this->ingestions) :
                    new IngestionResource($this->ingestions),
                'cooking_time' => $this->cooking_time,
                'unit_of_time' => trans("common.$this->unit_of_time"),
                'complexity'   => is_null($this?->complexity) ? null : new Complexity($this->complexity),
                'price'        => is_null($this?->price) ? null : new Price($this->price),
                'diets'        => $this->diets instanceof Collection ?
                    Diet::collection($this->diets) :
                    new Diet($this->diets),
                'favourite' => $this->favorited(),
            ];
        }

        if (is_array($this->paginatedData)) {
            $this->paginatedData['data'] = $data;
            return $this->paginatedData;
        }

        return $data;
    }
}
