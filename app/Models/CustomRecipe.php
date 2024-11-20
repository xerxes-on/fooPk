<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Ingredient\Models\Ingredient;

/**
 * Custom recipe.
 *
 * It is based on a common recipe (attribute recipe_id).
 * It "inherits" most of that common recipe properties.
 * It can be created by replacement of an ingredient of a common recipe.
 *
 * @property int $id
 * @property int $user_id
 * @property int $ingestion_id
 * @property int $recipe_id
 * @property string $title
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Ingestion|null $ingestion
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Ingredient> $ingredients
 * @property-read int|null $ingredients_count
 * @property-read \App\Models\Recipe|null $originalRecipe
 * @method static \Database\Factories\CustomRecipeFactory factory($count = null, $state = [])
 * @method static Builder|CustomRecipe newModelQuery()
 * @method static Builder|CustomRecipe newQuery()
 * @method static Builder|CustomRecipe query()
 * @method static Builder|CustomRecipe whereCreatedAt($value)
 * @method static Builder|CustomRecipe whereId($value)
 * @method static Builder|CustomRecipe whereIngestionId($value)
 * @method static Builder|CustomRecipe whereRecipeId($value)
 * @method static Builder|CustomRecipe whereTitle($value)
 * @method static Builder|CustomRecipe whereUpdatedAt($value)
 * @method static Builder|CustomRecipe whereUserId($value)
 * @mixin \Eloquent
 */
final class CustomRecipe extends Model
{
    use HasFactory;
    use Prunable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'custom_recipes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'user_id',
        'recipe_id',
        'ingestion_id',
        'title'
    ];

    /**
     * Get the prunable model query.
     *
     * @return Builder<CustomRecipe>
     */
    public function prunable(): Builder
    {
        return self::where('created_at', '<=', now()->subMonths(6));
    }

    /**
     * @return HasOne<Ingestion>
     */
    public function ingestion(): HasOne
    {
        return $this->hasOne(Ingestion::class, 'id', 'ingestion_id');
    }

    /**
     * @return HasOne<Recipe>
     */
    public function originalRecipe(): HasOne
    {
        return $this->hasOne(Recipe::class, 'id', 'recipe_id');
    }

    /**
     * Check if recipe is Cooked
     */
    public function cooked(): bool
    {
        return (bool)$this->pivot->cooked;
    }

    /**
     * Check if recipe is eaten out
     */
    public function eatOut(): bool
    {
        return (bool)$this->pivot->eat_out;
    }

    /**
     * Save recipe ingredients
     *
     * TODO: consider moving to a service
     * TODO: can be very slow more the 2 seconds...need to find a way to speed it up
     * @param $ingredients
     * @return $this
     */
    public function saveRecipeIngredients($ingredients): CustomRecipe
    {
        $this->ingredients()->detach();

        if (!is_null($ingredients)) {
            $separated_ingredients = [];
            // separate empty ingredients
            foreach ($ingredients as $ingredient) {
                if ($ingredient['ingredient_id'] !== '0') {
                    $separated_ingredients[] = $ingredient;
                }
            }

            $this->ingredients()->attach($separated_ingredients);
        }

        return $this;
    }

    /**
     * @return BelongsToMany<Ingredient>
     */
    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'ingredients_to_custom_recipes')
            ->withPivot('amount', 'type');
    }
}
