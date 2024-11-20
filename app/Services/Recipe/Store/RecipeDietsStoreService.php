<?php

declare(strict_types=1);

namespace App\Services\Recipe\Store;

use App\Contracts\Services\Recipe\RecipeRelationStoreInterface;
use App\Models\Recipe;

/**
 * Service for storing recipe category diets.
 *
 * @package App\Services\Recipe\Store
 */
final class RecipeDietsStoreService implements RecipeRelationStoreInterface
{
    public function store(Recipe $model, ?array $data = []): void
    {
        $model->diets()->detach();

        if (count((array)$data) > 0) {
            $model->diets()->attach($data);
        }
    }
}
