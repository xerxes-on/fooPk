<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\RecipeProcessed;
use App\Exceptions\PublicException;
use App\Models\CustomRecipe;
use App\Models\Ingestion;
use App\Models\Recipe;
use App\Models\User;
use App\Models\UserRecipe;
use App\Models\UserRecipeCalculated;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Calculation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Ingredient\Models\Ingredient;
use Modules\Ingredient\Models\IngredientCategory;

/**
 * Recipe service class
 *
 * @package App\Services
 */
class RecipeService
{
    /**
     * Update or create custom recipe from a common one.
     *
     * TODO: unoptimized code
     * TODO: takes a lot of insert
     * @throws PublicException
     */
    final public function createCustomRecipe(
        User      $user,
        Carbon    $date,
        Ingestion $ingestion,
        Recipe    $common_recipe,
        array     $fixed_ingredients,
        array     $variable_ingredients,
    ): array|object {
        // TODO: what is going to be saved in the end and what does matter!?
        $ingredients                 = [];
        $processed_fixed_ingredients = [];
        $meal                        = $user->meals()
//					 ->where('challenge_id', $user->subscription?->id)
            ->whereDate('meal_date', $date)
            ->where('ingestion_id', $ingestion->id)
            ->first();
        if (is_null($meal)) {
            $formattedDate = $date->format('Y-m-d');
            throw new PublicException("There's no $ingestion->key on $formattedDate.");
        }
        /**
         * @note: replace_by Supposed to be one as there should not be 2 items to replace.
         */
        foreach ($fixed_ingredients as $ingredient) {
            if (isset($ingredient['replace_by'])) {
                $ingredientsData = Ingredient::withOnly(
                    ['unit' => fn(HasOne $relation) => $relation->setEagerLoads([])->select('id', 'default_amount')]
                )
                    ->whereIn('ingredients.id', [$ingredient['ingredient_id'], $ingredient['replace_by']])
                    ->get();
                $current    = $ingredientsData->find($ingredient['ingredient_id']);
                $replace_by = $ingredientsData->find($ingredient['replace_by']);
                $category   = IngredientCategory::MAIN_CATEGORIES[$ingredient['ingredient_category_id']];

                $nutrients = $category['full'] === 'carbohydrates' ? $category['full'] : $category['full'] . 's';

                // calculate current ingredient nutrients by ingredient category
                $current_val = $current->{$nutrients} * (float)$ingredient['amount'] / $current->unit->default_amount;

                // calculate ingredient_to_replace amount
                $replace_amount = empty($replace_by->{$nutrients}) ?
                    0 :
                    $current_val * $current->unit->default_amount / $replace_by->{$nutrients};

                $processed_fixed_ingredients[] = [
                    'id'     => $ingredient['replace_by'],
                    'amount' => round($replace_amount)
                ];
                $ingredients[] = [
                    'ingredient_id'          => $ingredient['replace_by'],
                    'ingredient_category_id' => $ingredient['ingredient_category_id'],
                    'amount'                 => round($replace_amount),
                    'type'                   => 'fixed'
                ];
                continue;
            }

            $processed_fixed_ingredients[] = [
                'id'     => $ingredient['ingredient_id'],
                'amount' => $ingredient['amount'],
            ];

            $ingredients[] = [
                'ingredient_id'          => $ingredient['ingredient_id'],
                'ingredient_category_id' => $ingredient['ingredient_category_id'],
                'amount'                 => $ingredient['amount'],
                'type'                   => 'fixed'
            ];
        }

        $custom_recipe      = $meal->customRecipe;
        $recipe_information = (object)[
            'id'                      => is_null($custom_recipe) ? null : $custom_recipe->id,
            'recipe_id'               => $common_recipe->id,
            'purchase_list_recipe_id' => is_null($custom_recipe) ? $common_recipe->id : $custom_recipe->id,
            'title'                   => $common_recipe->title,
            'ingestion'               => $ingestion,
            'ingredients'             => $ingredients,
            'fixed_ingredients'       => $processed_fixed_ingredients,
            'variable_ingredients'    => $variable_ingredients,
            'date'                    => $date,
        ];
        $customRecipe = $this->saveCustomRecipe(
            $user,
            $recipe_information,
            true,
        );

        if (empty($customRecipe->recipe)) {
            //            \Log::error('Creation of custom recipe has failed.', [
            //                'recipe_information' => $recipe_information,
            //                'recipe'             => $customRecipe,
            //            ]);
            throw new PublicException('Creation of custom recipe has failed.');
        }

        // replace recipe in meal plan
        $meal->recipe_id        = null;
        $meal->custom_recipe_id = $customRecipe->recipe->id;
        $meal->save();

        // TODO:: review custom recipe relation to allRecipes scope

        # create calculated from custom recipe
        UserRecipeCalculated::updateOrCreate(
            [
                'user_id'          => $user->id,
                'custom_recipe_id' => $customRecipe->recipe->id,
                'ingestion_id'     => $ingestion->id,
            ],
            [
                'recipe_data' => $customRecipe->calculated_recipe,
                'invalid'     => 0,
            ]
        )->touch();

        $customRecipeId = $customRecipe->recipe->id;

        // part where calculates possible ingestions for custom recipe based on original one
        $ingestions = $common_recipe->ingestions()->where('ingestions.active', 1)->whereNot('ingestions.id', $ingestion->id)->get();

        foreach ($ingestions as $ingestion) {
            $stepRecipeInformation = (object)[
                'id'                      => is_null($custom_recipe) ? null : $custom_recipe->id,
                'recipe_id'               => $common_recipe->id,
                'purchase_list_recipe_id' => is_null($custom_recipe) ? $common_recipe->id : $custom_recipe->id,
                'title'                   => $common_recipe->title,
                'ingestion'               => $ingestion,
                'ingredients'             => $ingredients,
                'fixed_ingredients'       => $processed_fixed_ingredients,
                'variable_ingredients'    => $variable_ingredients,
                'date'                    => $date,
            ];
            $stepCustomRecipe = $this->saveCustomRecipe(
                $user,
                $stepRecipeInformation,
                true,
                false
            );

            if (!empty($stepCustomRecipe->calculated_recipe)) {
                UserRecipeCalculated::updateOrCreate(
                    [
                        'user_id'          => $user->id,
                        'custom_recipe_id' => $customRecipeId,
                        'ingestion_id'     => $ingestion->id,
                    ],
                    [
                        'recipe_data' => $stepCustomRecipe->calculated_recipe,
                        'invalid'     => 0,
                    ]
                )->touch();
            }

        }

        RecipeProcessed::dispatch();

        return $customRecipe;
    }

