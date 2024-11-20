<?php

declare(strict_types=1);

namespace App\Services\Recipe\Store;

use App\Contracts\Services\Recipe\RecipeRelationStoreInterface;
use App\Models\Recipe;

/**
 * Service for storing recipe variable ingredients.
 *
 * @package App\Services\Recipe\Store
 */
final class RecipeVariableIngredientsStoreService implements RecipeRelationStoreInterface
{
    public function store(Recipe $model, ?array $data = []): void
    {
        $model->variableIngredients()->detach();

        if (empty($data)) {
            return;
        }

        $newIngredients = [];
        foreach ($data as $ingredient) {
            $ingredientId = null;

            if (isset($ingredient['ingredient_id']) && $ingredient['ingredient_id'] != '0') {
                $ingredientId = $ingredient['ingredient_id'];
            }

            $newIngredients[] = [
                'ingredient_id'          => $ingredientId,
                'ingredient_category_id' => $ingredient['category_id']
            ];
        }

        $model->variableIngredients()->attach($newIngredients);
    }
}
