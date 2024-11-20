<?php

declare(strict_types=1);

namespace Modules\FlexMeal\Http\Controllers\API;

use App\Enums\Recipe\RecipeTypeEnum;
use App\Exceptions\{NoData, PublicException};
use App\Http\Controllers\API\APIBase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\{JsonResponse, Request};
use Modules\FlexMeal\Http\Requests\{API\FlexMealReplacementRequest, CheckFlexmealRequest};
use Modules\FlexMeal\Http\Requests\API\{FlexMealUpdateImageRequest, FlexMealUpdateRequest};
use Modules\FlexMeal\Http\Requests\StoreFlexmealRequest;
use Modules\FlexMeal\Services\Calculations\FlexMealDeviationCalculator;
use Modules\FlexMeal\Services\FlexMealService;
use Modules\ShoppingList\Services\ShoppingListAssistanceService;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * API controller for managing FlexMeal
 *
 * @package App\Http\Controllers\API
 */
final class FlexMealApiController extends APIBase
{
    /**
     * Create a flexmeal.
     *
     * @route POST /api/v1/flex-meal/save
     */
    public function store(StoreFlexmealRequest $request, FlexMealService $service): JsonResponse
    {
        $flexmealId = $service->processStore(
            $request->only(
                [
                    'user_id',
                    'name',
                    'mealtime',
                    'notes',
                    'image'
                ]
            ),
            $request->ingredients
        );

        return $this->sendResponse(['id' => $flexmealId], trans('common.flexmeal_saved'));
    }

    /**
     * Update a FlexMeal by id.
     *
     * @route POST /api/v1/flex-meal/update/{$list_id}
     */
    public function update(FlexMealUpdateRequest $request, int $list_id, FlexMealService $service): JsonResponse
    {
        try {
            $service->processUpdateOverAPI($request, $list_id);
        } catch (ModelNotFoundException) {
            return $this->sendError(null, trans('common.nothing_found'));
        } catch (PublicException) {
            return $this->sendError(null, trans('common.unexpected_error'), ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->sendResponse(null, trans('api.flexmeal_updated'));
    }

    /**
     * Update a FlexMeal image by id.
     *
     * @route POST /api/v1/flex-meal/update/image/{list_id}
     */
    public function updateImage(FlexMealUpdateImageRequest $request, int $list_id, FlexMealService $service): JsonResponse
    {
        try {
            $service->processImageUpdate($request->file('new_image'), $list_id);
            return $this->sendResponse(trans('api.flexmeal_image_updated'), "Image of FlexMeal with id #$list_id was updated");
        } catch (ModelNotFoundException $e) {
            return $this->sendError(message: $e->getMessage());
        }
    }

    /**
     * Remove a FlexMeal image.
     *
     * @route  DELETE /api/v1/flex-meal/delete/image/{flexmealID}
     */
    public function destroyImage(Request $request, int $flexmealID): JsonResponse
    {
        try {
            $flexmeal = $request->user()->flexmealLists()->whereId($flexmealID)->firstOrFail();
            $flexmeal->update(['image' => STAPLER_NULL]);
        } catch (ModelNotFoundException) {
            return $this->sendError(message: "FlexMeal #$flexmealID can't be found.");
        }

        return $this->sendResponse(true, trans('common.success'));
    }

    /**
     * Delete a FlexMeal by id.
     *
     * @route DELETE /api/v1/flex-meal/{list_id}
     *
     * @param int $list_id ID of a flexmeal
     */
    public function destroy(Request $request, int $list_id, FlexMealService $service): JsonResponse
    {
        try {
            $service->processDelete($request->user(), $list_id);
        } catch (NoData|PublicException $e) {
            return $this->sendError(null, $e->getMessage());
        }

        return $this->sendResponse(trans('common.list_was_deleted'), "FlexMeal with ID #$list_id was deleted");
    }

    /**
     * Replace a meals recipe with a flexmeal.
     *
     * @route POST /api/v1/flex-meal/replace/
     */
    public function replace(
        FlexMealReplacementRequest    $request,
        FlexMealService               $flexMealService,
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
            $flexMealService->replaceWithFlexMeal($user, $request->ingestion, $request->date, $request->flexmeal);
        } catch (PublicException $exception) {
            return $this->sendError(message: $exception->getMessage());
        }

        // Attempt to add new recipe to shopping list
        $shoppingListService->maybeAddRecipe(
            $user,
            $request->flexmeal_id,
            RecipeTypeEnum::FLEXMEAL->value,
            $request->date->format('Y-m-d'),
            $request->ingestionIntValue
        );

        return $this->sendResponse([], trans('common.success'));
    }

    /**
     * Check flexmeal for nutrient deviation.
     *
     * @route POST /api/v1/flex-meal/check-flexmeal
     */
    public function check(CheckFlexmealRequest $request, FlexMealDeviationCalculator $calculator): JsonResponse
    {
        return response()->json($calculator->calculate($request->flexmeal, $request->desired_mealtime));
    }
}
