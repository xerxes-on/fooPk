<?php

namespace App\Models;

use App\Events;
use Illuminate\Database\Eloquent\Model;
use Modules\Ingredient\Models\Ingredient;

/**
 * Class RecipeIngredient
 *
 * @package App\Models
 * @property int $recipe_id
 * @property int $ingredient_id
 * @property float $amount
 * @property-read \Modules\Ingredient\Models\Ingredient|null $ingredient
 * @property-read \App\Models\Recipe|null $recipe
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeIngredient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeIngredient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeIngredient query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeIngredient whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeIngredient whereIngredientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeIngredient whereRecipeId($value)
 * @mixin \Eloquent
 */
class RecipeIngredient extends Model
{
    protected $table = 'recipe_ingredients';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'recipe_id',
        'ingredient_id',
        'amount'
    ];

    /**
     * The event map for the model.
     *
     * Allows for object-based events for native Eloquent events.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'saved'   => Events\RecipeProcessed::class,
        'deleted' => Events\RecipeProcessed::class,
    ];

    /**
     * relation get Recipe
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function recipe()
    {
        return $this->hasOne(Recipe::class, 'id', 'recipe_id');
    }

    /**
     * relation get Ingredient
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ingredient()
    {
        return $this->hasOne(Ingredient::class, 'id', 'ingredient_id');
    }
}
