<?php

declare(strict_types=1);

namespace App\Http\Traits\Recipe\Model;

use App\Models\Ingestion;
use App\Services\Recipe\Store\RecipeIngestionStoreService;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasIngestions
{
    public function ingestions(): BelongsToMany
    {
        return $this->belongsToMany(Ingestion::class, 'recipes_to_ingestions');
    }

    public function saveRecipeIngestions(?array $ingestions = null): self
    {
        app(RecipeIngestionStoreService::class)->store($this, $ingestions);

        return $this;
    }

    public function getIngestionsAttribute()
    {
        return $this->relationLoaded('ingestions') ? $this->getRelation('ingestions') : $this->ingestions()->get();
    }
}