    /**
     * Save custom recipe.
     * TODO: slowly refactor to service
     * TODO: too many possible data types return. need to return one type and trow errors instead
     */
    private function saveCustomRecipe(
        User   $user,
        object $recipe_information,
        bool   $show_errors = false,
        bool   $saveIntoDatabase = true
    ): bool|object|array {
        // get ingredients only ID
        $ingredient_ids = [];

        if (!empty($recipe_information->variable_ingredients)) {
            foreach ($recipe_information->variable_ingredients as $ingredient) {
                if ($ingredient['ingredient_id'] !== '0') {
                    $ingredient_ids[] = $ingredient['ingredient_id'];
                }
            }
        }

        // get ingredients
        $variable_ingredients = Ingredient::whereIn('id', $ingredient_ids)
            ->get()
            ->toArray();

        // calculate recipe
        $calculated_recipe = Calculation::getUserRecipe(
            $user->id,
            null,
            $recipe_information->fixed_ingredients, // fixed_ingredients
            $variable_ingredients, // variable_ingredients
            $recipe_information->ingestion->key,
            //            false // todo: Maybe set to true?
        );

        // return false if recipe has errors
        if (!empty($calculated_recipe['errors'])) {
            return $show_errors ? $calculated_recipe : false;
        }

        $recipeExistInMealPlan = $user->meals()
            ->where('custom_recipe_id', $recipe_information->id)
            ->where('meal_date', '!=', $recipe_information->date)
            ->count();

        # recipeData optimization
        $calculated_recipe = Calculation::calcRecipeOptimization(null, $calculated_recipe);
        $recipe            = null;
        if ($saveIntoDatabase && $recipeExistInMealPlan) {
            $recipe = CustomRecipe::create(
                [
                    'user_id'      => $user->id,
                    'recipe_id'    => $recipe_information->recipe_id,
                    'title'        => $recipe_information->title,
                    'ingestion_id' => $recipe_information->ingestion->id,
                    'error'        => $calculated_recipe['errors'],
                ]
            )->saveRecipeIngredients($recipe_information->ingredients);
        } elseif ($saveIntoDatabase) {
            // create or update recipe
            $recipe = CustomRecipe::updateOrCreate(
                [
                    'id' => $recipe_information->id,
                ],
                [
                    'user_id'      => $user->id,
                    'recipe_id'    => $recipe_information->recipe_id,
                    'title'        => $recipe_information->title,
                    'ingestion_id' => $recipe_information->ingestion->id,
                    'error'        => $calculated_recipe['errors'],
                ]
            )->saveRecipeIngredients($recipe_information->ingredients);
        }
        RecipeProcessed::dispatch();

        return (object)[
            'recipe'            => $recipe,
            'calculated_recipe' => $calculated_recipe,
        ];
    }

