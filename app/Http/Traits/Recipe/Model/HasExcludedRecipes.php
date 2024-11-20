<?php

namespace App\Http\Traits\Recipe\Model;

use App\Models\Recipe;
use App\Services\Users\UserExcludedRecipeStoreService;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasExcludedRecipes
{
    /**
     * Excluded recipes relation.
     */
    public function excludedRecipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'user_excluded_recipes');
    }

    /**
     * Save user Excluded Recipes
     */
    public function saveExcludedRecipes(array $excludedRecipes = []): void
    {
        app(UserExcludedRecipeStoreService::class)->store($this, $excludedRecipes);
    }
}
