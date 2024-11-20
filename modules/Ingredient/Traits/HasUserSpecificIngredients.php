<?php

namespace Modules\Ingredient\Traits;

use App\Services\Users\UserAllowedIngredientsService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Ingredient\Models\Ingredient;
use Modules\Ingredient\Services\UserExcludedIngredientsSyncService;
use Modules\Ingredient\Services\UserIngredientGeneratorService;
use Modules\Ingredient\Services\UserProhibitedIngredientsSyncService;

/**
 * User model designated trait.
 */
trait HasUserSpecificIngredients
{
    /**
     * User excluded recipes relation
     */
    public function excludedIngredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'user_excluded_ingredients');
    }

    public function saveExcludedIngredients(?array $ingredients = []): void
    {
        app(UserExcludedIngredientsSyncService::class)->store($this, $ingredients);
    }

    public function prohibitedIngredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'user_prohibited_ingredients');
    }

    public function saveProhibitedIngredients(array $ingredients = []): void
    {
        app(UserProhibitedIngredientsSyncService::class)->syncForbidden($this, $ingredients);
    }

    /**
     * Get user allowed ingredient ids
     */
    public function getGeneratedAllowedIngredientsList(): array
    {
        return app(UserIngredientGeneratorService::class)->getUserAllowedIngredientIds($this);
    }

    public function getAllowedIngredients(): Collection
    {
        return app(UserAllowedIngredientsService::class)->getAppropriate($this);
    }
}
