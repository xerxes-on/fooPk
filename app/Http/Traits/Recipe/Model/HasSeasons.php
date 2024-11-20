<?php

declare(strict_types=1);

namespace App\Http\Traits\Recipe\Model;

use App\Models\Seasons;
use App\Repositories\SeasonsRepository;
use App\Services\Recipe\Store\RecipeSeasonsStoreService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection as SupportCollection;

trait HasSeasons
{
    use CanCollectRecipeSeasons;

    public function seasons(): BelongsToMany
    {
        return $this->belongsToMany(Seasons::class, 'recipes_to_seasons');
    }

    /**
     * Temporarily added method to get true seasons from ingredients
     * @internal
     */
    public function getSeasonIds()
    {
        $commonValues = $this->collectRecipeSeasonsIds();

        if ($commonValues === []) {
            return collect();
        }

        if (in_array(0, $commonValues, true)) {
            return collect([['id' => 0, 'name' => __('common.any')]]);
        }

        return app(SeasonsRepository::class)->getAll()->whereIn('id', array_values($commonValues));
    }

    public function getSeasonsAttribute(): SupportCollection|EloquentCollection
    {
        return $this->relationLoaded('seasons') ? $this->getRelation('seasons') : $this->seasons()->get();
    }

    public function saveRecipeSeasons(?array $data = []): self
    {
        app(RecipeSeasonsStoreService::class)->store($this, $data);
        return $this;
    }
}
