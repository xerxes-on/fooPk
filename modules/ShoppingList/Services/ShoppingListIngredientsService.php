<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Services;

use App\Exceptions\PublicException;
use App\Models\{CustomRecipe, Ingestion, Recipe, User};
use Illuminate\Database\Eloquent\Collection;
use Modules\FlexMeal\Models\FlexmealLists;
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

    /**
     * @throws PublicException
     */
    public function removeIngredient(User $user, int|string $ingredientId): array
    {
        if (!ShoppingListIngredient::whereId($ingredientId)->delete()) {
            throw new PublicException(__('shopping-list::messages.error.item_removal'));
        }

        $shoppingList = $user->shoppingList;
        $ingestions = Ingestion::getAll()->keyBy('key');
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
            if (!$shoppingList->ingredients()
                ->whereIn(
                    'ingredient_id',
                    $this->getIngredientIds($user, $recipe, $mealDate, $ingestion->id)
                )
                ->exists()) {
                $id = $this->deleteRecipe($recipe, $shoppingList->id);
                if ($id === 0) {
                    continue;
                }
                $deletedRecipes[] = $id;
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

            $ingestion = $ingestions[$mealTime] ?? null;
            if (!$ingestion) {
                continue;
            }

            if (!$shoppingList->ingredients()->whereIn(
                'ingredient_id',
                $this->getIngredientIds($user, $recipe)
            )
                ->exists()) {
                $id = $this->deleteRecipe($recipe, $shoppingList->id);
                if ($id === 0) {
                    continue;
                }
                $deletedRecipes[] = $id;
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
            $mealDate = $flexMeal->pivot->meal_date;
            $ingestion = $ingestions[$mealTime] ?? null;
            if (!$ingestion) {
                continue;
            }
            if ($shoppingList->ingredients()
                    ->whereIn('ingredient_id', $this->getIngredientIds($user, $recipe, $mealDate, $ingestion->id))
                    ->count() === 0) {
                $id = $this->deleteRecipe($recipe, $shoppingList->id);
                if ($id === 0) {
                    continue;
                }
                $deletedRecipes[] = $id;
            }
        }
        return $deletedRecipes;
    }

    private function getIngredientIds(User $user, $recipe, string $mealDate = null, int $ingestionId = null): array
    {
        switch (true) {
            case $recipe instanceof Recipe:
                $plannedRecipe = $user->plannedRecipesForGettingIngredients(
                    $recipe->id,
                    $mealDate,
                    $ingestionId
                )->first();
                break;
            case $recipe instanceof CustomRecipe:
                $plannedRecipe = $user->customplannedRecipeForGettingIngredient($recipe->id)->first();
                break;
            case $recipe instanceof FlexmealLists:
                $plannedRecipe = $user->plannedFlexmealForGettingIngredients($recipe->id, $mealDate,
                    $ingestionId)->first();
                return $plannedRecipe->ingredients?->pluck('ingredient_id')->toArray();
            default:
                return [];
        }
        if (empty($plannedRecipe) || empty($plannedRecipe->calc_recipe_data)) {
            return [];
        }
        $recipeData = json_decode($plannedRecipe->calc_recipe_data, true);
        if (empty($recipeData['ingredients'])) {
            return [];
        }
        return array_column($recipeData['ingredients'], 'id');
    }

    private function deleteRecipe(Recipe|FlexmealLists|CustomRecipe $recipe, int $shoppingListId): int
    {
        if (!ShoppingListRecipe::where('recipe_id', $recipe->id)->where('list_id', $shoppingListId)->delete()) {
            return 0;
        }
        return $recipe->pivot->id;
    }

}
