<?php

declare(strict_types=1);

namespace Modules\FlexMeal\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Light API representation of a FlexMeal recipe.
 *
 * @package Modules\FlexMeal\Http\Resources
 */
final class FlexMealPreviewResource extends JsonResource
{
    public function __construct(mixed $resource, protected string $imageSize = 'mobile')
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'image' => asset($this->image->url($this->imageSize)),
            'type'  => 'flexmeal',
        ];
    }
}
