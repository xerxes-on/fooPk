<?php

declare(strict_types=1);

namespace App\Http\Controllers\Recipes;

use App\Enums\Recipe\RecipeTypeEnum;
use App\Helpers\{CacheKeys, Calculation};
use App\Http\Controllers\Controller;
use App\Http\Requests\Recipe\GetRecipesByRationRequest;
use App\Http\Resources\Recipe\RecipeSearchPreviewResource;
use App\Models\{Diet, Ingestion, Recipe, RecipeComplexity, RecipePrice, RecipeTag};
use App\Repositories\Recipes;
use App\Repositories\SeasonsRepository;
use Carbon\{Carbon, CarbonPeriod};
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;

/**
 * Recipes controller
 *
 * TODO: The class has a coupling between objects value of 25. Consider to reduce the number of dependencies under 13.
 * @package App\Http\Controllers
 */
class RecipeController extends Controller
{
    /**
     * Get a recipe for a planned users meal, with some details.
     */
    public function show(Request $request, int $id, string $date = null, string $ingestion_key = ''): Factory|View
    {
        try {
            // TODO: maybe get it as a model from app on method init
            // TODO: ingestion is displayed incorrectly
            $ingestion = Ingestion::ofKey($ingestion_key)->firstOrFail();
            $recipe    = $request->user()->plannedRecipes($id, $date, $ingestion->id)->firstOrFail();

            //TODO:: @NickMost refactor, trick to show only allowed for user ingestions
            $recipe->setRelation('ingestions', $recipe->ingestions->whereIn('id', $request->user()->allowed_ingestion_ids));

        } catch (ModelNotFoundException) {
            abort(ResponseAlias::HTTP_NOT_FOUND);
        }

        $recipeType = RecipeTypeEnum::ORIGINAL->value;

        $calculatedIngredients = Calculation::parseRecipeData($recipe, $request->user()->lang);
        $recipeData            = json_decode($recipe->calc_recipe_data, true);
        $additionalInfo        = array_filter(
            $recipeData,
            static fn($key) => in_array(
                $key,
                ['calculated_KCal', 'calculated_KH', 'calculated_EW', 'calculated_F']
            ),
            ARRAY_FILTER_USE_KEY
        );

        if (request()->has('print')) {
            $ingestion = null;
            return view(
                'print.recipe',
                compact('recipe', 'date', 'ingestion', 'calculatedIngredients', 'additionalInfo', 'recipeType')
            );
        }

        return view(
            'recipes.feed.show',
            compact('recipe', 'date', 'ingestion', 'calculatedIngredients', 'additionalInfo', 'recipeType')
        );
    }

    /**
     * Get any users' recipe.
     */
    public function showFromAllRecipes(Request $request, int $id): View|Factory
    {
        $ingestion = null;
        $recipe    = $request->user()->recipeWithCalculations($id)->orderBy('calc_invalid', 'ASC')->first();
        if (is_null($recipe)) {
            abort(ResponseAlias::HTTP_NOT_FOUND);
        }
        //TODO:: @NickMost refactor, trick to show only allowed for user ingestions
        $recipe->setRelation('ingestions', $recipe->ingestions->whereIn('id', $request->user()->allowed_ingestion_ids));

        $calculatedIngredients = Calculation::parseRecipeData($recipe, $request->user()->lang);

        $view       = request()->has('print') ? 'print.recipe' : 'recipes.feed.show';
        $recipeType = RecipeTypeEnum::ORIGINAL->value;

        return view($view, compact('recipe', 'ingestion', 'calculatedIngredients', 'recipeType'));
    }

    /**
     * Show custom recipe created from a common recipe.
     */
    public function showCustomCommon(int $id, string $date, string $ingestion): Factory|View|RedirectResponse
    {
        $user         = \Auth::user();
        $customRecipe = $user
            ->datedCustomRecipes()
            ->withPivot('meal_date', 'meal_time', 'cooked')
            ->leftJoin(
                'user_recipe_calculated',
                'custom_recipes.id',
                '=',
                'user_recipe_calculated.custom_recipe_id'
            )
            ->leftJoin('ingestions', 'user_recipe_calculated.ingestion_id', '=', 'ingestions.id')
            ->select([
                'custom_recipes.title',
                'custom_recipes.recipe_id AS original_recipe_id',
                'recipes_to_users.custom_recipe_id AS custom_recipe_id',
                'user_recipe_calculated.recipe_data AS calc_recipe_data',
                'user_recipe_calculated.invalid AS calc_invalid',
                'user_recipe_calculated.updated_at AS calc_updated_at',
                'recipes_to_users.meal_date AS meal_date',
                'ingestions.key AS meal_time'
            ])
            ->whereColumn('user_recipe_calculated.ingestion_id', 'recipes_to_users.ingestion_id')
            ->where('user_recipe_calculated.user_id', $user->id)
            ->where('custom_recipes.id', $id)
            ->first();

        if (is_null($customRecipe)) {
            abort(ResponseAlias::HTTP_NOT_FOUND);
        }

        try {
            $recipe = Recipe::findOrFail($customRecipe->original_recipe_id);
        } catch (Throwable) {
            abort(ResponseAlias::HTTP_NOT_FOUND);
        }

        $calculatedIngredients = Calculation::parseRecipeData($customRecipe, $user->lang);
        $custom_common         = $customRecipe->custom_recipe_id;
        $recipeType            = RecipeTypeEnum::CUSTOM->value;

        if (request()->has('print')) {
            $ingestion = null;
            return view('print.recipe', compact('recipe', 'date', 'ingestion', 'calculatedIngredients', 'recipeType'));
        }

        // TODO: maybe amend it?
        $ingestion = Ingestion::ofKey($ingestion)->firstOrFail();

        return view(
            'recipes.feed.show',
            compact('recipe', 'date', 'ingestion', 'calculatedIngredients', 'custom_common', 'recipeType')
        );
    }

