<?php

namespace Modules\FlexMeal\Http\Controllers;

use App\Enums\Recipe\RecipeTypeEnum;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\View\View;
use Modules\FlexMeal\Http\Requests\{FlexMealByIngestionRequest};
use Modules\FlexMeal\Http\Resources\CalculatedFlexmealResource;
use Modules\FlexMeal\Models\FlexmealLists;
use Modules\FlexMeal\Services\FlexMealService;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Meal calculation controller
 *
 * @package App\Http\Controllers
 */
final class FlexMealPreviewController extends Controller
{
    /**
     * Render page with All ingredient based Calculator
     *
     * @route GET /user/recipes/flexmeal/
     */
    public function index(Request $request): Factory|View
    {
        $user = $request->user();
        return view(
            'flexmeal::create',
            ['dietdata' => empty($user->dietdata) ? false : $user->dietdata['ingestion']]
        );
    }

    /**
     * Render page with a list of saved flexmeals.
     *
     * @route GET  /user/recipes/flexmeal/archive
     */
    public function show(Request $request): View|Factory
    {
        $user = $request->user();
        return view(
            'flexmeal::archive',
            ['dietdata' => empty($user->dietdata) ? false : $user->dietdata['ingestion']]
        );
    }

    /**
     * Show a single flexmeal.
     *
     * @route GET  /user/recipes/flexmeal/show/{id}
     */
    public function showSingle(Request $request): View|Factory
    {
        /**
         * @note table recipes_to_users is huge. Search over it is very slow.
         * In case we need to filter it more by date or else, adds some time seconds to duration.
         * This is why after obtaining collection we filter it by date and ingestion using php
         */
        $recipe = $request
            ->user()
            ->flexmealLists()
            ->join(
                'recipes_to_users',
                'flexmeal_lists.id',
                '=',
                'recipes_to_users.flexmeal_id'
            )
            ->with(['ingredients.ingredient.hint', 'ingredients.ingredient.alternativeUnit'])
            ->select(
                'flexmeal_lists.*',
                'recipes_to_users.meal_date AS meal_date',
                'recipes_to_users.meal_time AS mealtime',
            )
            ->where('flexmeal_lists.id', $request->route('id'))
            ->get()
            ->filter(function (FlexmealLists $item) use ($request) {
                $dateIsSame = Carbon::parse($item->meal_date)->format('Y-m-d') ===
                    Carbon::parse($request->route('date'))->format('Y-m-d');
                $ingestionIsSame = $item->mealtime === $request->route('ingestion');
                if ($dateIsSame && $ingestionIsSame) {
                    return $item;
                }
            })
            ->first();

        if (is_null($recipe)) {
            abort(ResponseAlias::HTTP_NOT_FOUND);
        }

        return view(
            'recipes.feed.flexmeal',
            ['recipe' => $recipe, 'recipeType' => RecipeTypeEnum::FLEXMEAL->value]
        );
    }

    /**
     * Get Flexmeals with calculated ingredients grouped by ingestions.
     *
     * @route GET /user/recipes/flexmeal/for-mealtime
     */
    public function showByIngestion(FlexMealByIngestionRequest $request, FlexMealService $service): JsonResponse
    {
        return response()
            ->json(
                new CalculatedFlexmealResource(
                    $service->getFlexMealByIngestionWithIngredientsMap($request->user(), $request->mealtime)
                )
            );
    }
}
