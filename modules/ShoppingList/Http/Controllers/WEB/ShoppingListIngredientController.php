<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\ShoppingList\Http\Requests\ChangeIngredientStatusRequest;
use Modules\ShoppingList\Http\Requests\CustomIngredientRequest;
use Modules\ShoppingList\Http\Requests\DeleteIngredientRequest;
use Modules\ShoppingList\Models\ShoppingListIngredient;
use Modules\ShoppingList\Models\ShoppingListRecipe;
use Modules\ShoppingList\Services\ShoppingListIngredientsService;

/**
 * Controller responsible for ingredients in purchase list.
 *
 * @package Modules\ShoppingList\Http\Controllers\WEB
 */
final class ShoppingListIngredientController extends Controller
{
    /**
     * Add new ingredient to current list.
     *
     * @route POST /user/purchases/ingredient/add
     */
    public function add(CustomIngredientRequest $request, ShoppingListIngredientsService $service): JsonResponse
    {
        return response()
            ->json(
                [
                    'success' => true,
                    'message' => trans('shopping-list::messages.success.item_added'),
                    'data'    => $service->addIngredient($request->user(), $request->custom_ingredient)
                ]
            );
    }

    /**
     * Delete ingredients from list.
     *
     * @route POST /user/purchases/ingredient/delete
     */
    public function destroy(DeleteIngredientRequest $request): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => trans('shopping-list::messages.error.item_removal')
        ];

        $shoppingListIngredient = ShoppingListIngredient::whereId($request->ingredient_id);

        if (empty($shoppingListIngredient)) {
            return response()->json($response, 404);
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
            $response = [
                'success' => true,
                'message' => trans('shopping-list::messages.success.item_removed'),
                'deletedRecipes' => $deletedRecipes
            ];
        }

        return response()->json($response);
    }

    /**
     * Change ingredient status.
     *
     * @route POST /user/purchases/ingredient/check
     */
    public function changeStatus(ChangeIngredientStatusRequest $request): JsonResponse
    {
        return response()->json(
            ShoppingListIngredient::whereId($request->ingredient_id)->update(['completed' => $request->completed]) ?
                ['success' => true, 'message' => trans('common.success')] :
                ['success' => false, 'message' => trans('common.error')]
        );
    }
}
