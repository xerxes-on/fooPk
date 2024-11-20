<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Services;

use App\Enums\Recipe\RecipeTypeEnum;
use App\Exceptions\PublicException;
use App\Models\{CustomRecipe, Recipe, User};
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use Modules\FlexMeal\Models\Flexmeal;
use Modules\FlexMeal\Models\FlexmealLists;
use Modules\Ingredient\Models\Ingredient;
use Modules\ShoppingList\Events\ShoppingListProcessed;
use Modules\ShoppingList\Models\ShoppingListRecipe;
use Modules\ShoppingList\Traits\{CanCombineIngredientsInShoppingList};
use Modules\ShoppingList\Traits\CanStoreIngredientsOfShoppingList;
use Throwable;

/**
 * Service for managing recipes in purchase list.
 *
 * @package App\Services\ShoppingList
 */
final class ShoppingListRecipesService
{
    use CanCombineIngredientsInShoppingList;
    use CanStoreIngredientsOfShoppingList;

    /**
     * Collection of Recipes extracted from generated shopping list.
     */
    private FlexmealLists|Recipe|CustomRecipe|null $recipe = null;

    /**
     * Delete recipe from purchase list.
     *
     * @note recipes can be totally identical in shopping's list, and sometimes only one all params can actually differ.
     *
     * @throws ModelNotFoundException
     * @throws InvalidArgumentException
     * @throws PublicException
     */
    public function deleteRecipe(
        User    $user,
        int     $recipeId,
        int     $recipeType,
        ?string $mealDate = null,
        ?int    $mealtime = null
    ): void {
        $list = $user->shoppingList()->with('ingredients')->firstOrCreate();
        /**
         * We may have multiple recipes with the same id, but different meal_day.
         * We must specify which one to delete. Passing $mealDate param allows us to do that.
         * Otherwise, all duplicated recipes will be deleted.
         */
        $where = [
            ['recipe_id', $recipeId],
            ['recipe_type', $recipeType],
        ];
        if (is_string($mealDate)) {
            $where[] = ['meal_day', $mealDate];
        }
        if (is_int($mealtime)) {
            $where[] = ['mealtime', $mealtime];
        }
        $recipeInList = ShoppingListRecipe::whereListId($list->id)->where($where)->firstOrFail();

        // Grab correct data according to list recipe type
        $this->recipe = match ($recipeInList->recipe_type) {
            RecipeTypeEnum::ORIGINAL => $user->calculatedRecipeData($recipeInList->recipe_id)->first(),
            RecipeTypeEnum::CUSTOM   => $user->calculatedCustomRecipeByID($recipeInList->recipe_id)->first(),
            RecipeTypeEnum::FLEXMEAL => $list->flexMealWithIngredientsByID($recipeInList->recipe_id)->first(),
            default                  => throw new InvalidArgumentException(trans('shopping-list::messages.error.unknown_recipe_type'))
        };

        $this->prepareRecipeIngredients();

        // Drop recipe ingredients from list.
        try {
            $this->storeWhileDeletingRecipe($list, $recipeInList);
        } catch (Throwable $e) {
            logError($e);
            throw new PublicException(trans('shopping-list::messages.error.delete_recipe'));
        }

        ShoppingListProcessed::dispatch();
    }

    /**
     * Preparer recipe Ingredients.
     *
     * @throws InvalidArgumentException
     */
    private function prepareRecipeIngredients(): void
    {
        if ($this->recipe === null) {
            throw new InvalidArgumentException(trans('common.no_such_recipe'));
        }

        $this->ingredients = collect();
        if ($this->recipe instanceof FlexmealLists) {
            $this->recipe->ingredients->each(
                function (Flexmeal $ingredient): void {
                    $ingredient->amount *= $this->recipe->pivot->servings;
                    // For the flexmeal we must add ingredient category id
                    if (is_null($ingredient?->category_id)) {
                        $ingredient->category_id = Ingredient::whereId($ingredient->ingredient_id)->pluck('category_id')->first();
                    }
                }
            );
            $this->ingredients->push($this->recipe->ingredients->all());
            $this->ingredients = $this->ingredients->flatten();
        }

        // Recipes and custom recipes
        if (!empty($this->recipe->calc_recipe_data) && ($data = json_decode((string)$this->recipe->calc_recipe_data))) {
            $data           = $this->getCombinedCalculatedDuplicatedIngredients(collect($data->ingredients));
            $allIngredients = Ingredient::ofIds($data->pluck(['id'])->toArray())->get();

            $allIngredients->each(
                function (Ingredient $ingredient) use ($data): void {
                    $ingredient->amount = (int)$data->where('id', $ingredient->id)?->first()?->amount;
                }
            );
            $this->ingredients->push($allIngredients->all());
            $this->ingredients = $this->ingredients->flatten();
        }
    }

