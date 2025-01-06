<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Enums\Recipe\RecipeTypeEnum;
use App\Exceptions\PublicException;
use App\Helpers\Calculation;
use App\Http\Requests\API\Meal\{MealWeekFilterRequest, PlannedMealFilterRequest, SkipMealRequest};
use App\Http\Requests\API\Recipe\Replacement\RecipeReplacementRequest;
use App\Http\Resources\{Meal\PlannedMealResource, UsersNutritionData};
use App\Repositories\Recipes;
use App\Services\{MealService, RecipeService, UserMealService};
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Modules\FlexMeal\Http\Resources\FlexMealIngredientResource;
use Modules\ShoppingList\Services\{ShoppingListAssistanceService};
use Modules\ShoppingList\Services\ShoppingListRecipesService;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

/**
 * Controller for planned users meals.
 *
 * @package App\Http\Controllers\API
 */
final class MealsApiController extends APIBase
{
    /**
     * Meals constructor.
     */
    public function __construct(private readonly Recipes $recipesRepo)
    {
    }

    /**
     * Get planned meal details.
     * TODO: Refactor required!!!
     * @route GET /api/v1/planned-meal
     */
    public function getPlanned(PlannedMealFilterRequest $request): JsonResponse
    {
        /**@var \App\Models\User $user */
        $user          = $request->user();
        $formattedDate = $request->date->format('Y-m-d');

        try {
            $meal = $user
                ->meals()
                ->whereDate('meal_date', $request->date)
                ->where('ingestion_id', $request->ingestion->id)
                ->firstOrFail();
        } catch (ModelNotFoundException) {
            return $this->sendError(message: "There's no {$request->ingestion->key} on $formattedDate.");
        }

        // Detect recipe type. Will always be one instanceof UserRecipe
        if ($meal->recipe_id) {
            try {
                $recipe = $user
                    ->plannedRecipes(
                        $meal->recipe_id,
                        $formattedDate,
                        $request->ingestion->id
                    )
                    ->firstOrFail();
                $recipe->custom_categories = $this->recipesRepo->getRecipeCustomCategories($recipe, $user);
            } catch (ModelNotFoundException) {
                return $this->sendError(message: 'The recipe can\'t be found.');
            }
        } elseif ($meal->custom_recipe_id) {
            $recipe = $user->customPlannedRecipe($meal->custom_recipe_id)->first();
        } elseif ($meal->flexmeal_id) {
            $recipe = $user
                ->plannedFlexmeals()
                ->with(['ingredients.ingredient' => static fn(Relation $query) => $query->with(['hint', 'alternativeUnit'])])
                ->where([
                    ['flexmeal_id', $meal->flexmeal_id],
                    ['meal_date', $meal->meal_date],
                ])
                ->first();
        } else {
            return $this->sendError(message: 'The meal doesn\'t have a recipe.');
        }

        if (!$recipe) {
            return $this->sendError(message: 'The recipe can\'t be found.');
        }

        // If ingestion is missing from current meal, just substitute with correct meal ingestion data
        if (is_null($recipe->pivot->ingestion)) {
            $recipe->pivot->ingestion = $request->ingestion;
        }
        $result = ['planned-meal' => new PlannedMealResource($recipe)];

        // load missing recipe tags only for original recipes
        if ($recipe instanceof \App\Models\Recipe) {
            $recipe->loadMissing('publicTags.translations');
        }

        if (is_null($meal->flexmeal_id)) {
            $result['users-nutrition-data'] = new UsersNutritionData($user);
            $result['ingredients']          = Calculation::parseRecipeData($recipe, $user->lang);
            $result['additional-info']      = array_filter(
                json_decode((string)$recipe->calc_recipe_data, true),
                static fn($key) => in_array($key, ['calculated_KCal', 'calculated_KH', 'calculated_EW', 'calculated_F']),
                ARRAY_FILTER_USE_KEY
            );
        } else {
            $result['users-nutrition-data'] = [];
            $result['ingredients']          = FlexMealIngredientResource::collection($recipe->ingredients);
            $result['additional-info']      = $recipe->getNutritionalData();
        }

        return $this->sendResponse($result, trans('common.success'));
    }

