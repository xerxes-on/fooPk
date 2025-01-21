<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\ShoppingList\Http\Requests\ChangeIngredientStatusRequest;
use Modules\ShoppingList\Http\Requests\CustomIngredientRequest;
use Modules\ShoppingList\Http\Requests\DeleteIngredientRequest;
use Modules\ShoppingList\Models\ShoppingListIngredient;
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
    public function destroy(DeleteIngredientRequest $request, ShoppingListIngredientsService $service): JsonResponse
    {
        $data = $service->removeIngredient(intval($request->input('ingredient_id')));
        return response()->json(
            is_array($data) ? ['success' => true, 'deletedRecipes' => $data, 'message' => trans('common.success')] :
                ['success' => false, 'message' => trans('common.error')]
        );
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
