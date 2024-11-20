<?php

namespace App\Http\Controllers\Recipes;

use App\Enums\Recipe\RecipeTypeEnum;
use App\Events\RecipeProcessed;
use App\Http\Controllers\Controller;
use App\Http\Requests\Recipe\CookRecipeRequest;
use App\Http\Requests\Recipe\RemoveRecipeFromCookedRequest;
use App\Models\UserRecipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Recipes cooking status controller.
 *
 * @package App\Http\Controllers\Recipes
 */
class RecipeCookingPlannerController extends Controller
{
    /**
     * Mark as cooked and set rating.
     *
     * @route POST /user/recipes/to-cook
     */
    public function toCookRecipe(CookRecipeRequest $request): RedirectResponse|JsonResponse
    {
        $requestJson = $request->expectsJson();
        try {
            $where = match (RecipeTypeEnum::tryFrom($request->recipeType)) {
                RecipeTypeEnum::ORIGINAL => 'recipe_id',
                RecipeTypeEnum::CUSTOM   => 'custom_recipe_id',
                RecipeTypeEnum::FLEXMEAL => 'flexmeal_id',
                default                  => throw new InvalidArgumentException('Unknown recipe type'),
            };
        } catch (InvalidArgumentException $e) {
            return $requestJson ?
                response()->json(
                    [
                        'success' => false,
                        'message' => $e->getMessage(),
                        'errors'  => ['recipeType' => $e->getMessage()]
                    ]
                ) :
                back()->withErrors($e->getMessage());
        }

        $user = $request->user();
        UserRecipe::where(
            [
                ['user_id', $user->id],
                [$where, $request->recipe],
                ['meal_time', $request->mealtime],
                ['meal_date', 'like', $request->date->format('Y-m-d') . '%'] // TODO: probably needs to be fixed meal_date range
            ]
        )
            ->update(['cooked' => 1]);

        RecipeProcessed::dispatch();

        // TODO: maybe set rating only to original recipes?
        $rating = DB::table('ratings')
            ->where(
                [
                    ['user_id', $user->id],
                    ['recipe_id', $request->recipe],
                ]
            )
            ->first();

        if (empty($rating)) {
            $user->ratings()
                ->attach(
                    [
                        [
                            'recipe_id' => $request->recipe,
                            'rating'    => $request->rate,
                        ]
                    ]
                );
        } else {
            DB::table('ratings')
                ->where(
                    [
                        ['user_id', $user->id],
                        ['recipe_id', $request->recipe],
                    ]
                )
                ->update(['rating' => $request->rate]);
        }

        return $requestJson ?
            response()->json(
                [
                    'success' => true,
                    'message' => trans('common.success'),
                    'errors'  => null
                ]
            ) : back();
    }

    /**
     * Unmark as cooked.
     *
     * @route POST /user/recipes/uncook
     */
    public function unCookRecipe(RemoveRecipeFromCookedRequest $request): RedirectResponse|JsonResponse
    {
        $requestJson = $request->expectsJson();
        try {
            $where = match (RecipeTypeEnum::tryFrom($request->recipeType)) {
                RecipeTypeEnum::ORIGINAL => 'recipe_id',
                RecipeTypeEnum::CUSTOM   => 'custom_recipe_id',
                RecipeTypeEnum::FLEXMEAL => 'flexmeal_id',
                default                  => throw new InvalidArgumentException('Unknown recipe type'),
            };
        } catch (InvalidArgumentException $e) {
            return $requestJson ?
                response()->json(
                    [
                        'success' => false,
                        'message' => $e->getMessage(),
                        'errors'  => ['recipeType' => $e->getMessage()]
                    ]
                ) :
                back()->withErrors($e->getMessage());
        }
        UserRecipe::where(
            [
                ['user_id', $request->user()->id],
                [$where, $request->recipe],
                ['meal_date', 'like', $request->date->format('Y-m-d') . '%'] // TODO: probably needs to be fixed meal_date range
            ]
        )->update(['cooked' => 0]);

        RecipeProcessed::dispatch();

        return $requestJson ?
            response()->json(
                [
                    'success' => true,
                    'message' => trans('common.success'),
                    'errors'  => null
                ]
            ) : back();
    }
}
