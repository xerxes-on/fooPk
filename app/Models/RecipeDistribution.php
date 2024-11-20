<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\RecipeDistribution
 *
 * @property int $id
 * @property array $recipes Array of recipe IDs to be distributed
 * @property string|null $comment Admin comment for the distribution
 * @property int $is_distributed Flag to indicate if the recipes have been distributed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeDistribution newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeDistribution newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeDistribution query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeDistribution whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeDistribution whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeDistribution whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeDistribution whereIsDistributed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeDistribution whereRecipes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeDistribution whereUpdatedAt($value)
 * @mixin \Eloquent
 */
final class RecipeDistribution extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'monthly_recipe_distributions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = ['recipes', 'comment'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'recipes' => 'array',
    ];
}
