<?php

declare(strict_types=1);

namespace App\Http\Traits\Recipe\Model;

use App\Services\Recipe\Store\RecipeRelationStoreService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection as SupportCollection;

trait HasRelatedRecipes
{
    public function getRelatedScopeAttribute(): array
    {
        return array_map('intval', [$this->id, ...($this->related_recipes ?? [])]);
    }

    public function getRelatedAttribute(): EloquentCollection|SupportCollection|null
    {
        return empty($this->related_recipes) ? null : self::whereIn('id', $this->related_recipes)->get();
    }

    public function saveRelatedRecipes(?array $relatedRecipes = null): self
    {
        app(RecipeRelationStoreService::class)->store($this, $relatedRecipes);
        return $this;
    }
}
