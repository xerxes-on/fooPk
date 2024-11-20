<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Http\Resources;

use App\Http\Resources\IngestionResource;
use App\Models\CustomRecipe;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\FlexMeal\Models\FlexmealLists;

/**
 * API representation of a recipe in a Shopping list.
 *
 * @property-read \App\Models\Recipe|\App\Models\CustomRecipe|\Modules\FlexMeal\Models\FlexmealLists $resource
 * TODO: note we have deprecation fields
 * @package App\Http\Resources\Purchases
 */
final class ShoppingListRecipeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return match (get_class($this->resource)) {
            Recipe::class        => $this->prepareResponseForOriginalRecipe(),
            CustomRecipe::class  => $this->prepareResponseForCustomRecipe(),
            FlexmealLists::class => $this->prepareResponseForFlexmeal(),
            default              => throw new \InvalidArgumentException('Unknown recipe type'),
        };
    }

    private function prepareResponseForOriginalRecipe(): array
    {
        return [
            'id'          => $this->resource->id,
            'title'       => $this->resource?->title,
            'image'       => asset($this->resource->image->url('small_market')),
            'edited'      => false,
            'recipe_type' => $this->resource?->pivot?->recipe_type,
            'servings'    => $this->resource?->pivot?->servings,
            'meal_time'   => $this->resource?->pivot?->mealtime,
            'meal_date'   => $this->resource?->pivot?->meal_day,
            'ingestions'  => IngestionResource::collection(collect($this->resource->ingestions)),
            'ingestion'  => new IngestionResource($this->resource->ingestions->firstWhere('id', $this->resource->pivot->mealtime)),
        ];
    }

    private function prepareResponseForCustomRecipe(): array
    {
        return [
            'id'          => $this->resource->id,
            'title'       => $this->resource?->originalRecipe->title,
            'image'       => asset($this->resource?->originalRecipe->image->url('small_market')),
            'edited'      => false,
            'recipe_type' => $this->resource?->pivot?->recipe_type,
            'ingestions'  => [new IngestionResource($this->resource->ingestion)],
            'ingestion'  => new IngestionResource($this->resource->ingestion),
            'servings'    => $this->resource?->pivot?->servings,
            'meal_time'   => $this->resource?->pivot?->mealtime,
            'meal_date'   => $this->resource?->pivot?->meal_day,
        ];
    }

    private function prepareResponseForFlexmeal(): array
    {
        return [
            'id'          => $this->resource->id,
            'title'       => $this->resource?->name,
            'image'       => asset($this->resource->image->url('small_market')),
            'edited'      => false,
            'recipe_type' => $this->resource?->pivot?->recipe_type,
            'ingestions'  => [new IngestionResource($this->resource->ingestion)],
            'ingestion'  => new IngestionResource($this->resource->ingestion),
            'servings'    => $this->resource?->pivot?->servings,
            'meal_time'   => $this->resource?->pivot?->mealtime,
            'meal_date'   => $this->resource?->pivot?->meal_day,
        ];
    }
}
