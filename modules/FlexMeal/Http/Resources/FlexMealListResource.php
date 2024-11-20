<?php

declare(strict_types=1);

namespace Modules\FlexMeal\Http\Resources;

use App\Enums\Recipe\RecipeTypeEnum;
use App\Http\Resources\IngestionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API resource for a FlexMeal.
 *
 * @property-read string|int $id
 * @property-read string $name
 * @property-read string $mealtime
 * @property-read string $notes
 * @property-read \App\Models\Ingestion $ingestion
 * @property-read \Modules\FlexMeal\Models\Flexmeal $ingredients
 * @property-read null|array $used_ingredients
 * @property-read \Neko\Stapler\Attachment $image
 *
 * @package App\Http\Resources
 */
final class FlexMealListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'mealtime'         => $this->mealtime,
            'ingestion'        => new IngestionResource($this->ingestion),
            'notes'            => $this->notes,
            'image'            => asset($this->image->url('mobile')),
            'image_large'      => asset($this->image->url('large')),
            'image_is_default' => !(bool)$this->image->originalFilename(), // Required to control delete button in mobile app
            'used_ingredients' => $this?->used_ingredients, //TODO: they are same
            'type'             => RecipeTypeEnum::FLEXMEAL->lowerName(),
        ];
    }
}
