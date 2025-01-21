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
    public function destroy(DeleteIngredientRequest $request, ShoppingListIngredientsService $service): JsonResponse
    {
        return $service->removeIngredient($request->input('ingredient_id')) ?
            $this->sendResponse(true, trans('common.success')) :
            $this->sendError(message: trans('common.error'));
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
