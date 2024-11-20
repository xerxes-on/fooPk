<?php

declare(strict_types=1);

namespace App\Http\Controllers\Recipes;

use App\Enums\MealtimeEnum;
use App\Enums\Recipe\RecipeTypeEnum;
use App\Exceptions\PublicException;
use App\Helpers\Calculation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Recipe\{CreateFullyCustomRecipeRequest, CustomFromCommonRecipeCreationRequest};
use App\Models\Ingestion;
use App\Services\{RecipeService};
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Routing\Redirector;
use Illuminate\View\View;
use Modules\Ingredient\Models\Ingredient;
use Modules\Ingredient\Models\IngredientCategory;
use Modules\ShoppingList\Services\ShoppingListAssistanceService;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

/**
 * Controller for custom user recipes.
 *
 * @package App\Http\Controllers\User
 */
final class CustomRecipeController extends Controller
{
    /**
     * Create custom recipe from common recipe.
     *
     * @route POST /user/recipes/custom/create-from-common
     */
    public function createFromCommonRecipe(
        CustomFromCommonRecipeCreationRequest $request,
        ShoppingListAssistanceService         $shoppingListService,
        RecipeService                         $recipeService
    ): JsonResponse {
        $user = $request->user();

        // Attempt to delete old recipe from shopping list
        $params = $request->custom_recipe_id ?
            [
                $user,
                (int)$request->custom_recipe_id,
                RecipeTypeEnum::CUSTOM->value,
                $request->date->format('Y-m-d'),
                $request->ingestionIntValue
            ] :
            [
                $user,
                (int)$request->recipe->id,
                RecipeTypeEnum::ORIGINAL->value,
                $request->date->format('Y-m-d'),
                $request->ingestionIntValue
            ];
        $shoppingListService->maybeDeleteRecipe(...$params);

        // Attempt to create or update custom recipe
        try {
            $result = $recipeService->createCustomRecipe(
                $user,
                $request->date,
                $request->ingestion,
                $request->recipe,
                (array)$request->fixed_ingredients,
                $request->variable_ingredients,
            );
        } catch (PublicException $e) {
            return $this->sendError(message: $e->getMessage());
        } catch (Throwable $e) {
            logError($e);
            return $this->sendError(message: 'Something went wrong.', status: ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Attempt to add new recipe to shopping list
        $resultObject = is_array($result) ? (object)$result : $result;
        $shoppingListService->maybeAddRecipe(
            $user,
            (int)($resultObject?->recipe?->id ?? $resultObject?->recipe_information?->id), // TODO: it can be missing and thus set to null
            RecipeTypeEnum::CUSTOM->value,
            $request->date->format('Y-m-d'),
            $request->ingestionIntValue
        );

        return response()
            ->json(
                is_array($result) ?
                    $result :
                    [
                        'success' => true,
                        'route'   => route(
                            'recipe.show.custom.common',
                            [
                                'id'        => $result->recipe->id,
                                'date'      => $request->date->format('Y-m-d'),
                                'ingestion' => $request->ingestion->key,
                            ]
                        )
                    ]
            );
    }

    /**
     * Restore original recipe from custom
     *
     * @rote GET  /user/recipes/custom/{id}/restore
     */
    public function restoreRecipe(
        Request                       $request,
        int                           $id,
        ShoppingListAssistanceService $shoppingListService,
        RecipeService                 $recipeService
    ): RedirectResponse|Redirector {
        // Gather required data
        $user = $request->user();

        try {
            $meal = $user->meals()
                ->with(['customRecipe' => fn(HasOne $relation) => $relation->select(['id', 'recipe_id'])])
                ->where('custom_recipe_id', $id)
                ->firstOrFail();
            $originalRecipeId = $meal->customRecipe?->recipe_id ?? $meal->original_recipe_id;
        } catch (ModelNotFoundException) {
            return redirect()->back()->with('error', trans('common.recipe_dont_exist'));
        }

        $mealtimeIntValue = MealtimeEnum::tryFromValue($meal->meal_time)->value;
        // Attempt to delete old recipe from shopping list
        $shoppingListService->maybeDeleteRecipe(
            $user,
            $meal->custom_recipe_id,
            RecipeTypeEnum::CUSTOM->value,
            $meal->meal_date,
            $mealtimeIntValue
        );

        // Restore custom to original recipe
        $recipeService->restore($meal, $originalRecipeId);

        // Attempt to add new recipe to shopping list
        $shoppingListService->maybeAddRecipe(
            $user,
            $originalRecipeId,
            RecipeTypeEnum::ORIGINAL->value,
            $meal->meal_date,
            $mealtimeIntValue
        );

        return redirect()
            ->route(
                'recipe.show',
                [
                    'id'        => $meal->recipe_id,
                    'date'      => date('Y-m-d', strtotime((string)$meal->meal_date)),
                    'ingestion' => $meal->meal_time
                ]
            )
            ->with('success', trans('common.recipe_restored'));
    }
}
