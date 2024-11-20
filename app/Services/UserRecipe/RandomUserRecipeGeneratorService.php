<?php

declare(strict_types=1);

namespace App\Services\UserRecipe;

use App\Enums\Recipe\RecipeStatusEnum;
use App\Exceptions\PublicException;
use App\Models\User;
use App\Models\UserRecipe;
use Auth;

final class RandomUserRecipeGeneratorService
{
    /**
     * @throws PublicException
     */
    public function generateRandomRecipe(UserRecipe $model, array $excludedRecipes, ?User $user = null): int
    {
        if (is_null($user)) {
            $user = Auth::user();
        }

        if (is_null($user)) {
            throw new PublicException(trans('api.exclude_meal_public_error'));
        }

        $recipe = $user
            ->allRecipes()
            ->leftJoin(
                'user_recipe_calculated',
                'recipes.id',
                '=',
                'user_recipe_calculated.recipe_id'
            )
            ->where(
                [
                    ['user_recipe_calculated.user_id', $model->user_id],
                    ['user_recipe_calculated.ingestion_id', $model->ingestion_id],
                    ['user_recipe_calculated.invalid', false],
                    ['recipes.status','=', RecipeStatusEnum::ACTIVE->value]
                ]
            )
            ->setEagerLoads([])
            ->inRandomOrder();

        if ($excludedRecipes !== []) {
            $recipe = $recipe->whereNotIn('recipes.id', $excludedRecipes);
        }

        $recipe = $recipe->first(['recipes.id']);

        if (is_null($recipe)) {
            throw new PublicException(trans('api.exclude_meal_public_error'));
        }

        $model->recipe_id          = $recipe->id;
        $model->original_recipe_id = $recipe->id; // This field is required for restoring original recipe, restoring for hidden recipe is not possible
        $model->custom_recipe_id   = null;
        $model->flexmeal_id        = null;
        $model->save();

        return $recipe->id;
    }
}
