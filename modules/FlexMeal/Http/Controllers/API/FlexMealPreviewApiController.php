<?php

namespace Modules\FlexMeal\Http\Controllers\API;

use App\Http\Controllers\API\APIBase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\{JsonResponse, Request};
use Modules\FlexMeal\Http\Resources\FlexMealListResource;
use Modules\FlexMeal\Models\FlexmealLists;
use Modules\FlexMeal\Services\FlexMealService;

/**
 * API controller for previewing FlexMeals
 *
 * @package App\Http\Controllers\API
 */
final class FlexMealPreviewApiController extends APIBase
{
    /**
     * Get a single FlexMeal by ID.
     *
     * @route  GET /api/v1/flex-meal/edit/{id}
     */
    public function get(Request $request, int $id, FlexMealService $service): JsonResponse
    {
        $user = $request->user();
        try {
            // Just a check if meal exits.
            $user->flexmealLists()->where('id', $id)->firstOrFail();
        } catch (ModelNotFoundException) {
            return $this->sendError(trans('api.flexmeal_404'), "FlexMeal with ID #$id isn't found.");
        }

        $mealListMap = $service->getFlexMealWithIngredientsMap($user, true);
        $flexmeal    = $mealListMap->first(fn(FlexmealLists $item) => $item->id === $id);
        $resource    = new FlexMealListResource($flexmeal);
        return $this->sendResponse($resource, 'FlexMeal loaded successfully.');
    }

    /**
     * Retrieve archive page
     */
    public function getArchive(Request $request, FlexMealService $service): JsonResponse
    {
        $perPage     = (int)$request->get('per_page', 20);
        $mealListMap = $service->getFlexMealWithIngredientsMap($request->user(), true);
        $response    = FlexMealListResource::collection($mealListMap);
        $message     = $response->count() > 0 ? trans('common.saved_meal_header') : trans('common.saved_text');
        return $this->sendResponse($response->paginate($perPage), $message);
    }
}
