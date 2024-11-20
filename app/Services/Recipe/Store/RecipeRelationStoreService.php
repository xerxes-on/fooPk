<?php

declare(strict_types=1);

namespace App\Services\Recipe\Store;

use App\Contracts\Services\Recipe\RecipeRelationStoreInterface;
use App\Models\Recipe;

/**
 * Service for storing related recipes
 * TODO: high cyclomatic complexity, need to refactor and optimize
 * @package App\Services\Recipe\Store
 */
final class RecipeRelationStoreService implements RecipeRelationStoreInterface
{
    public function store(Recipe $model, ?array $data = []): void
    {
        # get relative recipes for save
        $data = (array)$data;
        $key  = array_search($model->id, $data);
        if ($key !== false) {
            unset($data[$key]);
        }

        # get current relative recipes
        $_relatedRecipes = empty($model->related_recipes) ? [] : $model->related_recipes;
        $currentRecipeId = $model->id;

        // data processing for IDs which are removed
        # different IDs for remove
        $diffRm = array_diff($_relatedRecipes, $data);

        if ($diffRm !== []) {
            // TODO: for performance its better to find all recipes in one query, avoid query in a loop
            foreach ($diffRm as $itemId) {
                $recipe = Recipe::find($itemId);
                if (empty($recipe)) {
                    continue;
                }

                $key = array_search($currentRecipeId, $recipe->related_recipes);
                if (!empty($recipe->related_recipes) && $key !== false) {
                    $recipeRelatedRecipes = $recipe->related_recipes;
                    unset($recipeRelatedRecipes[$key]);

                    $recipeRelatedRecipes = array_values(array_map('strval', $recipeRelatedRecipes));
                    sort($recipeRelatedRecipes);

                    $recipe->related_recipes = $recipeRelatedRecipes;
                    $recipe->save();
                }
            }
        }

        // data processing for IDs which are added
        # different IDs for adding
        $diffAdd = array_diff($data, $_relatedRecipes);

        if ($diffAdd !== []) {
            foreach ($diffAdd as $itemId) {
                $recipe = Recipe::find($itemId);
                if (empty($recipe)) {
                    continue;
                }

                $recipeRelatedRecipes = $recipe->related_recipes;
                if (empty($recipeRelatedRecipes)) {
                    $recipeRelatedRecipes = [];
                }

                $recipeRelatedRecipes[] = $currentRecipeId;
                $recipeRelatedRecipes   = array_values(array_map('strval', $recipeRelatedRecipes));
                sort($recipeRelatedRecipes);

                $recipe->related_recipes = $recipeRelatedRecipes;
                $recipe->save();
            }
        }

        # reindex array and convert to string
        $model->related_recipes = array_values(array_map('strval', $data));
        $model->save();
    }
}
