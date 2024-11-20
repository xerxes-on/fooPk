<?php

declare(strict_types=1);

namespace App\Http\Traits\Recipe\Model;

use App\Models\Diet;
use App\Services\Recipe\Calculation\RecipeDietCalculationService;
use App\Services\Recipe\Store\RecipeDietsStoreService;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasDiets
{
    /**
     * relation get Diets
     */
    public function diets(): BelongsToMany
    {
        return $this->belongsToMany(Diet::class, 'recipes_to_diets');
    }

    public function saveRecipeDietCategory(array $diets = []): self
    {
        app(RecipeDietsStoreService::class)->store($this, $diets);
        return $this;
    }

    /**
     * Calculate recipe diets from recipe ingredients
     */
    public static function calculateRecipeDiets(array $ingredientIds = [], bool $extended = false): array
    {
        return app(RecipeDietCalculationService::class)->calculate($ingredientIds, $extended);
    }
}
