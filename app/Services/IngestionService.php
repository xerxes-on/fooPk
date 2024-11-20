<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\CacheKeys;
use App\Models\Ingestion;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service providing ingestion data.
 *
 * @package App\Services
 */
final class IngestionService
{
    /**
     * Get all ingestions.
     *
     * @return Collection<array-key,Ingestion>
     */
    public function getAll(): Collection
    {
        $ingestions = \Cache::get(CacheKeys::allIngestions());

        if (!empty($ingestions)) {
            return $ingestions;
        }
        $ingestions = Ingestion::get();

        \Cache::put(CacheKeys::allIngestions(), $ingestions, config('cache.lifetime_10m'));

        return $ingestions;
    }

    /**
     * Get all (active) ingestions.
     *
     * @return Collection<array-key,Ingestion>
     */
    public function getAllActive(): Collection
    {
        $ingestions = \Cache::get(CacheKeys::allActiveIngestions());

        if (!empty($ingestions)) {
            return $ingestions;
        }
        $ingestions = Ingestion::active()->get();

        \Cache::put(CacheKeys::allActiveIngestions(), $ingestions, config('cache.lifetime_10m'));

        return $ingestions;
    }
}
