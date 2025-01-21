<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Http\Controllers\API;

use App\Http\Controllers\API\APIBase;
use Illuminate\Http\JsonResponse;
use Modules\ShoppingList\Http\Requests\ChangeIngredientStatusRequest;
use Modules\ShoppingList\Http\Requests\CustomIngredientRequest;
use Modules\ShoppingList\Http\Requests\DeleteIngredientRequest;
use Modules\ShoppingList\Http\Resources\ShoppingListCustomIngredientResource;
use Modules\ShoppingList\Models\ShoppingListIngredient;
use Modules\ShoppingList\Models\ShoppingListRecipe;
use Modules\ShoppingList\Services\ShoppingListIngredientsService;

/**
 * Shopping list ingredient API controller.
 *
 * @package Modules\ShoppingList\Http\Controllers\API
 */
class ShoppingListIngredientApiController extends APIBase
{
    /**
     * Add new ingredient to list.
     *
     * @route POST /api/v1/purchases/ingredient/add
     */
    public function add(CustomIngredientRequest $request, ShoppingListIngredientsService $service): JsonResponse
    {
        return $this->sendResponse(
            new ShoppingListCustomIngredientResource($service->addIngredient($request->user(), $request->custom_ingredient)),
            trans('shopping-list::messages.success.item_added')
        );
    }

    /**
     * Change ingredient status.
     *
     * @route POST /api/v1/purchases/ingredient/remove
     */
    public function destroy(DeleteIngredientRequest $request): JsonResponse
    {
        $shoppingListIngredient = ShoppingListIngredient::find($request->ingredient_id);

        if (!$shoppingListIngredient) {
            return $this->sendError(message: trans('common.error'));
        }

        $shoppingList = auth()->user()->shoppinglist;
        $deletedRecipes = [];

        if ($shoppingListIngredient->delete()) {
            foreach ($shoppingList->recipes as $recipe) {
                $remainingIngredients = $shoppingList->ingredients()
                    ->whereIn('shopping_lists_ingredients.id', function ($query) use ($recipe) {
                        $query->select('id')
                            ->from('shopping_lists_ingredients')
                            ->whereIn('ingredient_id', $recipe->ingredients->pluck('id'));
                    })
                    ->count();

                if ($remainingIngredients === 0) {
                    $pivotId = $recipe->pivot->id;
                    ShoppingListRecipe::where('recipe_id', $recipe->id)
                        ->where('list_id', $shoppingList->id)
                        ->delete();
                    $deletedRecipes[] = $pivotId;
                }
            }

            return $this->sendResponse(
                data: ['deletedRecipes' => $deletedRecipes],
                message: trans('common.success')
            );
        }

        return $this->sendError(message: trans('common.error'));
    }

    /**
     * Change ingredient status.
     *
     * @route POST /api/v1/purchases/ingredient/status
     */
    public function changeStatus(ChangeIngredientStatusRequest $request): JsonResponse
    {
        return ShoppingListIngredient::whereId($request->ingredient_id)->update(['completed' => $request->completed]) ?
            $this->sendResponse(true, trans('common.success')) :
            $this->sendError(message: trans('common.error'));
    }
}
