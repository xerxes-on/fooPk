<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\CacheKeys;
use App\Models\Diet;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

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
        // Use Cache::remember to avoid duplication:
        return Cache::remember(
            CacheKeys::diets(),
            config('cache.lifetime_10m'),
            function () {
                return Diet::with('translations')->get();
            }
        );
    }
}
