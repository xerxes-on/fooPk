<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Services;

use App\Models\{CustomRecipe, Ingestion, Recipe, User};
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Modules\FlexMeal\Models\Flexmeal;
use Modules\ShoppingList\Events\ShoppingListProcessed;
use Modules\ShoppingList\Models\ShoppingListIngredient;
use Modules\ShoppingList\Models\ShoppingListRecipe;

/**
 * Service to add custom ingredients to purchase list.
 *
 * @package App\Services\ShoppingList
 */
final class ShoppingListIngredientsService
{
    /**
     * Add new ingredient to purchase list
     */
    public function addIngredient(User $user, string $title): ShoppingListIngredient
    {
        ShoppingListProcessed::dispatch();

        return ShoppingListIngredient::create([
            'list_id'       => $user->shoppingList()->firstOrCreate()->id,
            'category_id'   => null,
            'ingredient_id' => null,
            'custom_title'  => $title,
        ]);
    }

    public function removeIngredient(User $user, int|string $ingredientId): ?array
    {
        if (!ShoppingListIngredient::whereId($ingredientId)->delete()) {
            return null;
        }

        $shoppingList = $user->shoppingList;

        return array_merge(
            $this->handleStandardRecipes($user, $shoppingList),
            $this->handleCustomRecipes($user, $shoppingList),
            $this->handleFlexMeals($user, $shoppingList)
        );
    }

    private function handleStandardRecipes(User $user, $shoppingList): array
    {
        $deletedRecipes = [];

        foreach ($shoppingList->recipes as $recipe) {
            $mealTime = $user->recipes()
                ->where('recipes.id', $recipe->id)
                ->firstOrFail()
                ->pivot->meal_time;

            $mealDate = $user->recipes()
                ->where('recipes.id', $recipe->id)
                ->firstOrFail()
                ->pivot->meal_date;

            $ingestion = Ingestion::ofKey($mealTime)->firstOrFail();

            $remainingIngredients = $shoppingList->ingredients()
                ->whereIn('ingredient_id', $this->getIngredientIds($user, $recipe, $mealDate, $ingestion->id))
                ->count();

            if ($remainingIngredients === 0) {
                ShoppingListRecipe::where('recipe_id', $recipe->id)
                    ->where('list_id', $shoppingList->id)
                    ->delete();
                $deletedRecipes[] = $recipe->pivot->id;
            }
        }

        return $deletedRecipes;
    }

    private function handleCustomRecipes(User $user, $shoppingList): array
    {
        $deletedRecipes = [];

        foreach ($shoppingList->customRecipes as $recipe) {
            $customPlannedRecipe = $user->customPlannedRecipe($recipe->id)->firstOrFail();

            $mealTime = $customPlannedRecipe->meal_time ?? null;
            $mealDate = $customPlannedRecipe->meal_date ?? null;

            if (!$mealTime || !$mealDate) {
                continue;
            }

            $ingestion = Ingestion::ofKey($mealTime)->firstOrFail();

            $remainingIngredients = $shoppingList->ingredients()
                ->whereIn('ingredient_id', $this->getCustomIngredientIds($user, $recipe, $mealDate, $ingestion->id))
                ->count();

            if ($remainingIngredients === 0) {
                ShoppingListRecipe::where('recipe_id', $recipe->id)
                    ->where('list_id', $shoppingList->id)
                    ->delete();
                $deletedRecipes[] = $recipe->pivot->id;
            }
        }

        return $deletedRecipes;
    }

    private function handleFlexMeals(User $user, $shoppingList): array
    {
        $deletedRecipes = [];

        foreach ($shoppingList->flexmeals as $recipe) {
            $flexMeal = $user->plannedFlexmeals()
                ->where('flexmeal_lists.id', $recipe->id)
                ->firstOrFail();

            $mealTime = $flexMeal->pivot->meal_time ?? null;
            $mealDate = $flexMeal->pivot->meal_date ?? null;

            if (!$mealTime || !$mealDate) {
                continue;
            }

            $ingestion = Ingestion::ofKey($mealTime)->firstOrFail();

            $remainingIngredients = $shoppingList->ingredients()
                ->whereIn('ingredient_id', $this->getFlexIngredientIds($user, $recipe, $mealDate, $ingestion->id))
                ->count();

            if ($remainingIngredients === 0) {
                ShoppingListRecipe::where('recipe_id', $recipe->id)
                    ->where('list_id', $shoppingList->id)
                    ->delete();
                $deletedRecipes[] = $recipe->pivot->id;
            }
        }

        return $deletedRecipes;
    }

    private function getIngredientIds(User $user, Recipe $recipe, string $mealDate, int $ingestionId): array
    {
        try {
            $plannedRecipe = $user->plannedRecipes($recipe->id, $mealDate, $ingestionId)->firstOrFail();

            $recipeData = json_decode($plannedRecipe->calc_recipe_data, true);

            if (!empty($recipeData['ingredients'])) {
                return array_column($recipeData['ingredients'], 'id');
            }

            return [];
        } catch (ModelNotFoundException) {
            return [];
        }
    }

    private function getCustomIngredientIds(User $user, CustomRecipe $recipe, string $mealDate, int $ingestionId): array
    {
        try {
            $plannedRecipe = $user->customPlannedRecipe($recipe->id)->firstOrFail();

            $recipeData = json_decode($plannedRecipe->calc_recipe_data, true);

            if (!empty($recipeData['ingredients'])) {
                return array_column($recipeData['ingredients'], 'id');
            }

            return [];
        } catch (ModelNotFoundException) {
            return [];
        }
    }

    private function getFlexIngredientIds(User $user, Flexmeal $recipe, string $mealDate, int $ingestionId): array
    {
        try {
            $plannedRecipe = $user->plannedFlexmeals()
                ->where('flexmeal_lists.id', $recipe->id)
                ->firstOrFail();

            $recipeData = json_decode($plannedRecipe->calc_recipe_data, true);

            if (!empty($recipeData['ingredients'])) {
                return array_column($recipeData['ingredients'], 'id');
            }

            return [];
        } catch (ModelNotFoundException) {
            return [];
        }
    }

}
