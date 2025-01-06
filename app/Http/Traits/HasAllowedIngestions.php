<?php

declare(strict_types=1);

namespace App\Http\Traits;

use App\Helpers\CacheKeys;
use App\Models\Ingestion;
use App\Services\IngestionService;
use Illuminate\Database\Eloquent\Collection;

trait HasAllowedIngestions
{
    public function getAllowedIngestionKeysAttribute(): array
    {
        return array_keys(array_filter($this->dietdata['ingestion'], static fn(array $ingestion) => (int)$ingestion['percents'] !== 0));
    }

    public function getAllowedIngestionIdsAttribute(): array
    {
        return $this->allowed_ingestions->pluck('id')->toArray();
    }

    /**
     * @return Collection<int, Ingestion>
     */
    public function getAllowedIngestionsAttribute(): Collection
    {
        $data = \Cache::get(CacheKeys::userAllowedIngestions($this->id));
        if (!empty($data)) {
            return $data;
        }
        $data = app(IngestionService::class)->getFilteredIngestions($this->allowed_ingestion_keys);
        \Cache::put(CacheKeys::userAllowedIngestions($this->id), $data, config('cache.lifetime_10m'));

        return $data;
    }
}