    /**
     * Replace custom recipe with its original version.
     */
    final public function restore(UserRecipe $recipe, int $originalRecipeID): void
    {
        $recipe->recipe_id        = $originalRecipeID;
        $recipe->custom_recipe_id = null;
        $recipe->save();

        RecipeProcessed::dispatch();
    }

    /**
     * Replace existing recipe of a planned meal with a new one.
     *
     * @throws PublicException
     */
    final public function replaceRecipe(
        User      $user,
        Recipe    $newRecipe,
        Carbon    $date,
        Ingestion $ingestion,
    ): void {
        $calculations = $newRecipe
            ->calculations()
            ->where(
                [
                    ['user_id', $user->id],
                    ['ingestion_id', $ingestion->id],
                    ['invalid', 0]
                ]
            )
            ->first();
        if (is_null($calculations) || empty($calculations->recipe_data)) {
            throw new PublicException(trans('common.no_calculations_error'));
        }

        // Forbid to replace with excluded recipes.
        if (!is_null($user->excludedRecipes()->where('recipe_id', $newRecipe->id)->first())) {
            throw new PublicException(trans('common.replace_by_hidden_error'));
        }

        /**
         * Replace all recipes for provided ingestion and date despite challenge_id.
         * Bear in mind that we can have more than one recipe but with different challenge_id.
         */
        $meals = $user
            ->meals()
            ->where('ingestion_id', $ingestion->id)
            ->whereDate('meal_date', $date)
            ->get();

        if ($meals->isEmpty()) {
            throw new PublicException(
                trans('common.no_meal_time_error', ['ingestion' => $ingestion->key, 'date' => $date->format('Y-m-d')])
            );
        }

        $meals->each(
            function (UserRecipe $meal) use ($newRecipe) {
                // replace in a planned meal
                $meal->recipe_id        = $newRecipe->id;
                $meal->custom_recipe_id = null;
                $meal->flexmeal_id      = null;
                $meal->save();
            }
        );

        $user->allRecipes()->syncWithoutDetaching(['recipe_id' => $newRecipe->id]);
        RecipeProcessed::dispatch();
    }

    /**
     *  Replace existing recipe of a planned meal with a new custom one.
     *
     * @throws PublicException
     */
    final public function replaceWithCustom(
        User         $user,
        CustomRecipe $newRecipe,
        Carbon       $date,
        Ingestion    $ingestion,
    ): void {
        $meal = $user
            ->meals()
            ->where('ingestion_id', $ingestion->id)
            ->whereDate('meal_date', $date)
            ->first();

        if (is_null($meal)) {
            $day = $date->format('Y-m-d');
            throw new PublicException("There's no planned $ingestion->key on $day.");
        }

        // replace in a planned meal
        $meal->recipe_id        = null;
        $meal->custom_recipe_id = $newRecipe->id;
        $meal->flexmeal_id      = null;
        $meal->save();

        $user->allRecipes()->syncWithoutDetaching(['recipe_id' => $newRecipe->recipe_id]);
        RecipeProcessed::dispatch();
    }

    /**
     * Buy a recipe.
     *
     * @throws PublicException
     */
    final public function buy(User $buyer, Recipe $recipe): string
    {
        // checks
        if (is_null(config('adding-new-recipes.purchase_url'))) {
            throw new PublicException('Purchase URL is not configured');
        }

        if ($buyer->allRecipes()->where('recipe_id', $recipe->id)->exists()) {
            throw new PublicException(trans('common.recipe_already_bought'));
        }

        if (!$buyer->canWithdraw(config('foodpunk.new_recipe_price'))) {
            throw new PublicException(trans('common.insufficient_funds_for_recipe'));
        }

        $validRecipes = $buyer->preliminaryCalc()->first(['valid'])?->valid ?? [];

        if (!in_array($recipe->id, $validRecipes)) {
            throw new PublicException(trans('common.invalid_recipe'));
        }

        $result = Calculation::_calcRecipe2user($buyer, [$recipe->id], true);
        // TODO: need to clarify why recipe is unavailable for user
        if ($result['success']) {
            try {
                $buyer->withdraw(10, ['description' => "Purchase of Recipe #$recipe->id"]);
            } catch (ExceptionInterface $e) {
                throw new PublicException($e->getMessage());
            }

            return $result['message'];
        }

        throw new PublicException(trans('common.unavailable_recipe_plan'));
    }
}
