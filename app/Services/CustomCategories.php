<?php

namespace App\Services;

use App\Exceptions\CategoryAttached;
use App\Models\{CustomRecipeCategory, Recipe, User};
use Illuminate\Support\Facades\DB;

/**
 * Service for custom recipe categories.
 *
 * @package App\Services
 */
final class CustomCategories
{
    /**
     * Add a category to a recipe.
     *
     * @throws \App\Exceptions\CategoryAttached
     */
    public function addToRecipe(User $user, string $name, Recipe $recipe): CustomRecipeCategory
    {
        $category = $user->customRecipeCategories->where('name', $name)->first();

        if (is_null($category)) {
            $category = CustomRecipeCategory::create(['user_id' => $user->id, 'name' => $name]);
        } elseif ($recipe->customCategories($user)->where('category_id', $category->id)->exists()) {
            throw new CategoryAttached(trans('common.category_already_attached', ['name' => $category->name]));
        }

        $recipe->customCategories($user)->syncWithoutDetaching([$category->id]);
        return $category;
    }

    /**
     * Delete custom category.
     */
    public function delete(CustomRecipeCategory $category): void
    {
        DB::table('recipes_to_custom_categories')->where('category_id', $category->id)->delete();
        $category->delete();
    }

    /**
     * Change custom category.
     */
    public function edit(CustomRecipeCategory $category, string $newName): ?CustomRecipeCategory
    {
        $category->name = trim($newName);
        $category->save();

        return $category->fresh();
    }
}
