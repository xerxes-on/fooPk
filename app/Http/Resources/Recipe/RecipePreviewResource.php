<?php

declare(strict_types=1);

namespace App\Http\Resources\Recipe;

use App\Http\Resources\Complexity;
use App\Http\Resources\Meal\PlannedDailyMealPreviewResource;
use App\Http\Resources\ResourceWithDynamicImageSize;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Recipe without details.
 * It's meant for My Plan and Weekly Plan.
 *
 * @property-read \App\Models\Recipe $resource
 * @used-by PlannedDailyMealPreviewResource::toArray()
 *
 * @package App\Http\Resources
 */
final class RecipePreviewResource extends ResourceWithDynamicImageSize
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->resource->id,
            'title'        => $this->resource->title,
            'complexity'   => new Complexity($this->resource->complexity),
            'favourited'   => $this->resource->favorited(),
            'cooking_time' => $this->resource->cooking_time,
            'unit_of_time' => trans("common.{$this->resource->unit_of_time}"),
            'image'        => asset($this->resource->image->url($this->imageSize)),
            'custom'       => false,
            'type'         => 'common',
        ];
    }
}
