<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Ingredient\Models\Ingredient;
use Modules\Ingredient\Models\IngredientCategory;

/**
 * Class RecipeVariableIngredient
 *
 * @package App\Models
 * @property int $id
 * @property int $recipe_id
 * @property int|null $ingredient_id
 * @property int $ingredient_category_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\Ingredient\Models\Ingredient|null $ingredient
 * @property-read \Modules\Ingredient\Models\IngredientCategory|null $ingredient_category
 * @property-read \App\Models\Recipe|null $recipe
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeVariableIngredient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeVariableIngredient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeVariableIngredient query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeVariableIngredient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeVariableIngredient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeVariableIngredient whereIngredientCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeVariableIngredient whereIngredientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeVariableIngredient whereRecipeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeVariableIngredient whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RecipeVariableIngredient extends Model
{
    protected $table = 'recipe_variable_ingredients';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'recipe_id',
        'ingredient_id',
        'ingredient_category_id'
    ];

    /**
     * relation get Ingredient
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ingredient()
    {
        return $this->hasOne(Ingredient::class, 'id', 'ingredient_id');
    }

    /**
     * relation get ingredient category
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ingredient_category()
    {
        return $this->hasOne(IngredientCategory::class, 'id', 'ingredient_category_id');
    }

    /**
     * relation get Recipe
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function recipe()
    {
        return $this->hasOne(Recipe::class, 'id', 'recipe_id');
    }
}
