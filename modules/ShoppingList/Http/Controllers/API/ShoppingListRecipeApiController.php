<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Http\Controllers\API;

use App\Exceptions\PublicException;
use App\Http\Controllers\API\APIBase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use Modules\ShoppingList\Http\Requests\AddRecipeToShoppingListRequest;
use Modules\ShoppingList\Http\Requests\ChangeRecipeServingsRequest;
use Modules\ShoppingList\Http\Requests\DeleteRecipeRequest;
use Modules\ShoppingList\Services\ShoppingListRecipesService;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

/**
 * Shopping list recipe API controller.
 *
 * @package Modules\ShoppingList\Http\Controllers\API
 */
final class ShoppingListRecipeApiController extends APIBase
{
    /**
     * Add recipe to shopping list.
     *
     * @route POST /api/v1/purchases/recipe/add
     */
    public function add(AddRecipeToShoppingListRequest $request, ShoppingListRecipesService $service): JsonResponse
    {
        try {
            $service->addRecipe($request->user(), ...array_values($request->validated()));
            return $this->sendResponse(true, trans('shopping-list::messages.success.recipe_added_to_purchase_list'));
        } catch (PublicException|InvalidArgumentException $e) {
            return $this->sendError(message: $e->getMessage());
        }
    }

    /**
     * Delete recipe from shopping list.
     *
     * @route  POST /api/v1/purchases/recipe/delete
     */
    public function destroy(DeleteRecipeRequest $request, ShoppingListRecipesService $service): JsonResponse
    {
        try {
            $service->deleteRecipe(
                $request->user(),
                $request->recipe_id,
                $request->recipe_type,
                $request->meal_day,
                $request->mealtime,
            );
            return $this->sendResponse(true, trans('shopping-list::messages.success.recipe_removed_from_purchase_list'));
        } catch (ModelNotFoundException|PublicException $e) {
            return $this->sendError(message: $e->getMessage());
        } catch (InvalidArgumentException) {
            return $this->sendError(
                message: trans('shopping-list::messages.error.recipe_servings'),
                status: ResponseAlias::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }

    /**
     * Change recipe servings.
     *
     * @route POST /api/v1/purchases/recipe/servings
     */
    public function changeServings(ChangeRecipeServingsRequest $request, ShoppingListRecipesService $service): JsonResponse
    {
        try {
            $service
                ->changeRecipeServings(
                    $request->user(),
                    $request->recipe_id,
                    $request->servings,
                    $request->recipe_type,
                    $request->mealtime,
                    $request->meal_day,
                );
            return $this->sendResponse(true, trans('shopping-list::messages.success.portions_changed'));
        } catch (ModelNotFoundException|PublicException $e) {
            return $this->sendError(message: $e->getMessage());
        } catch (Throwable $e) {
            logError($e);
            return $this->sendError(
                message: trans('shopping-list::messages.error.recipe_servings'),
                status: ResponseAlias::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }
}
