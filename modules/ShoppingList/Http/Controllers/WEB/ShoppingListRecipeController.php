<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Http\Controllers\WEB;

use App\Exceptions\NoData;
use App\Exceptions\PublicException;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use Modules\ShoppingList\Http\Requests\AddRecipeToShoppingListRequest;
use Modules\ShoppingList\Http\Requests\ChangeRecipeServingsRequest;
use Modules\ShoppingList\Http\Requests\DeleteRecipeRequest;
use Modules\ShoppingList\Services\ShoppingListRecipesService;
use Modules\ShoppingList\Services\ShoppingListRetrieverService;
use Throwable;

/**
 * Controller responsible for recipes in purchase list.
 *
 * @package Modules\ShoppingList\Http\Controllers\WEB
 */
final class ShoppingListRecipeController extends Controller
{
    /**
     * Add recipe to shopping list.
     *
     * @route POST /user/purchases/recipe/add-to-shopping-list
     */
    public function add(AddRecipeToShoppingListRequest $request, ShoppingListRecipesService $service): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => trans('shopping-list::messages.success.recipe_added_to_purchase_list')
        ];
        try {
            $service->addRecipe($request->user(), ...array_values($request->validated()));
        } catch (PublicException|InvalidArgumentException $e) {
            $response = [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
        return response()->json($response);
    }

    /**
     * Delete recipe from purchase list.
     *
     * @route POST /user/purchases/recipe/delete
     */
    public function destroy(
        DeleteRecipeRequest          $request,
        ShoppingListRecipesService   $service,
        ShoppingListRetrieverService $retrieverService
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => trans('shopping-list::messages.success.recipe_removed_from_purchase_list'),
            'data'    => null
        ];
        $user = $request->user();
        try {
            $service->deleteRecipe(
                $user,
                $request->recipe_id,
                $request->recipe_type,
                $request->meal_day,
                $request->mealtime,
            );

            /**
             * Form Html response and send.
             * We must ensure we really have a collection.
             * As it is not required for api, we form it directly here and not in service.
             */
            $ingredientCollection = $user->shoppingList()->with(['ingredients.category'])->first()?->ingredients;

            if ($ingredientCollection->isNotEmpty()) {
                $response['data'] = view(
                    'shopping-list::includes.ingredientsList',
                    ['ingredient_categories' => $retrieverService->generateListData($user->lang, $ingredientCollection)]
                )->render();
            }
        } catch (ModelNotFoundException|PublicException $e) {
            $response = [
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null
            ];
        } catch (Throwable $e) {
            logError($e);
            $response = [
                'success' => false,
                'message' => trans('shopping-list::messages.error.recipe_servings'),
                'data'    => null
            ];
        } finally {
            return response()->json($response);
        }
    }

    /**
     * Change recipe servings.
     *
     * @route POST /user/purchases/recipe/changeServings
     */
    public function changeServings(
        ChangeRecipeServingsRequest  $request,
        ShoppingListRecipesService   $service,
        ShoppingListRetrieverService $retrieverService
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => trans('shopping-list::messages.success.portions_changed'),
            'data'    => null
        ];
        $user = $request->user();
        try {
            $service->changeRecipeServings(
                $user,
                $request->recipe_id,
                $request->servings,
                $request->recipe_type,
                $request->mealtime,
                $request->meal_day,
            );
            $ingredientCollection = $user->shoppingList()->with(['ingredients.category'])->first()?->ingredients;
            $response['data']     = view(
                'shopping-list::includes.ingredientsList',
                ['ingredient_categories' => $retrieverService->generateListData($user->lang, $ingredientCollection)]
            )->render();
        } catch (InvalidArgumentException|NoData|ModelNotFoundException|PublicException $e) {
            $response = [
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null
            ];
        } catch (Throwable $e) {
            logError($e);
            $response = [
                'success' => false,
                'message' => trans('shopping-list::messages.error.recipe_servings'),
                'data'    => null
            ];
        } finally {
            return response()->json($response);
        }
    }
}
