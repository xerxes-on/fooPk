<?php

declare(strict_types=1);

namespace App\Http\Traits\Recipe\Model;

use App\Models\RecipeStep;
use App\Services\Recipe\Store\RecipeStepsStoreService;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasSteps
{
    public function steps(): HasMany
    {
        return $this->hasMany(RecipeStep::class, 'recipe_id', 'id');
    }

    public function saveRecipeSteps($recipeSteps): self
    {
        app(RecipeStepsStoreService::class)->store($this, $recipeSteps);

        return $this;
    }

    public function syncRecipeSteps(array $recipeSteps): self
    {
        app(RecipeStepsStoreService::class)->syncRecipeSteps($this, $recipeSteps);

        return $this;
    }
}