    /**
     * Show all recipes
     *
     * @route GET /user/recipes/grid
     */
    public function allRecipes(Request $request, SeasonsRepository $seasonsRepo, Recipes $recipesRepo): Factory|View
    {
        $perPage = (int)$request->get('per_page', 20);

        $perPage    = ($perPage < 20) ? 20 : (($perPage > 40) ? 40 : $perPage);
        $conditions = $request
            ->only(
                [
                    'search_name',
                    'ingestion',
                    'complexity',
                    'cost',
                    'diet',
                    'invalid',
                    'seasons',
                    'favorite',
                    'excluded',
                    'recipe_tag'
                ]
            );

        return view(
            'recipes.allRecipes.index',
            [
                'recipes'      => $recipesRepo->getAll($request->user(), $perPage, $conditions),
                'ingestions'   => Ingestion::getAllActive()->pluck('title', 'id')->toArray(),
                'complexities' => RecipeComplexity::getAll()->pluck('title', 'id')->toArray(),
                'costs'        => RecipePrice::pluck('title', 'id')->toArray(),
                'diets'        => Diet::getAll()->pluck('name', 'id')->toArray(),
                'seasons'      => $seasonsRepo->getRelevant($request->user())->pluck('name', 'id')->toArray(),
                'favorites'    => [
                    'favorite' => trans('common.favorite'),
                ],
                'invalids' => [
                    -1 => trans('common.all'),
                    0  => trans('common.valid'),
                    1  => trans('common.invalid'),
                ],
                'tags' => RecipeTag::publicOnly()
                    ->with('translations')
                    ->get()
                    ->map(fn(RecipeTag $tag) => ['id' => $tag->id, 'title' => $tag->title])
                    ->pluck('title', 'id')
                    ->toArray(),
            ]
        );
    }

    /**
     * Show weekly recipes.
     */
    public function listView(Request $request, SeasonsRepository $seasonsRepo): Factory|View
    {
        $calendar     = $this->getCalendar($request->only(['week', 'year']));
        $recipesGroup = $this->getWeeklyRecipes($calendar);
        $seasons      = $seasonsRepo->getAll();
        return view('recipes.feed.list', compact('recipesGroup', 'calendar', 'seasons'));
    }

    /**
     * Show weekly recipes in grid
     */
    public function gridView(Request $request, SeasonsRepository $seasonsRepo): Factory|View
    {
        $ingestions   = Ingestion::getAllActive();
        $calendar     = $this->getCalendar($request->only(['week', 'year']));
        $recipesGroup = $this->getWeeklyRecipes($calendar);
        $seasons      = $seasonsRepo->getAll();
        return view('recipes.feed.grid', compact('recipesGroup', 'calendar', 'ingestions', 'seasons'));
    }

    /**
     * Get or create calendar by date.
     */
    private function getCalendar(array $attributes): array
    {
        $date    = Carbon::now();
        $curYear = $date->year;
        if (
            !empty($attributes) &&
            array_key_exists('week', $attributes) && !empty($attributes['week']) &&
            array_key_exists('year', $attributes) && !empty($attributes['year'])
        ) {
            $date->setISODate($attributes['year'], $attributes['week']);
            $curYear = $attributes['year'];
        }

        $prevWeek = $date->copy()->subWeek()->endOfWeek();
        $nextWeek = $date->copy()->addWeek()->endOfWeek();

        return [
            'prevWeek' => [
                'week' => $prevWeek->weekOfYear,
                'year' => ($curYear > $prevWeek->year) ? $prevWeek->year : $curYear,
            ],
            'curWeek' => CarbonPeriod::create(
                $date->startOfWeek()->format('Y-m-d'),
                '1 days',
                $date->endOfWeek()->format('Y-m-d')
            ),
            'nextWeek' => [
                'week' => $nextWeek->weekOfYear,
                'year' => ($curYear < $nextWeek->year) ? $nextWeek->year : $curYear,
            ],
        ];
    }

