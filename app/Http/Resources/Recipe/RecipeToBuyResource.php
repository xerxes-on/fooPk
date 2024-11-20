<?php

declare(strict_types=1);

namespace App\Http\Resources\Recipe;

use App\Http\Resources\IngestionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\File;
use Modules\Ingredient\Http\Resources\ShortIngredientResource;

/**
 * API representation of a recipe in Recipes to Buy list.
 *
 * @property \App\Models\Recipe $resource
 *
 * @package App\Http\Resources\Recipe
 */
final class RecipeToBuyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        // TODO: we need a job to check on the images and call image->reprocess() in case style is missing
        $ingredients = $this->resource->ingredients->merge($this->resource->variableIngredients);
        $imageStyle  = File::exists($this->resource->image->path('small_market')) ? 'small_market' : 'mobile';
        return [
            'id'          => $this->resource->id,
            'title'       => $this->resource->title,
            'mealtime'    => $this->prepareMealtimeString(),
            'ingestions'  => IngestionResource::collection($this->resource->ingestions), /** @deprecated field use 'mealtime' instead */
            'ingredients' => ShortIngredientResource::collection($ingredients),
            'image'       => asset($this->resource->image->url($imageStyle)),
        ];
    }

    private function prepareMealtimeString(): string
    {
        $return          = '';
        foreach ($this->resource->ingestions as $ingestion) {
            $return .= $ingestion->title . ' / ';
        }
        return rtrim($return, ' / ');
    }
}
