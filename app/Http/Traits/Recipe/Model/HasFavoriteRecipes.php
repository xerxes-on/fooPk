<?php

namespace App\Http\Traits\Recipe\Model;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasFavoriteRecipes
{
    /**
     * Users favourite recipes.
     */
    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'favorites')->withTimeStamps();
    }

    /**
     * Mark a recipe as favourite.
     * Returns boolean, whether any changes were performed.
     */
    public function setFavourite(Recipe $recipe): bool
    {
        $marked = $this->favorites()->where('recipe_id', $recipe->id)->exists();

        if ($marked) {
            return false;
        }

        $this->favorites()->attach($recipe);
        return true;
    }

    /**
     * Remove favourite mark from a recipe.
     * Returns boolean, whether any changes were performed.
     */
    public function unsetFavourite(Recipe $recipe): bool
    {
        $marked = $this->favorites()->whereIn('recipe_id', $recipe->relatedScope)->exists();

        if (!$marked) {
            return false;
        }

        $this->favorites()->detach($recipe->relatedScope);
        return true;
    }
}
