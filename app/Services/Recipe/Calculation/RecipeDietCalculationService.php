<?php

namespace App\Services\Recipe\Calculation;

use Modules\Ingredient\Models\Ingredient;

/**
 * Service for calculating the diets of the ingredients.
 *
 * @package App\Services\Recipe
 */
final class RecipeDietCalculationService
{
    /**
     * Calculate the diets of the ingredients
     * TODO: high cyclomatic complexity
     * TODO: better make a generator
     */
    public function calculate(array $ingredientIds = [], bool $extended = false): array
    {
        $ingredients = Ingredient::ofIds($ingredientIds)->withOnly('category.diets')->get();

        $diets            = [];
        $ingredientsCount = (int)$ingredients?->count();
        foreach ($ingredients as $ingredient) {
            if ($ingredient->category->diets->count() === 0) {
                continue;
            }
            foreach ($ingredient->category->diets as $diet) {
                if (!isset($diets['diet_' . $diet->id])) {
                    if ($extended) {
                        $diets['diet_' . $diet->id] = [
                            'id'    => $diet->id,
                            'name'  => ucwords($diet->name),
                            'count' => 0
                        ];
                    } else {
                        $diets['diet_' . $diet->id] = [
                            'diet_id' => $diet->id,
                            'count'   => 0
                        ];
                    }
                }

                $diets['diet_' . $diet->id]['count'] += 1;
            }
        }

        if ($extended) {
            $totalDiets = [];

            foreach ($diets as $diet) {
                if ($diet['count'] === $ingredientsCount) {
                    $totalDiets[] = $diet;
                }
            }

            return $totalDiets;
        }

        $trueDiets = [];

        foreach ($diets as $diet) {
            if ($diet['count'] === $ingredientsCount) {
                $trueDiets[] = ['diet_id' => $diet['diet_id']];
            }
        }

        return $trueDiets;
    }
}
