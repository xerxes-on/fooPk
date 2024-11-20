<?php

declare(strict_types=1);

namespace App\Http\Resources\Meal;

use App\Http\Resources\IngestionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Meal for My Plan and Weekly Plan.
 *
 * @property-read \App\Models\UserRecipe $resource
 * @used-by \App\Http\Controllers\Api\MealsApiController::getPlan()
 *
 * @package App\Http\Resources
 */
abstract class PlannedMealPreviewAbstract extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        // UserRecipe
        return [
            'ingestion' => new IngestionResource($this->resource->ingestion),
            'cooked'    => (bool)$this->resource->cooked,
            'eat_out'   => (bool)$this->resource->eat_out,
            'meal_date' => date('Y-m-d', strtotime((string)$this->resource->meal_date)),
            'meal_time' => $this->resource->meal_time,
            'recipe'    => $this->getRecipe(),
        ];
    }

    /**
     * Obtain correct recipe resource.
     */
    abstract protected function getRecipe(): mixed;
}