    /**
     * Get weekly recipes.
     */
    public function getWeeklyRecipes(array $calendar): array
    {
        $curWeek                = $calendar['curWeek'];
        $user                   = auth()->user();
        $currentWeek            = Carbon::now()->weekOfYear;
        $isCurrentWeekRequested = $currentWeek === $calendar['curWeek']->get('current')->weekOfYear;
        $recipesGroup           = Cache::get(CacheKeys::userWeeklyPlan($user->id, $currentWeek));

        // We only want to get cached values if it exists and a recipes requested for this week
        if (!empty($recipesGroup) && $isCurrentWeekRequested) {
            return $recipesGroup;
        }

        $meals = Ingestion::getAll()
            ->map(fn($meal) => ['key' => $meal->key, 'value' => $meal->order])
            ->pluck('value', 'key')
            ->toArray();
        $mealDateStart = $curWeek->getStartDate();
        $mealDateEnd   = $curWeek->getEndDate();
        $recipes       = $user
            ->recipes()
            ->with(['complexity', 'favorite'])
            ->whereBetween('recipes_to_users.meal_date', [$mealDateStart, $mealDateEnd])
            ->get(); // todo: slow query
        $custom = $user
            ->datedCustomRecipes()
            ->with(['originalRecipe.complexity', 'originalRecipe.favorite'])
            ->whereBetween('recipes_to_users.meal_date', [$mealDateStart, $mealDateEnd])
            ->get();
        $flexmeals = $user
            ->plannedFlexmeals()
            ->whereBetween('recipes_to_users.meal_date', [$mealDateStart, $mealDateEnd])
            ->get();
        $recipesGroup = [];

        // TODO: refactor, group it as a collection
        foreach ($recipes as $item) {
            $recipesGroup[date('Y-m-d', strtotime($item->pivot->meal_date))][] = $item;
        }

        foreach ($custom as $item) {
            $recipesGroup[date('Y-m-d', strtotime($item->pivot->meal_date))][] = $item;
        }

        foreach ($flexmeals as $item) {
            $recipesGroup[date('Y-m-d', strtotime($item->pivot->meal_date))][] = $item;
        }

        ksort($recipesGroup);

        foreach ($recipesGroup as $key => $_recipe) {
            usort(
                $recipesGroup[$key],
                static fn($a, $b) => $meals[$a->pivot->meal_time] - $meals[$b->pivot->meal_time]
            );
        }

        // We only cache recipes for current week.
        if ($isCurrentWeekRequested) {
            Cache::put(CacheKeys::userWeeklyPlan($user->id, $currentWeek), $recipesGroup, config('cache.lifetime_day'));
        }

        return $recipesGroup;
    }

    /**
     * Get recipes by ration food.
     *
     * @route POST /user/recipes/get_user_recipes
     */
    public function getRecipesByRationFood(GetRecipesByRationRequest $request): JsonResponse
    {
        # get ingestion Ids
        $ingestionIds = Ingestion::whereIn('key', $request->mealtime)->pluck('id')->toArray();
        $conditions   = $request->filters;
        $user         = $request->user();

        // Favorite recipes conditions
        if (!empty($conditions) && key_exists('favorite', $conditions) && !empty($conditions['favorite'])) {
            $recipeFavorite         = $user->favorites()->pluck('recipe_id')->toArray();
            $conditions['favorite'] = $recipeFavorite;
        } else {
            $recipeFavorite = [];
            unset($conditions['favorite']);
        }

        $recipesCollection = $user
            ->allRecipes()
            ->with(['diets', 'price', 'complexity'])
            ->leftJoin('user_recipe_calculated', 'recipes.id', '=', 'user_recipe_calculated.recipe_id')
            ->leftJoin('ingestions', 'user_recipe_calculated.ingestion_id', '=', 'ingestions.id')
            ->select(
                'user_recipe_calculated.ingestion_id AS calc_ingestion_id',
                'user_recipe_calculated.recipe_data AS calc_recipe_data',
                'user_recipe_calculated.invalid AS calc_invalid',
                'user_recipe_calculated.updated_at AS calc_updated_at',
                'ingestions.key AS meal_time'
            )
            ->whereIn('user_recipe_calculated.ingestion_id', $ingestionIds)
            ->where(
                [
                    ['user_recipe_calculated.user_id', $user->id],
                    ['recipes.id', '!=', $request->recipe_id],
                    ['user_recipe_calculated.recipe_data', '!=', ''],
                    ['user_recipe_calculated.recipe_data', '!=', '[]'],
                    ['user_recipe_calculated.invalid', '=', 0]
                ]
            )
            ->searchBy($conditions)
            ->groupBy('recipes.id');

        //  TODO: check pagination for other types of collection data
        return response()->json(new RecipeSearchPreviewResource($recipesCollection->paginate(10)));
    }
}
