<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Models;

use App\Enums\MealtimeEnum;
use App\Enums\Recipe\RecipeTypeEnum;
use App\Models\CustomRecipe;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Recipe in shopping list.
 *
 * @property int $id
 * @property int $list_id
 * @property int $recipe_id
 * @property RecipeTypeEnum $recipe_type Described in php RecipeTypeEnum
 * @property int $servings Amount of recipe servings
 * @property MealtimeEnum $mealtime Described in php MealtimeEnum
 * @property string $meal_day Date when a meal was planned
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\Recipe|null $recipe
 * @method static \Illuminate\Database\Eloquent\Builder|ShoppingListRecipe newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShoppingListRecipe newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShoppingListRecipe query()
 * @method static \Illuminate\Database\Eloquent\Builder|ShoppingListRecipe whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShoppingListRecipe whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShoppingListRecipe whereListId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShoppingListRecipe whereMealDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShoppingListRecipe whereMealtime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShoppingListRecipe whereRecipeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShoppingListRecipe whereRecipeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShoppingListRecipe whereServings($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShoppingListRecipe whereUpdatedAt($value)
 * @property-read \Modules\ShoppingList\Models\ShoppingList $shoppingList
 * @mixin \Eloquent
 */
final class ShoppingListRecipe extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shopping_lists_recipes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'list_id',
        'recipe_id',
        'recipe_type',
        'servings',
        'mealtime',
        'meal_day',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'recipe_type' => RecipeTypeEnum::class,
        'mealtime'    => MealtimeEnum::class,
    ];

    /**
     * @return HasOne<CustomRecipe|Recipe>
     */
    public function recipe(): HasOne
    {
        return $this->hasOne(
            ($this->recipe_type === RecipeTypeEnum::CUSTOM ? CustomRecipe::class : Recipe::class),
            'id',
            'recipe_id'
        );
    }

    /**
     * @return BelongsTo<ShoppingList,ShoppingListRecipe>
     */
    public function shoppingList(): BelongsTo
    {
        return $this->belongsTo(ShoppingList::class, 'list_id', 'id');
    }
}
