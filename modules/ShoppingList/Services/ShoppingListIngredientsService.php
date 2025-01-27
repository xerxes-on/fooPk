<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Services;

use App\Models\{CustomRecipe, Ingestion, Recipe, User};
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Modules\FlexMeal\Models\Flexmeal;
use Modules\ShoppingList\Events\ShoppingListProcessed;
use Modules\ShoppingList\Models\ShoppingList;
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
        $ingestions = Cache::remember('ingestions_by_key', now()->addHour(), function () {
            return Ingestion::without('translations')->get()->keyBy('key');
        });
        return array_merge(
            $this->handleStandardRecipes($user, $shoppingList, $ingestions),
            $this->handleCustomRecipes($user, $shoppingList, $ingestions),
            $this->handleFlexMeals($user, $shoppingList, $ingestions)
        );
    }

    private function handleStandardRecipes(User $user, ShoppingList $shoppingList, Collection $ingestions): array
    {
        $deletedRecipes = [];

        $userRecipes = $user->recipes()
            ->whereIn('recipes.id', $shoppingList->recipes->pluck('id'))
            ->without('translations')
            ->get()
            ->keyBy('id');

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

    private function handleCustomRecipes(User $user, ShoppingList $shoppingList, Collection $ingestions): array
    {
        $deletedRecipes = [];

        $customPlannedRecipes = $user->datedCustomRecipes()
            ->whereIn('custom_recipes.id', $shoppingList->customRecipes->pluck('id'))
            ->without('translations')
            ->get()
            ->keyBy('id');
        foreach ($shoppingList->customRecipes as $recipe) {
            $customPlannedRecipe = $customPlannedRecipes[$recipe->id]->pivot ?? null;

            if (!$customPlannedRecipe) {
                continue;
            }
            $mealTime = $customPlannedRecipe->meal_time ?? null;
            //            $mealDate = $customPlannedRecipe->meal_date ?? null;

            $ingestion = $ingestions[$mealTime] ?? null;
            if (!$ingestion) {
                continue;
            }
            $remainingIngredients = $shoppingList->ingredients()
                ->whereIn(
                    'ingredient_id',
                    $this->getCustomIngredientIds($user, $recipe)
                )
                ->count();

            if ($remainingIngredients === 0) {
                $deletedRecipes[] = $this->deleteRecipe($recipe, $shoppingList->id);
            }
        }

        return $deletedRecipes;
    }

    private function handleFlexMeals(User $user, ShoppingList $shoppingList, Collection $ingestions): array
    {
        $deletedRecipes = [];

        $flexMeals = $user->plannedFlexmeals()
            ->whereIn('flexmeal_lists.id', $shoppingList->flexmeals->pluck('id'))
            ->without('translations')
            ->get()
            ->keyBy('id');
        foreach ($shoppingList->flexmeals as $recipe) {
            $flexMeal = $flexMeals[$recipe->id] ?? null;
            if (!$flexMeal || !$flexMeal->pivot->meal_time || !$flexMeal->pivot->meal_date) {
                continue;
            }

            $mealTime = $flexMeal->pivot->meal_time;
            //            $mealDate = $flexMeal->pivot->meal_date;
            $ingestion = $ingestions[$mealTime] ?? null;
            if (!$ingestion) {
                continue;
            }

            $remainingIngredients = $shoppingList->ingredients()
                ->whereIn('ingredient_id', $this->getFlexIngredientIds($user, $recipe))
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

    private function getCustomIngredientIds(User $user, CustomRecipe $recipe): array
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

    private function getFlexIngredientIds(User $user, Flexmeal $recipe): array
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

    private function deleteRecipe(Recipe|Flexmeal|CustomRecipe $recipe, int $shoppingListId): int
    {
        ShoppingListRecipe::where('recipe_id', $recipe->id)
            ->where('list_id', $shoppingListId)
            ->delete();
        return $recipe->pivot->id;
    }
}
