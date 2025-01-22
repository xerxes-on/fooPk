<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Services;

use App\Models\{User};
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

    public function removeIngredient($user, int|string $ingredientId): ?array
    {
        $shoppingListIngredient = ShoppingListIngredient::find($ingredientId);

        if (empty($shoppingListIngredient)) {
            return null;
        }
        $shoppingList = $user->shoppingList;
        $deletedRecipes = [];
        //TODO: edited recipe's ingredients are not matching to displayed ones
        if ($shoppingListIngredient->delete()) {
            $recipes = collect($shoppingList->recipes)
                ->merge($shoppingList->customRecipes ?? [])
                ->merge($shoppingList->flexmeals ?? []);

            $recipes->each(function ($recipe) use ($shoppingList) {
                $remainingIngredients = $shoppingList->ingredients()
                    ->whereIn('ingredient_id', $recipe->ingredients->pluck('id'))
                    ->count();
                if ($remainingIngredients === 0) {
                    ShoppingListRecipe::where('recipe_id', $recipe->id)
                        ->where('list_id', $shoppingList->id)
                        ->delete();

                    $deletedRecipes[] = $recipe->pivot->id;
                }
            });
        }
        return $deletedRecipes;
    }
}
