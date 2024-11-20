<?php

declare(strict_types=1);

namespace App\Services\Recipe\Store;

use App\Contracts\Services\Recipe\RecipeRelationStoreInterface;
use App\Models\Recipe;

/**
 * Service for storing recipe ingredients
 *
 * @package App\Services\Recipe\Store
 */
final class RecipeIngredientsStoreService implements RecipeRelationStoreInterface
{
    public function store(Recipe $model, ?array $data = []): void
    {
        $model->ingredients()->detach();

        if (empty($data)) {
            return;
        }

        // TODO: investigate why variable is referenced and not reassigned
        foreach ($data as &$ingredient) {
            if (is_null($ingredient['amount'])) {
                $ingredient['amount'] = 0;
            }
        }

        $model->ingredients()->attach($data);
    }
}
