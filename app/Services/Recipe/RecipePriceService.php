<?php

declare(strict_types=1);

namespace App\Services\Recipe;

use App\Helpers\CacheKeys;
use App\Models\RecipeComplexity;
use App\Models\RecipePrice;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service providing data for RecipePrice.
 *
 * @package App\Services\Recipe
 */
final class RecipePriceService
{
    /**
     * Get all RecipeComplexity.
     *
     * @return Collection<array-key,RecipeComplexity>
     */
    public function getAll(): Collection
    {
        $complexity = \Cache::get(CacheKeys::allRecipePrice());

        if (!empty($complexity)) {
            return $complexity;
        }
        $complexity = RecipePrice::get();

        \Cache::put(CacheKeys::allRecipePrice(), $complexity, config('cache.lifetime_10m'));

        return $complexity;
    }
}
