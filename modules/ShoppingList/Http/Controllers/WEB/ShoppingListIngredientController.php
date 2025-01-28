<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Http\Controllers\WEB;

use App\Exceptions\PublicException;
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
        try {
            $data = $service->removeIngredient($request->user(), $request->ingredient_id);
        } catch (PublicException $e) {
            return $this->sendError(message: trans('common.error'));
        }
        return $this->sendResponse($data, trans('common.success'));
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
