<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Enums\Recipe\RecipeTypeEnum;
use App\Exceptions\{AlreadyHidden, NoData, PublicException};
use App\Helpers\Calculation;
use App\Http\Requests\API\AllRecipesFilters;
use App\Http\Requests\API\Meal\PlannedMealFilterRequest;
use App\Http\Requests\API\Recipe\{IngredientReplacementRequest};
use App\Http\Resources\{Complexity,
    CustomRecipeCategory,
    Diet as DietResource,
    IngestionResource,
    Price,
    Recipe\RecipePreviewForAllResource,
    Recipe\RecipeTagResource,
    Recipe\RecipeToBuyResource,
    Recipe\UsersRecipeResource,
    Season
};
use App\Models\{Diet, Ingestion as IngestionModel, Recipe as RecipeModel, RecipeComplexity, RecipePrice, RecipeTag};
use App\Repositories\{Recipes as RecipeRepo, SeasonsRepository};
use App\Services\{RecipeService};
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\{JsonResponse, Request};
use Modules\ShoppingList\Services\ShoppingListRecipesService;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

/**
 * API controller for recipes.
 *
 * TODO: The class has a coupling between objects value of 36.
 * TODO: The class has an overall complexity of 60. Consider refactoring.
 * @package App\Http\Controllers\API
 */
final class RecipesApiController extends APIBase
{
    /**
     * Recipes constructor.
     */
    public function __construct(private readonly RecipeRepo $recipesRepo)
    {
    }

    /**
     * Get a single users` recipe.
     *
     * @route GET /api/v1/recipe/{id}
     */
    public function get(Request $request, int $id): JsonResponse
    {
        $user   = $request->user();
        $recipe = $user->recipeWithCalculations($id)->orderBy('calc_invalid', 'ASC')->first();

        if (is_null($recipe)) {
            return $this->sendError(message: trans('common.no_such_recipe'));
        }
        $recipe->custom_categories = $this->recipesRepo->getRecipeCustomCategories($recipe, $user);
        return $this->sendResponse(
            [
                'user_available_ingestions' => IngestionResource::collection($user->allowed_ingestions),
                'recipe'                    => new UsersRecipeResource($recipe),
                'ingredients'               => Calculation::parseRecipeData($recipe, $user->lang),
            ],
            trans('common.success')
        );
    }

    /**
     * Get users recipes.
     *
     * @route GET /api/v1/all-recipes
     */
    public function getAllRecipes(AllRecipesFilters $request): JsonResponse
    {
        $filters = $request->only(
            [
                'search_name',
                'ingestion',
                'cost',
                'complexity',
                'diet',
                'seasons',
                'invalid',
                'favorite',
                'replacement_ingestion',
                'custom_category',
                'excluded',
                'recipe_tag',
            ]
        );
        $perPage = (int)$request->input('per_page', 20);
        $recipes = $this->recipesRepo->getAll($request->user(), $perPage, $filters);
        return $this->sendResponse(RecipePreviewForAllResource::collection($recipes), trans('common.success'));
    }

    /**
     * Get options for filtering recipes.
     *
     * @route GET /api/v1/recipes-filter-options
     */
    public function getFilterOptions(Request $request, SeasonsRepository $seasonsRepo): JsonResponse
    {
        $user    = $request->user();
        $options = [
            'ingestions'        => IngestionResource::collection(IngestionModel::active()->get()),
            'complexities'      => Complexity::collection(RecipeComplexity::getAll()),
            'costs'             => Price::collection(RecipePrice::getAll()),
            'diets'             => DietResource::collection(Diet::getAll()),
            'seasons'           => Season::collection($seasonsRepo->getRelevant($user)),
            'custom_categories' => CustomRecipeCategory::collection($user->customRecipeCategories),
            'recipe_tags'       => RecipeTagResource::collection(RecipeTag::publicOnly()->with('translations')->get()),
        ];

        return $this->sendResponse($options, trans('common.success'));
    }