    /**
     * Add recipe to purchase list.
     *
     * @note params are snake cased due to they are extracted from request.
     *
     * @throws PublicException
     * @throws InvalidArgumentException
     */
    public function addRecipe(
        User   $user,
        int    $recipeId,
        int    $recipeType,
        string $mealDay,
        int    $mealTime,
        int    $portions = 1
    ): void {
        $list         = $user->shoppingList()->with(['ingredients', 'recipes'])->firstOrCreate();
        $this->recipe = match ($recipeType) {
            RecipeTypeEnum::ORIGINAL->value => $user->calculatedRecipeData($recipeId)->first(),
            RecipeTypeEnum::CUSTOM->value   => $user->calculatedCustomRecipeData($recipeId)->first(),
            RecipeTypeEnum::FLEXMEAL->value => $list->flexMealWithIngredientsByID($recipeId)->first() ??
                $user->plannedFlexmeals()->where('flexmeal_lists.id', $recipeId)->with('ingredients')->first(),
            default => throw new InvalidArgumentException(trans('shopping-list::messages.error.unknown_recipe_type'))
        };

        $this->prepareRecipeIngredients();

        try {
            $this->storeWhileAddingRecipe($list, $portions);
        } catch (Throwable $e) {
            logError($e);
            throw new PublicException(trans('shopping-list::messages.error.add_recipe'));
        }

        $servings = ShoppingListRecipe::whereListId($list->id)
            ->select('servings')
            ->where([
                ['recipe_id', $recipeId],
                ['recipe_type', $recipeType],
                ['mealtime', $mealTime],
                ['meal_day', $mealDay],
            ])
            ->first()?->servings ?? 0;

        ShoppingListRecipe::updateOrCreate(
            [
                'list_id'     => $list->id,
                'recipe_id'   => $recipeId,
                'recipe_type' => $recipeType,
                'mealtime'    => $mealTime,
                'meal_day'    => $mealDay
            ],
            [
                'servings' => $servings + $portions,
            ]
        );

        ShoppingListProcessed::dispatch();
    }

    /**
     * Change recipe servings in list.
     *
     * @throws ModelNotFoundException
     * @throws InvalidArgumentException
     * @throws PublicException
     */
    public function changeRecipeServings(
        User    $user,
        int     $recipeID,
        int     $servings,
        int     $recipeType,
        ?int    $mealTime = null,
        ?string $mealDay = null
    ): void {
        $list         = $user->shoppingList()->with('ingredients')->firstOrCreate();
        $recipeInList = ShoppingListRecipe::whereListId($list->id)
            ->whereRecipeId($recipeID)
            ->when($mealTime, fn($query, $mealTime) => $query->whereMealtime($mealTime))
            ->when($mealDay, fn($query, $mealDay) => $query->whereMealDay($mealDay))
            ->firstOrFail();

        // Prevent calculations for the same serving amount
        if ($servings === $recipeInList->servings) {
            throw new PublicException(trans('shopping-list::messages.error.same_amount'));
        }

        $this->recipe = match ($recipeType) {
            RecipeTypeEnum::ORIGINAL->value => $user->calculatedRecipeByID($recipeInList->recipe_id)->first(),
            RecipeTypeEnum::CUSTOM->value   => $user->calculatedCustomRecipeByID($recipeInList->recipe_id)->first(),
            RecipeTypeEnum::FLEXMEAL->value => $list->flexMealWithIngredientsByID($recipeInList->recipe_id)->first(),
            default                         => throw new InvalidArgumentException(trans('shopping-list::messages.error.unknown_recipe_type'))
        };

        $this->prepareRecipeIngredients();

        try {
            $this->storeWhileChangingServings($recipeInList, $servings, $list);
        } catch (Throwable $e) {
            logError($e);
            throw new PublicException(trans('shopping-list::messages.error.recipe_servings'));
        }

        ShoppingListProcessed::dispatch();
    }
}
