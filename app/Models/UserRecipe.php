<?php

declare(strict_types=1);

namespace App\Models;

use App\Events;
use App\Services\UserRecipe\RandomUserRecipeGeneratorService;
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\FlexMeal\Models\FlexmealLists;

/**
 * Planned meal.
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $recipe_id
 * @property int|null $custom_recipe_id
 * @property int $original_recipe_id
 * @property string|null $meal_date
 * @property string $meal_time
 * @property int $ingestion_id
 * @property int $cooked
 * @property int $eat_out
 * @property int|null $flexmeal_id
 * @property string $created_at
 * @property string $updated_at
 * @property-read CustomRecipe|null $customRecipe
 * @property-read FlexmealLists|null $flexmeal
 * @property-read UserRecipeCalculated|null $calculated_recipe
 * @property-read Recipe|null $original_recipe
 * @property-read Ingestion|null $ingestion
 * @property-read Recipe|null $recipe
 * @property-read UserRecipeCalculated|null $recipeCalculations
 * @property-read User|null $user
 * @method static Builder|UserRecipe newModelQuery()
 * @method static Builder|UserRecipe newQuery()
 * @method static Builder|UserRecipe query()
 * @method static Builder|UserRecipe whereCooked($value)
 * @method static Builder|UserRecipe whereCreatedAt($value)
 * @method static Builder|UserRecipe whereCustomRecipeId($value)
 * @method static Builder|UserRecipe whereEatOut($value)
 * @method static Builder|UserRecipe whereFlexmealId($value)
 * @method static Builder|UserRecipe whereId($value)
 * @method static Builder|UserRecipe whereIngestionId($value)
 * @method static Builder|UserRecipe whereMealDate($value)
 * @method static Builder|UserRecipe whereMealTime($value)
 * @method static Builder|UserRecipe whereOriginalRecipeId($value)
 * @method static Builder|UserRecipe whereRecipeId($value)
 * @method static Builder|UserRecipe whereUpdatedAt($value)
 * @method static Builder|UserRecipe whereUserId($value)
 * @mixin \Eloquent
 */
final class UserRecipe extends Model
{
    //	use Prunable; TODO: develop correct prunable strategy. We cannot just rely on meal_date.

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'recipes_to_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'user_id',
        'recipe_id',
        'custom_recipe_id',
        'flexmeal_id',
        'ingestion_id',
        'original_recipe_id',
        'meal_date',
        'meal_time'
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
     * Get the prunable model query.
     */
    //	public function prunable(): Builder
    //	{
    //		return UserRecipe::where('meal_date', '<=', now()->subMonth());
    //	}

    /**
     * Prune all prunable models in the database.
     */
    //	public function pruneAll(int $chunkSize = 1000): int
    //	{
    //		$total = 0;
    //
    //		$this
    //			->prunable()
    //			->when(in_array(SoftDeletes::class, class_uses_recursive(self::class)), fn($query) => $query->withTrashed())
    //			->chunkById($chunkSize, function ($models) use (&$total) {
    //				$models->each->prune();
    //
    //				$total += $models->count();
    //
    //				event(new ModelsPruned(self::class, $total));
    //			}, 'user_id');
    //
    //		return (int)$total;
    //	}

    /**
     * @return HasOne<Recipe>
     */
    public function recipe(): HasOne
    {
        return $this->hasOne(Recipe::class, 'id', 'recipe_id');
    }

    /**
     * @return HasOne<CustomRecipe>
     */
    public function customRecipe(): HasOne
    {
        return $this->hasOne(CustomRecipe::class, 'id', 'custom_recipe_id');
    }

    /**
     * @return HasOne<FlexmealLists>
     */
    public function flexmeal(): HasOne
    {
        return $this->hasOne(FlexmealLists::class, 'id', 'flexmeal_id');
    }

    public function getOriginalRecipeAttribute(): ?Recipe
    {
        return $this?->recipe_id ? $this?->recipe : $this?->customRecipe?->originalRecipe;
    }

    /**
     * @return HasOne<UserRecipeCalculated>
     */
    public function recipeCalculations(): HasOne
    {
        return $this->hasOne(UserRecipeCalculated::class, 'ingestion_id', 'ingestion_id')
            ->where('user_id', $this->user_id)
            ->when(
                $this->recipe_id,
                function (Builder $query) {
                    $query->where('recipe_id', $this->recipe_id);
                },
                function (Builder $query) {
                    $query->where('custom_recipe_id', $this->custom_recipe_id);
                }
            );
    }

    /**
     * @return HasOne<User>
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * @return HasOne<Ingestion>
     */
    public function ingestion(): HasOne
    {
        return $this->hasOne(Ingestion::class, 'id', 'ingestion_id');
    }

    /**
     * @deprecated
     */
    public function getCalculatedRecipeAttribute(): ?UserRecipeCalculated
    {
        return UserRecipeCalculated::where('user_id', $this->user_id)
            ->where('ingestion_id', $this->ingestion_id)
            ->when(
                $this->recipe_id,
                fn(Builder $query) => $query->where('recipe_id', $this->recipe_id),
                fn(Builder $query) => $query->where('custom_recipe_id', $this->custom_recipe_id)
            )
            ->first();
    }

    /**
     * Replace current recipe with a random one.
     *
     * @param array $excluded IDs of recipes to exclude.
     *
     * @throws \App\Exceptions\PublicException
     */
    public function replaceWithRandom(array $excluded, ?User $user = null): int
    {
        return app(RandomUserRecipeGeneratorService::class)->generateRandomRecipe($this, $excluded, $user);
    }

    /**
     * Allow saving without proper primary key.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    protected function setKeysForSaveQuery($query)
    {
        return $query
            ->where('user_id', '=', $this->getAttribute('user_id'))
            ->where('meal_date', '=', $this->getAttribute('meal_date'))
            ->where('ingestion_id', '=', $this->getAttribute('ingestion_id'));
    }
}
