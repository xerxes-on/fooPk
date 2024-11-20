<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\CacheKeys;
use App\Models\Diet;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service providing data for Diets.
 *
 * @package App\Services
 */
final class DietService
{
    /**
     * Get all diets.
     *
     * @return Collection<array-key,Diet>
     */
    public function getAll(): Collection
    {
        $diets = \Cache::get(CacheKeys::diets());

        if (!empty($diets)) {
            return $diets;
        }
        $diets = Diet::get();

        \Cache::put(CacheKeys::diets(), $diets, config('cache.lifetime_10m'));

        return $diets;
    }
}
