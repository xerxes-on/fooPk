<?php

namespace App\Http\Controllers\Recipes;

use App\Events\RecipeProcessed;
use App\Http\Controllers\Controller;
use App\Models\Recipe;
use Auth;
use Illuminate\Http\RedirectResponse;

/**
 * Controller for marking recipes as favourite
 *
 * TODO: maybe make a service and move logic there leaving recipes controller to take care of routing
 *
 * @package App\Http\Controllers
 */
class FavoriteController extends Controller
{
    /**
     * Favourite a recipe
     *
     * @route POST /user/recipes/favorite/{recipe}
     */
    final public function favoriteRecipe(Recipe $recipe): RedirectResponse
    {
        Auth::user()->favorites()->attach($recipe->id);
        RecipeProcessed::dispatch();
        return back();
    }

    /**
     * Remove recipe from Favourites
     *
     * @route POST /user/recipes/unfavorite/{recipe}
     */
    final public function unFavoriteRecipe(Recipe $recipe): RedirectResponse
    {
        Auth::user()->favorites()->detach($recipe->relatedScope);
        RecipeProcessed::dispatch();
        return back();
    }
}