    /**
     * Mark a recipe as favourite.
     *
     * @route POST /api/v1/favourite-recipe/{recipe}
     */
    public function favourite(Request $request, RecipeModel $recipe): JsonResponse
    {
        // TODO: translation required
        return $this->sendResponse(
            [],
            $request->user()->setFavourite($recipe) ?
                'The recipe added to favourites.' :
                'The recipe is already set as favourite.'
        );
    }

    /**
     * Remove favourite mark from a recipe.
     *
     * @route POST /api/v1/unfavourite-recipe/{recipe}
     */
    public function unfavourite(Request $request, RecipeModel $recipe): JsonResponse
    {
        // TODO: translation required
        return $this->sendResponse(
            [],
            $request->user()->unsetFavourite($recipe) ?
                'The recipe is removed from favourites.' :
                'The recipe is already removed from favourites.'
        );
    }

    /**
     * Replace ingredient.
     *
     * TODO: The method is highly unoptimized. Refactor recommended
     * TODO: takes too much time to perform
     * TODO: >320 duplicated requests and > 12mb memory
     * TODO: The method has a Cyclomatic Complexity of 22. Simplify the code
     * TODO: The method has an NPath complexity of 16896. Simplify the code!
     *
     * @route POST /api/v1/replace-ingredient
     */
    public function replaceIngredient(
        IngredientReplacementRequest $request,
        ShoppingListRecipesService   $shoppingListService,
        RecipeService                $recipeService
    ): JsonResponse {
        $user = $request->user();
        // preparing arguments for createCustomRecipe() in format it understands.
        try {
            $meal = $user
                ->meals()
                ->whereDate('meal_date', $request->date)
                ->where('ingestion_id', $request->ingestion->id)
                ->firstOrFail();
        } catch (ModelNotFoundException) {
            return $this->sendError(trans('common.no_such_recipe'));
        }
        $recipe = $meal->original_recipe;

        // Can be null occasionally
        if (is_null($recipe)) {
            return $this->sendError(trans('common.no_such_recipe'));
        }
        $calculatedData = $meal->recipeCalculations()->first('recipe_data');

        if (is_null($calculatedData)) {
            return $this->sendError(trans('common.no_calculation'));
        }
        $ingredients      = Calculation::parseRecipeData((object)['calc_recipe_data' => $calculatedData->getRawOriginal('recipe_data')], $user->lang);
        $fixedIngredients = [];
        $varIngredients   = [];

        foreach ($ingredients as $ingredient) {
            if ($ingredient['ingredient_id'] === $request->old_ingredient && !$ingredient['allow_replacement']) {
                return $this->sendError(trans('api.ingredient_replacement_error'));
            }

            /**
             * Amount is introduced here to distinguish duplicated recipes.
             * In occasions when recipe has duplicated recipes we must understand which particular ingredient we are replacing.
             * @note: If amount is not passed all duplicated ingredients will be replaced!
             */
            $comparedAmount = is_null($request->amount) || $request->amount === (int)$ingredient['ingredient_amount'];

            if ($ingredient['ingredient_type'] === 'fixed') {
                $fixedIngredient = [
                    'ingredient_id'          => $ingredient['ingredient_id'],
                    'ingredient_category_id' => $ingredient['main_category'],
                    'amount'                 => (int)$ingredient['ingredient_amount'],
                ];

                if (($request->old_ingredient === $ingredient['ingredient_id']) && $comparedAmount) {
                    $fixedIngredient['replace_by'] = $request->new_ingredient;
                }

                $fixedIngredients[] = $fixedIngredient;
            } elseif ($ingredient['ingredient_type'] === 'variable') {
                $varIngredient = [
                    'ingredient_id'          => $ingredient['ingredient_id'],
                    'ingredient_category_id' => $ingredient['main_category'],
                ];

                if (($request->old_ingredient === $ingredient['ingredient_id']) && $comparedAmount) {
                    $varIngredient['ingredient_id'] = $request->new_ingredient;
                }

                $varIngredients[] = $varIngredient;
            } else {
                return $this->sendError(trans('api.ingredient_type_error'));
            }
        }

        $existedInShoppingList = false;
        try {
            $type     = RecipeTypeEnum::ORIGINAL;
            $recipeId = $meal->recipe_id;
            if ($meal->custom_recipe_id > 0) {
                $type     = RecipeTypeEnum::CUSTOM;
                $recipeId = $meal->custom_recipe_id;
            }
            if ($meal->flexmeal_id > 0) {
                $type     = RecipeTypeEnum::FLEXMEAL;
                $recipeId = $meal->flexmeal_id;
            }
            $shoppingListService->deleteRecipe(
                $user,
                $recipeId,
                $type->value,
                $request->date->format('Y-m-d'),
                $request->ingestionIntValue
            );
            $existedInShoppingList = true;
        } catch (ModelNotFoundException|PublicException) {
            // TODO: maybe show some info later?
        } catch (\InvalidArgumentException $e) {
            logError($e);
        }

        try {
            $result = $recipeService->createCustomRecipe(
                $user,
                $request->date,
                $request->ingestion,
                $recipe,
                $fixedIngredients,
                $varIngredients,
            );
        } catch (PublicException $exception) {
            return $this->sendError(message: $exception->getMessage());
        }

        if (is_array($result)) {
            return $this->sendError($result);
        }

        // Attempt to add recipe to list if previously it existed
        if (!$existedInShoppingList) {
            return $this->sendResponse(
                [
                    'id'        => $result->recipe->id,
                    'date'      => $request->date->format('Y-m-d'),
                    'ingestion' => $request->ingestion->key,
                ],
                trans('common.recipe_replaced')
            );
        }

        try {
            $shoppingListService->addRecipe(
                $user,
                $result->recipe->id,
                RecipeTypeEnum::CUSTOM->value,
                $request->date->format('Y-m-d'),
                $request->ingestionIntValue
            );
        } catch (PublicException|\InvalidArgumentException) {
            // TODO: maybe show some info later?
        }

        return $this->sendResponse(
            [
                'id'        => $result->recipe->id,
                'date'      => $request->date->format('Y-m-d'),
                'ingestion' => $request->ingestion->key,
            ],
            trans('common.recipe_replaced')
        );
    }

