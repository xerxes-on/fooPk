<?php

declare(strict_types=1);

namespace App\Http\Controllers\Recipes;

use App\Exceptions\PublicException;
use App\Http\Controllers\Controller;
use App\Models\Diet;
use App\Models\Ingestion;
use App\Models\Recipe;
use App\Models\RecipeComplexity;
use App\Models\RecipePrice;
use App\Models\RecipeTag;
use App\Repositories\Recipes;
use App\Repositories\SeasonsRepository;
use App\Services\RecipeService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Recipes shop controller
 *
 * @package App\Http\Controllers\Recipes
 */
class RecipeShopController extends Controller
{
    /**
     * Render page with list of "Buy Recipes"
     *
     * @route GET /user/recipes/buy
     * @route POST /user/recipes/buy
     */
    public function recipesToBuy(Request $request, SeasonsRepository $seasonsRepo, Recipes $recipesRepo): Factory|View
    {
        return view(
            'recipes.allRecipes.toBuy',
            [
                'recipes' => $recipesRepo->getRecipesToBuy(
                    $request->user(),
                    $request->only(
                        [
                            'search_name',
                            'ingestion',
                            'complexity',
                            'cost',
                            'diet',
                            'seasons',
                            'favorite',
                            'recipe_tag'
                        ]
                    ),
                    (int)$request->get('per_page', 20)
                ),
                'ingestions'   => Ingestion::getAllActive()->pluck('title', 'id')->toArray(),
                'complexities' => RecipeComplexity::getAll()->pluck('title', 'id')->toArray(),
                'costs'        => RecipePrice::pluck('title', 'id')->toArray(),
                'diets'        => Diet::getAll()->pluck('name', 'id')->toArray(),
                'seasons'      => $seasonsRepo->getAll()->pluck('name', 'id')->toArray(),
                'favorites'    => ['favorite' => trans('common.favorite')],
                'tags'         => RecipeTag::publicOnly()
                    ->with('translations')
                    ->get()
                    ->map(fn(RecipeTag $tag) => ['id' => $tag->id, 'title' => $tag->title])
                    ->pluck('title', 'id')
                    ->toArray()
            ]
        );
    }

    /**
     * Method for buying recipe.
     *
     * @route POST /user/recipes/buying
     */
    public function buyingRecipes(Request $request, RecipeService $service): JsonResponse
    {
        if (!$request->has('recipeId')) {
            return response()->json(['success' => false, 'message' => 'Recipe ID Empty!']);
        }

        try {
            $recipe = Recipe::whereId((int)$request->get('recipeId'))->firstOrFail();
        } catch (ModelNotFoundException) {
            return response()->json(['success' => false, 'message' => 'Recipe not found!']);
        }

        try {
            $successMessage = $service->buy($request->user(), $recipe);
        } catch (PublicException $exception) {
            return response()->json(
                ['success' => false, 'message' => $exception->getMessage(), 'link' => config('adding-new-recipes.purchase_url')]
            );
        }

        return response()->json(['success' => true, 'message' => $successMessage]);
    }
}
