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

    /**
     * Get filtered ingestions from cached data.
     *
     * @return Collection<array-key,Ingestion>
     */
    public function getFilteredIngestions(array $filterData): Collection
    {
        return $this->getAll()->filter(static fn(Ingestion $ingestion) => in_array($ingestion->key, $filterData, true));
    }

    /** * Get specific ingestion(s) * *
     * @param  int|string|array  $ids  * @return null|Ingestion|Collection<array-key,Ingestion>
     * @return Ingestion|Collection|null
     */
    public function getSpecific(int|string|array $ids): null|Ingestion|Collection
    {
        if (is_array($ids)) {
            return $this->getAll()->whereIn('id', $ids);
        }
        if (is_string($ids)) {
            return $this->getAll()->firstWhere('key', $ids);
        }
        return $this->getAll()->firstWhere('id', $ids);
    }
}
