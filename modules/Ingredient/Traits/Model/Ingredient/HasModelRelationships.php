<?php

namespace Modules\Ingredient\Traits\Model\Ingredient;

use App\Models\Recipe;
use App\Models\Seasons;
use App\Models\Vitamin;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Ingredient\Models\IngredientCategory;
use Modules\Ingredient\Models\IngredientHint;
use Modules\Ingredient\Models\IngredientTag;
use Modules\Ingredient\Models\IngredientUnit;

trait HasModelRelationships
{
    public function category(): HasOne
    {
        return $this->hasOne(IngredientCategory::class, 'id', 'category_id');
    }

    public function recipesAsStatic(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'recipe_ingredients')->withPivot('amount');
    }

    public function recipesAsVariable(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'recipe_variable_ingredients')->withPivot('ingredient_category_id');
    }

    public function diets(): HasOne|BelongsToMany
    {
        // This workaround was made to prevent getting diets on null category, which breaks application.
        $category = $this?->category;
        return is_null($category) ? $this->category() : $category->diets();
    }

    public function unit(): HasOne
    {
        return $this->hasOne(IngredientUnit::class, 'id', 'unit_id');
    }

    public function alternativeUnit(): HasOne
    {
        return $this->hasOne(IngredientUnit::class, 'id', 'alternative_unit_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(IngredientTag::class, 'ingredient_to_tags', 'ingredient_id');
    }

    public function hint(): HasOne
    {
        return $this->hasOne(IngredientHint::class);
    }

    public function vitamins(): BelongsToMany
    {
        return $this->belongsToMany(Vitamin::class, 'ingredient_vitamins')->withPivot('value');
    }

    public function seasons(): BelongsToMany
    {
        return $this->belongsToMany(Seasons::class, 'ingredient_seasons', 'ingredient_id', 'season_id');
    }
}
