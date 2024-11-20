<?php

declare(strict_types=1);

namespace App\Http\Controllers\Recipes;

use App\Enums\Recipe\RecipeTypeEnum;
use App\Events\RecipeProcessed;
use App\Exceptions\PublicException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Recipe\ApplyRecipeToDateRequest;
use App\Http\Requests\Recipe\RecipeReplacementRequest;
use App\Models\UserRecipe;
use App\Models\UserRecipeCalculated;
use App\Services\RecipeService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Modules\FlexMeal\Http\Requests\API\FlexMealReplacementRequest;
use Modules\FlexMeal\Services\FlexMealService;
use Modules\ShoppingList\Services\ShoppingListAssistanceService;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

/**
 * Recipes replacement controller
 *
 * @package App\Http\Controllers\Recipes
 */
class RecipeReplacementController extends Controller
{
    /**
     * Replace a recipe with common one.
     *
     * TODO: response messages are not used on frontend
     */
    public function recipeReplacement(RecipeReplacementRequest $request, ShoppingListAssistanceService $service): JsonResponse
    {
        $user = $request->user();
        //        $challengeId = $user?->subscription?->id;
        try {
            $where = match ($request->recipeType) {
                RecipeTypeEnum::ORIGINAL->value => 'recipe_id',
                RecipeTypeEnum::CUSTOM->value   => 'custom_recipe_id',
                RecipeTypeEnum::FLEXMEAL->value => 'flexmeal_id',
                default                         => throw new \Exception('Unexpected match value'),
            };
        } catch (Throwable $e) {
            logError($e);
            return response()->json(['error' => false, 'message' => 'Unable to replace'], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

        if (UserRecipeCalculated::where(
            [
                ['user_id', $user->id],
                ['recipe_id', $request->change],
                ['ingestion_id', $request->ingestion->id],
                ['invalid', 0]
            ]
        )->doesntExist()) {
            return response()->json(['error' => false, 'message' => trans('common.no_calculation')], ResponseAlias::HTTP_NOT_FOUND);
        }

        // Attempt to delete old recipe from shopping list
        $service->maybeDeleteRecipe(
            $user,
            $request->recipe,
            $request->recipeType,
            $request->date->format('Y-m-d'),
            $request->ingestionIntValue
        );

        UserRecipe::where(
            [
                ['user_id', $user->id],
                // TODO:: review challenge_id @NickMost
                //				['challenge_id', $challengeId],
                [$where, $request->recipe],
                ['meal_time', $request->ingestion->key],
            ]
        )
            ->whereDate('meal_date', $request->date->format('Y-m-d')) // TODO: probably needs to be fixed meal_date range
            ->update(
                [
                    'recipe_id'        => $request->change,
                    'custom_recipe_id' => null,
                    'flexmeal_id'      => null
                ]
            );

        $service->maybeAddRecipe(
            $user,
            $request->change,
            RecipeTypeEnum::ORIGINAL->value,
            $request->date->format('Y-m-d'),
            $request->ingestionIntValue
        );

        # add recipe from purchase list
        $user->allRecipes()->syncWithoutDetaching(['recipe_id' => $request->change]);

        RecipeProcessed::dispatch();

        return response()->json(['success' => true, 'message' => trans('common.recipe_replaced') . '!']);
    }

    /**
     * Method for setting recipe for certain date.
     *
     * @route POST /user/recipes/apply_to_date
     */
    public function applyRecipe2date(
        ApplyRecipeToDateRequest      $request,
        RecipeService                 $recipeService,
        ShoppingListAssistanceService $shoppingListService
    ): JsonResponse {
        $response = ['success' => true, 'message' => trans('common.recipe_replaced') . '!', 'url' => null];
        $user     = $request->user();

        // Attempt to delete old(assigned to specific date) recipe from shopping list
        try {
            $meal = $user
                ->meals()
                ->where('ingestion_id', $request->ingestion->id)
                ->whereDate('meal_date', $request->date)
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
                $request->date->format('Y-m-d'),
                $request->ingestionIntValue
            );
        } catch (ModelNotFoundException|PublicException) {
            // TODO: maybe show some info later?
        } catch (\InvalidArgumentException $e) {
            logError($e);
        }

        try {
            $recipeService->replaceRecipe($user, $request->recipe, $request->date, $request->ingestion);

            $response['url'] = url(
                sprintf(
                    'user/recipes/%s/%s/%s',
                    $request->recipe->id,
                    $request->date->format('Y-m-d'),
                    $request->ingestion->key
                )
            );
        } catch (PublicException $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        } catch (Throwable $e) {
            logError($e);
            $response['success'] = false;
            $response['message'] = trans('common.unexpected_error');
        }

        // Attempt to add recipe to list if previously it existed
        $shoppingListService->maybeAddRecipe(
            $user,
            $request->recipe->id,
            $request->recipe_type,
            $request->date->format('Y-m-d'),
            $request->ingestionIntValue
        );

        return response()->json($response);
    }

    /**
     * Replace a meals recipe with a flexmeal.
     *
     * @route POST /user/recipes/replace_with_flexmeal
     */
    public function replaceWithFlexmeal(
        FlexMealReplacementRequest    $request,
        FlexMealService               $flexmealService,
        ShoppingListAssistanceService $shoppingListService
    ): JsonResponse {
        $user = $request->user();

        // Attempt to delete old recipe from shopping list
        $shoppingListService->maybeDeleteRecipe(
            $user,
            $request->recipe,
            $request->recipeType,
            $request->date->format('Y-m-d'),
            $request->ingestionIntValue
        );

        try {
            $flexmealService->replaceWithFlexMeal($request->user(), $request->ingestion, $request->date, $request->flexmeal);
        } catch (Throwable $exception) {
            return response()->json(
                [
                    'success' => false,
                    'data'    => null,
                    'message' => $exception->getMessage(),
                    'errors'  => [
                        'other' => $exception->getMessage()
                    ],
                ],
                ResponseAlias::HTTP_NOT_FOUND
            );
        }

        // Attempt to add new recipe to shopping list
        $shoppingListService->maybeAddRecipe(
            $user,
            $request->flexmeal_id,
            RecipeTypeEnum::FLEXMEAL->value,
            $request->date->format('Y-m-d'),
            $request->ingestionIntValue
        );

        return response()->json(
            [
                'success' => true,
                'data'    => null,
                'message' => trans('common.success'),
                'errors'  => null
            ],
        );
    }
}
