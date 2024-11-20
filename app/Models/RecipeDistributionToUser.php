<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

/**
 * Recipe Distribution To User model
 *
 * @property int $id
 * @property int $user_id
 * @property int $distribution_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static Builder|RecipeDistributionToUser newModelQuery()
 * @method static Builder|RecipeDistributionToUser newQuery()
 * @method static Builder|RecipeDistributionToUser query()
 * @method static Builder|RecipeDistributionToUser whereCreatedAt($value)
 * @method static Builder|RecipeDistributionToUser whereDistributionId($value)
 * @method static Builder|RecipeDistributionToUser whereId($value)
 * @method static Builder|RecipeDistributionToUser whereUpdatedAt($value)
 * @method static Builder|RecipeDistributionToUser whereUserId($value)
 * @mixin \Eloquent
 */
final class RecipeDistributionToUser extends Model
{
    use Prunable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_to_monthly_recipe_distribution';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = ['user_id', 'distribution_id'];

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return self::where('created_at', '<=', now()->subDays(40));
    }
}
