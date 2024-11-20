<?php

declare(strict_types=1);

namespace App\Services\Recipe\Store;

use App\Contracts\Services\Recipe\RecipeRelationStoreInterface;
use App\Http\Traits\Recipe\Model\CanCollectRecipeSeasons;
use App\Models\Recipe;

/**
 * Service for storing recipe seasons.
 *
 * @package App\Services\Recipe\Store
 */
final class RecipeSeasonsStoreService implements RecipeRelationStoreInterface
{
    use CanCollectRecipeSeasons;

    public function store(Recipe $model, ?array $data = []): void
    {
        $commonValues = $model->collectRecipeSeasonsIds();

        if ($commonValues === []) {
            return;
        }

        sort($commonValues);

        $model->seasons()->sync($commonValues);
    }
}
