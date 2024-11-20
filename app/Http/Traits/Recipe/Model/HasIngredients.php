<?php

declare(strict_types=1);

namespace App\Http\Traits\Recipe\Model;

use App\Services\Recipe\Store\RecipeIngredientsStoreService;
use App\Services\Recipe\Store\RecipeVariableIngredientsStoreService;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Ingredient\Models\Ingredient;

trait HasIngredients
{
    /**
     * relation for ingredients
     */
    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'recipe_ingredients')->withPivot('amount');
    }

    /**
     * relation for variable Ingredients
     */
    public function variableIngredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'recipe_variable_ingredients')
            ->withPivot('ingredient_category_id');
    }

    public function saveRecipeIngredients(?array $ingredients = null): self
    {
        app(RecipeIngredientsStoreService::class)->store($this, $ingredients);

        return $this;
    }

    public function saveRecipeVariableIngredients(?array $ingredients = null): self
    {
        app(RecipeVariableIngredientsStoreService::class)->store($this, $ingredients);

        return $this;
    }
}
