<?php

namespace Modules\Ingredient\Services;

use App\Helpers\CacheKeys;
use Illuminate\Support\Collection;
use Modules\Ingredient\Models\Ingredient;

/**
 * Service allowing to access general ingredients' data.
 *
 * @package Modules\Ingredient\Services
 */
final class IngredientService
{
    /**
     * Get main ingredient categories.
     *
     * @return Collection<array-key,Ingredient>
     */
    public function getAll(): Collection
    {
        $ingredients = \Cache::get(CacheKeys::allIngredients());

        if (!empty($ingredients)) {
            return $ingredients;
        }
        $ingredients = Ingredient::get();

        \Cache::put(CacheKeys::allIngredients(), $ingredients, config('cache.lifetime_10m'));

        return $ingredients;
    }

    public function getIds(): array
    {
        $ids = \Cache::get(CacheKeys::allIngredientIds());

        if (!empty($ids)) {
            return $ids;
        }

        $ids = Ingredient::pluck('id')->toArray();

        \Cache::put(CacheKeys::allIngredientIds(), $ids, config('cache.lifetime_10m'));
        return $ids;
    }
}
