<?php

declare(strict_types=1);

namespace App\Http\Resources\Meal;

use App\Http\Resources\IngestionResource;
use App\Http\Resources\Recipe\UsersRecipeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of a planned users' meal.
 *
 * @property-read \App\Models\Recipe $resource
 * @package App\Http\Resources
 */
final class PlannedMealResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $meal = $this->resource->pivot;
        return [
            'ingestion'                 => new IngestionResource($meal->ingestion),
            'cooked'                    => (bool)$meal->cooked,
            'eat_out'                   => (bool)$meal->eat_out,
            'meal_date'                 => date('Y-m-d', strtotime((string)$meal->meal_date)),
            'meal_time'                 => $meal->meal_time,
            'recipe'                    => new UsersRecipeResource($this->resource),
            'user_available_ingestions' => IngestionResource::collection($request->user()->allowed_ingestions),
        ];
    }
}
