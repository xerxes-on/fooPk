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

        $userRecipes = $user->recipes()
            ->whereIn('recipes.id', $shoppingList->recipes->pluck('id'))
            ->without('translations')
            ->get()
            ->keyBy('id');
        $ingestions = Ingestion::whereIn('key', $userRecipes->pluck('pivot.meal_time'))
            ->without('translations')
            ->get()
            ->keyBy('key');
        foreach ($shoppingList->recipes as $recipe) {

            $meal = $userRecipes[$recipe->id]->pivot ?? null;

            if (!$meal) {
                continue;
            }

            $mealTime = $meal->meal_time;
            $mealDate = $meal->meal_date;

            $ingestion = $ingestions[$mealTime] ?? null;
            if (!$ingestion) {
                continue;
            }

            $remainingIngredients = $shoppingList->ingredients()
                ->whereIn('ingredient_id', $this->getIngredientIds($user, $recipe, $mealDate, $ingestion->id))
                ->count();

            if ($remainingIngredients === 0) {
                $deletedRecipes[] = $this->deleteRecipe($recipe, $shoppingList->id);
            }

        }

        return $deletedRecipes;
    }

    private function handleCustomRecipes(User $user, $shoppingList): array
    {
        $deletedRecipes = [];

        $customPlannedRecipes = $user->datedCustomRecipes()
            ->whereIn('custom_recipes.id', $shoppingList->customRecipes->pluck('id'))
            ->without('translations')
            ->get()
            ->keyBy('id');

        $ingestions = Ingestion::whereIn('key', $customPlannedRecipes->pluck('pivot.meal_time'))
            ->without('translations')
            ->get()
            ->keyBy('key');
        foreach ($shoppingList->customRecipes as $recipe) {
            $customPlannedRecipe = $customPlannedRecipes[$recipe->id]->pivot ?? null;
            //            dd($customPlannedRecipe->pivot);
            if (!$customPlannedRecipe) {
                continue;
            }
            $mealTime = $customPlannedRecipe->meal_time ?? null;
            $mealDate = $customPlannedRecipe->meal_date ?? null;

            $ingestion = $ingestions[$mealTime] ?? null;
            if (!$ingestion) {
                continue;
            }
            $remainingIngredients = $shoppingList->ingredients()
                ->whereIn('ingredient_id',
                    $this->getCustomIngredientIds($user, $recipe, (string) $mealDate, $ingestion->id))
                ->count();

            if ($remainingIngredients === 0) {
                $deletedRecipes[] = $this->deleteRecipe($recipe, $shoppingList->id);
            }
        }

        return $deletedRecipes;
    }

    private function handleFlexMeals(User $user, $shoppingList): array
    {
        $deletedRecipes = [];

        $flexMeals = $user->plannedFlexmeals()
            ->whereIn('flexmeal_lists.id', $shoppingList->flexmeals->pluck('id'))
            ->without('translations')
            ->get()
            ->keyBy('id');

        $ingestions = Ingestion::whereIn('key', $flexMeals->pluck('pivot.meal_time'))
            ->without('translations')
            ->get()
            ->keyBy('key');
        foreach ($shoppingList->flexmeals as $recipe) {
            $flexMeal = $flexMeals[$recipe->id] ?? null;

            if (!$flexMeal || !$flexMeal->pivot->meal_time || !$flexMeal->pivot->meal_date) {
                continue;
            }

            $mealTime = $flexMeal->pivot->meal_time;
            $mealDate = $flexMeal->pivot->meal_date;

            $ingestion = $ingestions[$mealTime] ?? null;
            if (!$ingestion) {
                continue;
            }



            $remainingIngredients = $shoppingList->ingredients()
                ->whereIn('ingredient_id', $this->getFlexIngredientIds($user, $recipe, $mealDate, $ingestion->id))
                ->count();
            if ($remainingIngredients === 0) {
                $deletedRecipes[] = $this->deleteRecipe($recipe, $shoppingList->id);
            }
        }

        return $deletedRecipes;
    }

    private function getIngredientIds(User $user, Recipe $recipe, string $mealDate, int $ingestionId): array
    {
        try {
            $plannedRecipe = $user->plannedRecipesForGettingIngredients($recipe->id, $mealDate, $ingestionId)
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

    private function getCustomIngredientIds(User $user, CustomRecipe $recipe, string $mealDate, int $ingestionId): array
    {
        try {
            $plannedRecipe = $user->customplannedRecipeForGettingIngredient($recipe->id)
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

    private function deleteRecipe($recipe, $shoppingListId)
    {
        ShoppingListRecipe::where('recipe_id', $recipe->id)
            ->where('list_id', $shoppingListId)
            ->delete();
        return $recipe->pivot->id;
    }
}
