<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Models;

use App\Enums\Recipe\RecipeTypeEnum;
use App\Models\CustomRecipe;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, HasMany, HasOne};
use Modules\FlexMeal\Models\FlexmealLists;
use Modules\Ingredient\Models\Ingredient;

/**
 * Shopping list model.
 *
 * For a users' convenience. So that a user could generate (and optionally print) it
 * and use it during shopping.
 *
 * Don't confuse it with in-app marketplace.
 *
 * @property int $id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CustomRecipe> $customRecipes
 * @property-read int|null $custom_recipes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\ShoppingList\Models\ShoppingListIngredient> $ingredients
 * @property-read int|null $ingredients_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Ingredient\Models\Ingredient> $ingredientsWithPivot
 * @property-read int|null $ingredients_with_pivot_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Recipe> $recipes
 * @property-read int|null $recipes_count
 * @property-read \App\Models\User|null $user
 * @method static Builder|ShoppingList flexMealWithIngredientsByID(int $recipeID)
 * @method static Builder|ShoppingList newModelQuery()
 * @method static Builder|ShoppingList newQuery()
 * @method static Builder|ShoppingList query()
 * @method static Builder|ShoppingList whereCreatedAt($value)
 * @method static Builder|ShoppingList whereId($value)
 * @method static Builder|ShoppingList whereUpdatedAt($value)
 * @method static Builder|ShoppingList whereUserId($value)
 * @mixin \Eloquent
 */
final class ShoppingList extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shopping_lists';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'user_id'
    ];

    /**
     * The event map for the model.
     *
     * Allows for object-based events for native Eloquent events.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'saved'   => \Modules\ShoppingList\Events\ShoppingListProcessed::class,
        'deleted' => \Modules\ShoppingList\Events\ShoppingListProcessed::class,
    ];

    /**
     * Bootstrap the model and its traits.
     */
    public static function boot(): void
    {
        parent::boot();
        // Clean model relations
        self::deleting(static function (ShoppingList $list) {
            $list->ingredients()->delete();
            $list->recipes()->detach();
        });
    }

    /**
     * relation get Ingredients
     */
    public function ingredients(): HasMany
    {
        return $this->hasMany(ShoppingListIngredient::class, 'list_id', 'id');
    }

    /**
     * relation get Recipes
     */
    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'shopping_lists_recipes', 'list_id', 'recipe_id')
            ->where('recipe_type', RecipeTypeEnum::ORIGINAL)
            ->withPivot('id', 'recipe_type', 'servings', 'mealtime', 'meal_day');
    }

    /**
     * relation get User
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * relation IngredientsWithPivot
     */
    public function ingredientsWithPivot(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'shopping_lists_ingredients', 'list_id', 'ingredient_id')
            ->withPivot('custom_title', 'amount', 'completed');
    }

    /**
     * relation get Custom Recipes
     */
    public function customRecipes(): BelongsToMany
    {
        return $this->belongsToMany(CustomRecipe::class, 'shopping_lists_recipes', 'list_id', 'recipe_id')
            ->where('recipe_type', RecipeTypeEnum::CUSTOM)
            ->withPivot('id', 'recipe_type', 'servings', 'mealtime', 'meal_day');
    }

    /**
     * Get planned flexmeal by id with ingredients.
     */
    public function scopeFlexMealWithIngredientsByID(Builder $query, int $recipeID): BelongsToMany
    {
        return $this->flexmeals()->where('flexmeal_lists.id', $recipeID)->with('ingredients');
    }

    /**
     * Relation with Flexmeals.
     *
     * Must use user id to check on, otherwise we can have flexmeal of other user.
     */
    public function flexmeals(?int $userId = null): BelongsToMany
    {
        return $this->belongsToMany(FlexmealLists::class, 'shopping_lists_recipes', 'list_id', 'recipe_id')
            ->where('user_id', is_null($userId) ? \Auth::id() : $userId)
            ->where('recipe_type', RecipeTypeEnum::FLEXMEAL)
            ->withPivot('id', 'recipe_type', 'servings', 'mealtime', 'meal_day');
    }
}
