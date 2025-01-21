<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Services;

use App\Enums\MealtimeEnum;
use App\Enums\Recipe\RecipeTypeEnum;
use App\Exceptions\PublicException;
use App\Models\{CustomRecipe, Recipe, User};
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Modules\FlexMeal\Models\FlexmealLists;
use Modules\Ingredient\Enums\IngredientCategoryEnum;
use Modules\Ingredient\Enums\IngredientTypeEnum;
use Modules\Ingredient\Models\Ingredient;
use Modules\ShoppingList\Events\ShoppingListProcessed;
use Modules\ShoppingList\Models\ShoppingListIngredient;
use Modules\ShoppingList\Traits\CanCombineIngredientsInShoppingList;

/**
 * Service that can generate users shopping list by provided time period.
 *
 * @package Modules\ShoppingList\Services
 */
final class ShoppingListGeneratorService
{
    use CanCombineIngredientsInShoppingList;

    /**
     * Collection of Recipes extracted from generated shopping list.
     * @var Collection<int, Recipe|FlexmealLists|CustomRecipe>|null
     */
    private ?Collection $recipes = null;

    /**
     * Fill purchase list by dates range.
     *
     * @throws PublicException
     */
    public function generate(User $user, string $dateStart, string $dateEnd): void
    {
        $customIngredients = $user->shoppingList()
            ->with(
                'ingredients',
                fn(HasMany $e) => $e->whereNotNull('custom_title')
            )
            ->first()
            ?->ingredients;

        $user->shoppingList()->delete();
        $this->ingredients = collect();

        // Collect and validate recipes data, map recipes collection to form correct data and extract ingredients.
        $this->collectRecipes($user, $dateStart, $dateEnd)
            ->alterDataStructure()
            ->combineCalculatedDuplicatedIngredients();

        $list = $user->shoppingList()->create();

        // Prepare ingredients
        $this->prepareIngredientsForStoring($list->id);
        $this->addCustomIngredients($list->id, $customIngredients);

        // Save recipes & ingredients
        $list->recipes()->attach($this->recipes->toArray());
        $list->ingredients()->createManyQuietly($this->ingredients->toArray());

        ShoppingListProcessed::dispatch();
    }

    /**
     * Alter Recipe data structure to meet the same structure.
     * During iteration allow to extract ingredients from recipes.
     */
    private function alterDataStructure(): ShoppingListGeneratorService
    {
        $this->recipes = $this->recipes?->map(
            function (Recipe|FlexmealLists|CustomRecipe $recipe) {
                if ($recipe instanceof Recipe) {
                    $this->extractIngredientsFromRecipe($recipe);
                    return [
                        'recipe_id'   => $recipe->id,
                        'recipe_type' => RecipeTypeEnum::ORIGINAL,
                        'servings'    => 1,
                        'mealtime'    => MealtimeEnum::tryFromValue($recipe->pivot->meal_time),
                        'meal_day'    => $recipe->pivot->meal_date,
                    ];
                }

                if ($recipe instanceof CustomRecipe) {
                    $this->extractIngredientsFromCustomRecipe($recipe);
                    return [
                        'recipe_id'   => $recipe->custom_recipe_id,
                        'recipe_type' => RecipeTypeEnum::CUSTOM,
                        'servings'    => 1,
                        'mealtime'    => MealtimeEnum::tryFromValue($recipe->pivot->meal_time),
                        'meal_day'    => $recipe->meal_date,
                    ];
                }

                if ($recipe instanceof FlexmealLists) {
                    $this->extractIngredientsFromFlexmeal($recipe);
                    return [
                        'recipe_id'   => $recipe->pivot->flexmeal_id,
                        'recipe_type' => RecipeTypeEnum::FLEXMEAL,
                        'servings'    => 1,
                        'mealtime'    => MealtimeEnum::tryFromValue($recipe->pivot->meal_time),
                        'meal_day'    => $recipe->pivot->meal_date,
                    ];
                }
            }
        );

        return $this;
    }

    /**
     * Extract ingredients from ordinary recipe type.
     */
    private function extractIngredientsFromRecipe(Recipe $recipe): void
    {
        /**
         * In case of calculations error calc_recipe_data may lack required data.
         * So we validate it.
         */
        $ingredientData = is_string($recipe?->calc_recipe_data) ? json_decode($recipe?->calc_recipe_data) : null;
        if (!is_null($ingredientData?->ingredients)) {
            $this->ingredients->push($ingredientData->ingredients);
        }
    }

