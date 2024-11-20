<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Custom (user managed) category of a recipe.
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\CustomRecipeCategoryFactory factory(...$parameters)
 * @method static Builder|CustomRecipeCategory newModelQuery()
 * @method static Builder|CustomRecipeCategory newQuery()
 * @method static Builder|CustomRecipeCategory query()
 * @method static Builder|CustomRecipeCategory whereId($value)
 * @method static Builder|CustomRecipeCategory whereName($value)
 * @method static Builder|CustomRecipeCategory whereUserId($value)
 * @mixin \Eloquent
 */
final class CustomRecipeCategory extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = ['user_id', 'name'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'custom_recipe_categories';

    /**
     * All categories are displayed in alphabetical order.
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(
            'order',
            function (Builder $builder) {
                $builder->orderBy('name', 'asc');
            }
        );
    }

    /**
     * A user who manages current category.
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
