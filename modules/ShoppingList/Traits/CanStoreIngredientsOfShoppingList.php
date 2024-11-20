<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\FlexMeal\Models\Flexmeal;
use Modules\Ingredient\Models\Ingredient;
use Modules\ShoppingList\Models\{ShoppingListIngredient, ShoppingListRecipe};
use Modules\ShoppingList\Models\ShoppingList;

trait CanStoreIngredientsOfShoppingList
{
    /**
     * Collection of ingredients extracted from recipes.
     */
    protected ?Collection $ingredients = null;

    /**
     * @throws \Throwable
     */
    protected function storeWhileAddingRecipe(ShoppingList $list, int $totalPortionsToSet): void
    {
        DB::transaction(
            function () use ($list, $totalPortionsToSet) {
                $this->ingredients->each(
                    function (Ingredient|Flexmeal $ingredient) use ($list, $totalPortionsToSet) {
                        /**
                         * Usually $ingredient is instanceof \App\Models\Ingredient.
                         * In that case we set $ingredient_id-> $ingredient->id
                         *
                         * In case of flexmeals $ingredient is instanceof \App\Models\Flexmeal.
                         * In this case $ingredient->id is different, and we must use $ingredient->ingredient_id as $ingredient_id.
                         * We also must use get original value $recipe_ingredient->amount, otherwise values will be increased dramatically,
                         * due to amount attribute holds calculated values
                         */
                        $ingredientId     = $ingredient->id;
                        $ingredientAmount = $ingredient->amount * $totalPortionsToSet;

                        if ($ingredient instanceof Flexmeal) {
                            $ingredientId     = $ingredient->ingredient_id;
                            $ingredientAmount = $ingredient->getOriginal('amount') * $totalPortionsToSet;
                        }

                        // check ingredient in list
                        $ingredientInList = $list->ingredients->where('ingredient_id', $ingredientId)->first();

                        // don't exist -> create
                        if (is_null($ingredientInList)) {
                            ShoppingListIngredient::insert(
                                [
                                    'list_id'       => $list->id,
                                    'ingredient_id' => $ingredientId,
                                    'category_id'   => $ingredient->category_id,
                                    'amount'        => $ingredientAmount,
                                ]
                            );
                            return;
                        }

                        // update amount
                        $ingredientAmount += $ingredientInList->amount;
                        $ingredientInList->amount = (int)round($ingredientAmount);
                        $ingredientInList->save();
                    }
                );
            },
            config('database.transaction_attempts')
        );
    }

    /**
     * @throws \Throwable
     */
    protected function storeWhileDeletingRecipe(ShoppingList $list, ShoppingListRecipe $recipeInList): void
    {
        DB::transaction(
            function () use ($list, $recipeInList) {
                $this->ingredients->each(
                    function (Ingredient|Flexmeal $ingredient) use ($list, $recipeInList) {
                        /**
                         * Search over already loaded collection.
                         * Usually $ingredient is instanceof \App\Models\Ingredient.
                         * In that case we set $ingredient_id -> $ingredient->id.
                         * In case of flexmeals $ingredient is instanceof \App\Models\Flexmeal.
                         * In this case $ingredient->id is different thus we must use $ingredient->ingredient_id as $ingredient_id
                         */
                        $ingredientId                = $ingredient instanceof Ingredient ? $ingredient->id : $ingredient->ingredient_id;
                        $isNotCustomRecipeIngredient = isset($ingredient->amount);
                        $ingredientInList            = $list->ingredients
                            ->whereNotNull('category_id')
                            ->where('ingredient_id', $ingredientId)
                            ->first();

                        if (is_null($ingredientInList)) {
                            return;
                        }

                        // Process ingredient calculation according to structure of recipe
                        $ingredientInList->amount -= $isNotCustomRecipeIngredient ?
                            ($ingredient->amount * $recipeInList->servings) : // ordinary recipe and flexmeal
                            ($ingredient->pivot->amount * $recipeInList->servings); // custom recipes

                        if ($ingredientInList->amount > 0) {
                            $ingredientInList->amount = (int)round($ingredientInList->amount);
                            $ingredientInList->save();
                            return;
                        }

                        $ingredientInList->delete();
                    }
                );
                $recipeInList->delete();
            },
            config('database.transaction_attempts')
        );
    }

    /**
     * Change ingredients amount in list.
     * Due to many save requests can occur, transaction is probably a good solution here.
     *
     * Steps:
     * 1. we need to process the ingredients amount change
     * 2. we modify the amount of recipe servings inside a list.
     * @throws \Throwable
     */
    protected function storeWhileChangingServings(ShoppingListRecipe $recipeInList, int $servings, ShoppingList $list): void
    {
        DB::transaction(
            function () use (&$recipeInList, $servings, $list) {
                // need for calculating ingredients amount
                $clearServings = $servings - $recipeInList->servings;

                $this->ingredients->each(
                    function (Ingredient|Flexmeal $recipeIngredient) use ($list, $clearServings): void {
                        /**
                         * Usually $ingredient is instanceof \App\Models\Ingredient.
                         * In that case we set $ingredient_id-> $ingredient->id and $ingredientAmount is received as usual.
                         *
                         * In case of flexmeals $ingredient is instanceof \App\Models\Flexmeal.
                         * In this case $ingredient->id is different, and we must use $ingredient->ingredient_id as $ingredient_id.
                         * We also must use get original value $recipe_ingredient->amount, otherwise values will be increased dramatically,
                         * due to amount attribute holds calculated values
                         */
                        $ingredientId     = $recipeIngredient->id;
                        $ingredientAmount = $recipeIngredient->amount;
                        if ($recipeIngredient instanceof Flexmeal) {
                            $ingredientId     = $recipeIngredient->ingredient_id;
                            $ingredientAmount = $recipeIngredient->getOriginal('amount');
                        }

                        // Ingredient relations is loaded already
                        $listElement = $list->ingredients->where('ingredient_id', $ingredientId)->first();

                        // check element existence in list
                        if (is_null($listElement)) {
                            return;
                        }

                        /**
                         * 1. Calculate the amount of ingredients per clear servings (positive increase, negative subtract).
                         * 2. Add calculated value to amount in current list ingredient total amount.
                         * 3. Obtain the new total amount that should be saved for this recipe.
                         * 4. Ensure that the amount is integer and not negative as database field will not accept it.
                         * 5. Update the amount of ingredient in list.
                         */
                        $newAmount = $listElement->amount += $ingredientAmount * $clearServings;
                        $newAmount = (int)abs(round($newAmount));
                        $list->ingredientsWithPivot()->updateExistingPivot($ingredientId, ['amount' => $newAmount]);
                    }
                );
                $recipeInList->servings = $servings;
                $recipeInList->save();
            },
            config('database.transaction_attempts')
        );
    }
}
