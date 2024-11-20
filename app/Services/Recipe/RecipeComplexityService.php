<?php

declare(strict_types=1);

namespace App\Services\Recipe;

use App\Helpers\CacheKeys;
use App\Models\RecipeComplexity;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service providing data for RecipeComplexity.
 *
 * @package App\Services\Recipe
 */
final class RecipeComplexityService
{
    /**
     * Get all RecipeComplexity.
     *
     * @return Collection<array-key,RecipeComplexity>
     */
    public function getAll(): Collection
    {
        $complexity = \Cache::get(CacheKeys::allRecipeComplexity());

        if (!empty($complexity)) {
            return $complexity;
        }
        $complexity = RecipeComplexity::get();

        \Cache::put(CacheKeys::allRecipeComplexity(), $complexity, config('cache.lifetime_10m'));

        return $complexity;
    }
}
