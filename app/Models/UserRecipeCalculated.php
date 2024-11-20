<?php

namespace App\Models;

use App\Events;
use App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * A recipe tailored for specific user and meal type (ingestion).
 *
 * @property int $user_id
 * @property $recipe_id
 * @property $custom_recipe_id
 * @property $ingestion_id
 * @property bool $invalid
 * @property array $recipe_data
 * @property-read \App\Models\User $user
 * @property-read \App\Models\Recipe $recipe
 * @property-read \App\Models\Ingestion $ingestion
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @package App\Models
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\UserRecipeCalculatedFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRecipeCalculated newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserRecipeCalculated newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserRecipeCalculated query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserRecipeCalculated whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRecipeCalculated whereCustomRecipeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRecipeCalculated whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRecipeCalculated whereIngestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRecipeCalculated whereInvalid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRecipeCalculated whereRecipeData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRecipeCalculated whereRecipeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRecipeCalculated whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRecipeCalculated whereUserId($value)
 * @mixin \Eloquent
 */
final class UserRecipeCalculated extends Model
{
    use HasFactory;

    protected $table = 'user_recipe_calculated';

    protected $fillable = [
        'user_id',
        'recipe_id',
        'custom_recipe_id',
        'ingestion_id',
        'invalid',
        'recipe_data'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'invalid'     => 'boolean',
        'recipe_data' => 'array',
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
     * User, for whom calculations are made.
     */
    public function user(): HasOne
    {
        return $this->hasOne(Models\User::class, 'id', 'user_id');
    }

    /**
     * Common recipe current one is based on.
     */
    public function recipe(): HasOne
    {
        return $this->hasOne(Models\Recipe::class, 'id', 'recipe_id');
    }

    /**
     * Ingestion the calculations are for.
     */
    public function ingestion(): HasOne
    {
        return $this->hasOne(Models\Ingestion::class, 'id', 'ingestion_id');
    }
}