    /**
     * Replace custom recipe with its original version.
     *
     * @route POST /api/v1/restore-recipe
     */
    public function restore(
        PlannedMealFilterRequest   $request,
        ShoppingListRecipesService $shoppingListService,
        RecipeService              $recipeService
    ): JsonResponse {
        // Gather required data
        $user = $request->user();

        try {
            $meal = $user
                ->meals()
                ->whereDate('meal_date', $request->date)
                ->with(['customRecipe' => fn(HasOne $relation) => $relation->select(['id', 'recipe_id'])])
                ->where('ingestion_id', $request->ingestion->id)
                ->firstOrFail();
        } catch (ModelNotFoundException) {
            return $this->sendError(trans('common.recipe_dont_exist'));
        }

        $existedInShoppingList = false;
        $type                  = RecipeTypeEnum::ORIGINAL;
        $recipeId              = $meal->recipe_id;
        if ($meal->custom_recipe_id > 0) {
            $type     = RecipeTypeEnum::CUSTOM;
            $recipeId = $meal->custom_recipe_id;
        }
        if ($meal->flexmeal_id > 0) {
            $type     = RecipeTypeEnum::FLEXMEAL;
            $recipeId = $meal->flexmeal_id;
        }

        // Attempt to delete old(assigned to specific date) recipe from shopping list
        try {
            $shoppingListService->deleteRecipe(
                $user,
                $recipeId,
                $type->value,
                $request->date->format('Y-m-d'),
                $request->ingestionIntValue
            );
            $existedInShoppingList = true;
        } catch (ModelNotFoundException|PublicException) {
            // TODO: maybe show some info later?
        } catch (\InvalidArgumentException $e) {
            logError($e);
        }

        $originalRecipeId = $meal->customRecipe?->recipe_id ?? $meal->original_recipe_id;

        // Restore custom to original recipe
        $recipeService->restore($meal, $originalRecipeId);

        // Attempt to add recipe to list if previously it existed
        if (!$existedInShoppingList) {
            return $this->sendResponse(
                [
                    'date'      => date('Y-m-d', strtotime((string)$meal->meal_date)),
                    'ingestion' => $meal->meal_time
                ],
                trans('common.recipe_restored')
            );
        }

        try {
            $shoppingListService->addRecipe(
                $user,
                $originalRecipeId,
                RecipeTypeEnum::ORIGINAL->value,
                $request->date->format('Y-m-d'),
                $request->ingestionIntValue
            );
        } catch (PublicException) {
            // TODO: maybe show some info later?
        }

        return $this->sendResponse(
            [
                'date'      => date('Y-m-d', strtotime((string)$meal->meal_date)),
                'ingestion' => $meal->meal_time
            ],
            trans('common.recipe_restored')
        );
    }