    /**
     * Get planned meals.
     *
     * @route GET /api/v1/plan
     */
    public function getPlan(MealWeekFilterRequest $request, UserMealService $service): JsonResponse
    {
        if (is_null($request->user()?->subscription)) {
            return $this->sendError(message: 'You need a challenge.', status: ResponseAlias::HTTP_FORBIDDEN);
        }

        $plan = $request->filter === 'daily' ?
            $service->getDailyMeals($request->user(), $request->date) :
            $service->getWeeklyMeals($request->user(), $request->date);

        return $this->sendResponse($plan, trans('common.success'));
    }

    /**
     * Replace recipe of a meal. Can replace any recipe with original one.
     * Passing custom recipe type allows to apply edited custom recipe to certain date.
     *
     * @route POST /api/v1/replace-recipe/
     */
    public function replace(
        RecipeReplacementRequest      $request,
        RecipeService                 $recipeService,
        ShoppingListAssistanceService $shoppingListService
    ): JsonResponse {
        $user = $request->user();

        // Attempt to delete old(assigned to specific date) recipe from shopping list
        try {
            $meal = $user
                ->meals()
                ->where('ingestion_id', $request->ingestion->id)
                ->whereDate('meal_date', $request->meal_date)
                ->firstOrFail();

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
            $shoppingListService->maybeDeleteRecipe(
                $user,
                $recipeId,
                $type->value,
                $request->meal_date->format('Y-m-d'),
                $request->ingestionIntValue
            );
        } catch (ModelNotFoundException) {
            // DO nothing
        }

        try {
            $request->recipe_type === RecipeTypeEnum::CUSTOM->value ?
                $recipeService->replaceWithCustom($user, $request->recipe, $request->meal_date, $request->ingestion) :
                $recipeService->replaceRecipe($user, $request->recipe, $request->meal_date, $request->ingestion);
        } catch (PublicException $e) {
            return $this->sendError(message: $e->getMessage());
        } catch (Throwable $e) {
            logError($e);
            return $this->sendError(message: trans('common.unexpected_error'), status: ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Attempt to add recipe to list if previously it existed
        $shoppingListService->maybeAddRecipe(
            $user,
            $request->recipe->id,
            $request->recipe_type,
            $request->meal_date->format('Y-m-d'),
            $request->ingestionIntValue
        );

        return $this->sendResponse(true, trans('common.success'));
    }

    /**
     * Skip user meal or restore it back.
     *
     * @route POST /api/v1/eat-out.
     */
    public function eatOutRecipe(
        SkipMealRequest            $request,
        MealService                $mealService,
        ShoppingListRecipesService $shoppingListService
    ): JsonResponse {
        $user = $request->user();
        try {
            $meal = $mealService->skipMeal(
                $user,
                $request->date,
                $request->ingestion,
                $request->isEatOut,
            );
        } catch (ModelNotFoundException $e) {
            return $this->sendError(message: $e->getMessage());
        }

        if ($request->isEatOut === 0) {
            return $this->sendResponse(true, trans('api.meal_skipped'));
        }

        // Attempt to delete old recipe from shopping list
        try {
            $recipeId = match ((int)$request->recipeType) {
                RecipeTypeEnum::ORIGINAL->value => $meal->recipe_id,
                RecipeTypeEnum::CUSTOM->value   => $meal->custom_recipe_id,
                RecipeTypeEnum::FLEXMEAL->value => $meal->flexmeal_id,
                default                         => throw new \InvalidArgumentException(
                    sprintf('Invalid recipe type of "%s" type -%s. ', $request->recipeType, gettype($request->recipeType))
                )
            };

            $shoppingListService->deleteRecipe($user, $recipeId, $request->recipeType, $request->date->format('Y-m-d'));
        } catch (ModelNotFoundException|PublicException) {
            // TODO: maybe show some info later?
        } catch (\InvalidArgumentException $e) {
            logError($e);
        }

        return $this->sendResponse(trans('api.meal_skipped'), trans('common.success'));
    }
}
