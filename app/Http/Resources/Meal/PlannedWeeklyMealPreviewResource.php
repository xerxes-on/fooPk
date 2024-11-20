<?php

declare(strict_types=1);

namespace App\Http\Resources\Meal;

use App\Http\Resources\CustomRecipePreview;
use App\Http\Resources\Recipe\RecipePreviewResource;
use Modules\FlexMeal\Http\Resources\FlexMealPreviewResource;

/**
 * Meal for My Plan and Weekly Plan.
 *
 * @property-read \App\Models\UserRecipe $resource
 * @used-by \App\Http\Controllers\Api\MealsApiController::getPlan()
 *
 * @package App\Http\Resources\Meal
 */
final class PlannedWeeklyMealPreviewResource extends PlannedMealPreviewAbstract
{
    /**
     * Obtain correct recipe resource.
     */
    protected function getRecipe(): RecipePreviewResource|CustomRecipePreview|FlexMealPreviewResource|string
    {
        if ($this->resource->recipe) {
            return new RecipePreviewResource($this->resource->recipe, 'mobile');
        }

        if ($this->resource->customRecipe?->originalRecipe) {
            return new CustomRecipePreview($this->resource->customRecipe, 'mobile');
        }

        if ($this->resource->flexmeal) {
            return new FlexMealPreviewResource($this->resource->flexmeal, 'thumb');
        }

        return 'error'; // TODO: probably should handle somehow
    }
}
