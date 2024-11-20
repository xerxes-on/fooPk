<?php

namespace App\Services\Users;

use App\Models\Recipe;
use App\Models\User;
use DB;

final class UserExcludedRecipeStoreService
{
    public function store(User $user, array $excludedRecipes = []): void
    {
        // If nothing is passed it is considered that all records should be deleted
        if ($excludedRecipes === []) {
            DB::table('user_excluded_recipes')->where('user_id', $user->id)->delete();
            return;
        }

        // Check if requested recipes have related ones
        $relatedRecipesIds = Recipe::whereIn('id', $excludedRecipes)
            ->whereNotNull('related_recipes')
            ->pluck('related_recipes')
            ->flatten()
            ->toArray();

        if ($relatedRecipesIds !== []) {
            $excludedRecipes = array_unique(array_merge($relatedRecipesIds, $excludedRecipes));
            $excludedRecipes = array_map('intval', $excludedRecipes);
        }

        $user->excludedRecipes()->sync($excludedRecipes);
    }
}