    /**
     * Extract ingredients from custom recipe type.
     */
    private function extractIngredientsFromCustomRecipe(CustomRecipe $recipe): void
    {
        /**
         * In case of calculations error calc_recipe_data may lack required data.
         * So we validate it.
         */
        $ingredientData = $recipe?->calc_recipe_data;
        $ingredientData = is_string($ingredientData) ? json_decode($ingredientData, false) : null;
        if (!is_null($ingredientData?->ingredients)) {
            $this->ingredients->push($ingredientData->ingredients);
        }
    }

    /**
     * Extract ingredients from flexmeal recipe type.
     */
    private function extractIngredientsFromFlexmeal(FlexmealLists $recipe): void
    {
        $this->ingredients->push(
            $recipe
                ->ingredients
                ->map(
                    fn($item) => (object)[
                        'id'     => $item->ingredient_id,
                        'type'   => IngredientTypeEnum::FIXED->value,
                        'amount' => $item->amount,
                    ],
                )
                ->ToArray()
        );
    }

    /**
     * Collect planned recipes for provided dates.
     *
     * @throws PublicException
     */
    private function collectRecipes(User $user, string $dateStart, string $dateEnd): ShoppingListGeneratorService
    {
        $parsedStartDate = parseDateString($dateStart);
        $parsedEndDate   = parseDateString($dateEnd);
        /**
         * get all user recipes (Ordinary, Custom, Flexmeals).
         * In order to grab all available data we use manual collection as merge can delete similar objects there.
         */
        $this->recipes = collect(
            [
                $user->calculatedRecipesForDatePeriod($parsedStartDate, $parsedEndDate)->get(),
                $user->calculatedCustomRecipesForDatePeriod($parsedStartDate, $parsedEndDate)->get(),
                $user->plannedFlexmealsForDatePeriod($parsedStartDate, $parsedEndDate)->get(),
            ]
        )
            ->flatten();

        if ($this->recipes->isEmpty()) {
            throw new PublicException(trans('shopping-list::messages.error.no_recipes'));
        }

        return $this;
    }

    /**
     * Save ingredients to purchase list.
     */
    private function prepareIngredientsForStoring(int $listId): void
    {
        /**
         * To avoid querying inside the loop we need to collect ingredients data,
         * category in particular, used inside provided collection.
         */
        $ingredientsWithCategory = Ingredient::ofIds($this->ingredients->pluck('id')->unique()->toArray())
            ->withOnly([
                'category' => fn(HasOne $query) => $query
                    ->withOnly([])
                    ->select('id', 'tree_information')
            ])
            ->get(['id', 'category_id'])
            ->keyBy('id');
        $this->ingredients = $this->ingredients
            ->map(
                function (\stdClass $ingredient) use ($ingredientsWithCategory, $listId) {
                    // We must check if ingredient in Collection really exist in DB and not removed.
                    $ingredientModel = $ingredientsWithCategory->get($ingredient->id);
                    if (!$ingredientModel) {
                        return [];
                    }

                    // Access category relationship
                    $categoryModel = $ingredientModel->category;
                    $categoryId = $categoryModel?->tree_information['mid_category'] ?? $categoryModel?->id;

                    return [
                        'list_id'       => $listId,
                        'ingredient_id' => $ingredient->id,
                        'category_id' => $categoryId ?? IngredientCategoryEnum::SPICES->value,
                        'amount'        => $ingredient->amount,
                    ];
                }
            )
            ->filter(fn(array $item) => !empty($item));
    }

    private function addCustomIngredients(int $listId, ?EloquentCollection $ingredients): void
    {
        if ($ingredients === null || $ingredients->isEmpty()) {
            return;
        }

        $ingredients->each(
            function (ShoppingListIngredient $ingredient) use ($listId) {
                $this->ingredients->push(
                    [
                        'list_id'       => $listId,
                        'ingredient_id' => null,
                        'category_id'   => null,
                        'custom_title'  => $ingredient->custom_title,
                        'amount'        => $ingredient->amount,
                        'completed'     => $ingredient->completed,
                    ]
                );
            }
        );
    }
}