    /**
     * Remove recipe from Meal plan and replace with random one.
     *
     * @route POST /api/v1/recipe/{id}/exclude
     */
    public function exclude(Request $request, int $recipeId, ShoppingListRecipesService $service): JsonResponse
    {
        $user = $request->user();
        try {
            $recipe = RecipeModel::whereId($recipeId)->firstOrFail();
            $this->recipesRepo->excludeRecipe($user, $recipe);
        } catch (ModelNotFoundException) {
            return $this->sendError(message: trans('common.no_such_recipe'));
        } catch (AlreadyHidden|PublicException $e) {
            return $this->sendError(message: $e->getMessage(), status: ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable $e) {
            logError($e, ['user' => $user->id, 'recipe_id' => $recipeId]);
            return $this->sendError(message: $e->getMessage(), status: ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Attempt to delete old recipe from shopping list
        try {
            $service->deleteRecipe($user, $recipeId, RecipeTypeEnum::ORIGINAL->value);
        } catch (ModelNotFoundException|PublicException) {
            // TODO: maybe show some info later?
        } catch (\InvalidArgumentException $e) {
            logError($e);
        }

        return $this->sendResponse(trans('api.exclude_success_response'), trans('common.success'));
    }

    /**
     * Remove recipe from excluded list.
     *
     * @route DELETE /api/v1/recipe/{id}/restore
     */
    public function removeFromExcluded(Request $request, int $recipeId): JsonResponse
    {
        try {
            $this->recipesRepo->removeRecipeFromExcluded($request->user(), $recipeId);
            return $this->sendResponse(trans('api.remove_from_excluded_success_response'), trans('common.success'));
        } catch (NoData $e) {
            return $this->sendError(message: $e->getMessage(), status: ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Generate a recipes to buy list.
     *
     * @route GET /api/v1/recipes-to-buy
     *
     * TODO: Need to optimize
     * Duration    19566 ms
     * Memory usage    122 MB (for 20recipes)
     * 71 queries, 39 of which are duplicated.
     */
    public function getRecipesToBuy(AllRecipesFilters $request): JsonResponse
    {
        $conditions = $request->only(
            [
                'search_name',
                'ingestion',
                'complexity',
                'cost',
                'diet',
                'seasons',
                'favorite',
                'recipe_tag'
            ]
        );
        $recipes = $this->recipesRepo->getRecipesToBuy($request->user(), $conditions, (int)$request->input('per_page', 20));
        // This step prevent call action methods in case null is passed
        $recipes = $recipes === null ? [] : RecipeToBuyResource::collection($recipes);

        return $this->sendResponse($recipes, trans('common.success'));
    }

    /**
     * Buy recipe.
     *
     * @route POST /api/v1/buy-recipe/{recipe_id}
     */
    public function buy(Request $request, int $recipe_id, RecipeService $service): JsonResponse
    {
        try {
            $recipe         = RecipeModel::whereId($recipe_id)->firstOrFail();
            $successMessage = $service->buy($request->user(), $recipe);
            return $this->sendResponse([], $successMessage);
        } catch (PublicException $e) {
            return $this->sendError(message: $e->getMessage());
        } catch (Throwable $e) {
            logError($e);
            return $this->sendError(message: $e->getMessage());
        }
    }
}
