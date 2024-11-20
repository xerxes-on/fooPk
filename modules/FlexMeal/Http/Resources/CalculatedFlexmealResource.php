<?php

namespace Modules\FlexMeal\Http\Resources;

use App\Http\Resources\IngestionResource;
use App\Http\Resources\PaginatedJsonResource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Modules\FlexMeal\Models\FlexmealLists;

/**
 * API resource of calculated Flex Meal Ingredient
 *
 * @property-read int $id
 * @property-read int $user_id
 * @property-read string $name
 * @property-read string $mealtime
 * @property-read string|null $notes
 * @property-read  \Neko\Stapler\Attachment $image
 * @property-read string $created_at
 * @property-read string $updated_at
 * @property-read \Illuminate\Support\Collection $used_ingredients
 * @property-read \Illuminate\Support\Collection $calculated_nutrients
 * @property-read \App\Models\ingestion $ingestion
 *
 * @package App\Http\Resources
 */
final class CalculatedFlexmealResource extends PaginatedJsonResource
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
                    fn(FlexmealLists $list): array => [
                        'id'                   => $list->id,
                        'user_id'              => $list->user_id,
                        'name'                 => $list->name,
                        'mealtime'             => $list->mealtime,
                        'notes'                => $list->notes,
                        'image'                => asset($list->image->url('thumb')),
                        'created_at'           => $list->created_at,
                        'updated_at'           => $list->updated_at,
                        'used_ingredients'     => $list?->used_ingredients,
                        'calculated_nutrients' => $list?->calculated_nutrients,
                        'ingestion'            => new IngestionResource($list->ingestion),
                    ]
                );
        } else {
            $data = [
                'id'                   => $this->id,
                'user_id'              => $this->user_id,
                'name'                 => $this->name,
                'mealtime'             => $this->mealtime,
                'notes'                => $this->notes,
                'image'                => asset($this->image->url('mobile')),
                'created_at'           => $this->created_at,
                'updated_at'           => $this->updated_at,
                'used_ingredients'     => $this->used_ingredients,
                'calculated_nutrients' => $this->calculated_nutrients,
                'ingestion'            => new IngestionResource($this->ingestion),
            ];
        }

        if (is_array($this->paginatedData)) {
            $this->paginatedData['data'] = $data;
            return $this->paginatedData;
        }

        return $data;
    }
}
