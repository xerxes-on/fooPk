<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * Light representation of a custom (created from common one) recipe.
 */
class CustomRecipePreview extends ResourceWithDynamicImageSize
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $original = $this->originalRecipe;
        return [
            'id'           => $this->id,
            'title'        => $original->title,
            'complexity'   => new Complexity($original->complexity),
            'favourited'   => $original->favorited(),
            'cooking_time' => $original->cooking_time,
            'unit_of_time' => $original->unit_of_time,
            'image'        => asset($original->image->url($this->imageSize)),
            'custom'       => true,
            'type'         => 'custom',
        ];
    }
}
