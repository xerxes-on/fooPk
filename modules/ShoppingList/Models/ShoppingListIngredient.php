<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOne};
use Modules\Ingredient\Models\Ingredient;
use Modules\Ingredient\Models\IngredientCategory;

/**
 * Class PurchaseListIngredient
 *
 * @property int $id
 * @property int $list_id
 * @property int|null $ingredient_id Null when manually by user
 * @property int|null $category_id Null when manually by user
 * @property string|null $custom_title Null when manually by user
 * @property int $amount Amount of ingredient in list
 * @property bool $completed Is ingredient already bought
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Modules\Ingredient\Models\IngredientCategory|null $category
 * @property-read \Modules\Ingredient\Models\Ingredient|null $ingredient
 * @property-read \Modules\ShoppingList\Models\ShoppingList $list
 * @method static \Illuminate\Database\Eloquent\Builder|ShoppingListIngredient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShoppingListIngredient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShoppingListIngredient query()
 * @method static \Illuminate\Database\Eloquent\Builder|ShoppingListIngredient whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShoppingListIngredient whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShoppingListIngredient whereCompleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShoppingListIngredient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShoppingListIngredient whereCustomTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShoppingListIngredient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShoppingListIngredient whereIngredientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShoppingListIngredient whereListId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShoppingListIngredient whereUpdatedAt($value)
 * @mixin \Eloquent
 */
final class ShoppingListIngredient extends Model
{
    protected $table = 'shopping_lists_ingredients';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'list_id',
        'ingredient_id',    // can be null in case it is a custom ingredient
        'category_id',      // can be null in case it is a custom ingredient
        'custom_title',     // custom ingredient title, can be null in case it is not a custom ingredient
        'amount',
        'completed'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'completed' => 'boolean'
    ];

    /**
     * relations get Shopping List
     */
    public function list(): BelongsTo
    {
        return $this->belongsTo(ShoppingList::class, 'list_id');
    }

    /**
     * relation get Ingredient
     */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class, 'ingredient_id')->with('unit');
    }

    /**
     * relation get ingredient category
     */
    public function category(): HasOne
    {
        return $this->hasOne(IngredientCategory::class, 'id', 'category_id');
    }
}
