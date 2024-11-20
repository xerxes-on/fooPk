<?php

declare(strict_types=1);

namespace App\Http\Traits\Recipe\Model;

use App\Models\RecipeTag;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasTags
{
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(RecipeTag::class, 'recipes_to_tags', 'recipe_id');
    }

    public function publicTags(): BelongsToMany
    {
        return $this->belongsToMany(RecipeTag::class, 'recipes_to_tags', 'recipe_id')->where('filter', 1);
    }
}
