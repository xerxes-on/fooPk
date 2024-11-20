<?php

namespace Modules\FlexMeal\Http\Controllers;

use App\Exceptions\{NoData, PublicException};
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\{JsonResponse, RedirectResponse, Response};
use Illuminate\Routing\Redirector;
use Modules\FlexMeal\Http\Requests\{CheckFlexmealRequest,
    DeleteFlexmealRequest,
    StoreFlexmealRequest,
    UpdateFlexmealRequest};
use Modules\FlexMeal\Services\Calculations\FlexMealDeviationCalculator;
use Modules\FlexMeal\Services\FlexMealService;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Meal calculation controller
 *
 * @package App\Http\Controllers
 */
final class FlexMealController extends Controller
{
    /**
     * Render page with save all the Ingredients based Calculator
     *
     * @route POST /user/recipes/flexmeal/
     */
    public function store(StoreFlexmealRequest $request, FlexMealService $flexmealService): RedirectResponse|Redirector
    {
        $flexmealService->processStore(
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

        return redirect()->route('recipes.flexmeal.show')->with('success', trans('common.flexmeal_saved'));
    }

    /**
     * Update single flexmeal
     *
     * @route PATCH /user/recipes/flexmeal/
     */
    public function update(UpdateFlexmealRequest $request, FlexMealService $flexmealService): JsonResponse
    {
        try {
            $flexmealService->processUpdateOverWeb($request);
        } catch (ModelNotFoundException|PublicException) {
            return $this->sendError(message: trans('common.unexpected_error'), status: ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
        return $this->sendResponse(null, trans('common.flexmeal_updated'));
    }

    /**
     * Delete saved flexmeal
     *
     * @route DELETE /user/recipes/flexmeal/delete
     */
    public function destroy(DeleteFlexmealRequest $request, FlexMealService $flexmealService): Response|ResponseFactory
    {
        try {
            $flexmealService->processDelete($request->user(), $request->list_id);
        } catch (NoData|PublicException $e) {
            return response(['message' => $e->getMessage()], ResponseAlias::HTTP_NOT_FOUND);
        }

        return response(['message' => trans('common.flexmeal_deleted')]);
    }

    /**
     * Check flexmeal for nutrient deviation.
     *
     * @route POST /user/recipes/flexmeal/check-flexmeal
     */
    public function check(CheckFlexmealRequest $request, FlexMealDeviationCalculator $calculator): JsonResponse
    {
        return response()->json($calculator->calculate($request->flexmeal, $request->desired_mealtime));
    }
}
