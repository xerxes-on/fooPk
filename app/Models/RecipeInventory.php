<?php

namespace App\Models;

use App\Events;
use Illuminate\Database\Eloquent\Model;

/**
 * Class RecipeInventory
 *
 * @package App\Models
 * @property int $recipe_id
 * @property int $inventory_id
 * @property-read \App\Models\Inventory|null $inventory
 * @property-read \App\Models\Recipe|null $recipe
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeInventory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeInventory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeInventory query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeInventory whereInventoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeInventory whereRecipeId($value)
 * @mixin \Eloquent
 */
class RecipeInventory extends Model
{
    protected $table = 'recipes_to_inventories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'recipe_id',
        'inventory_id'
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
     * relation get inventory
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function inventory()
    {
        return $this->hasOne(Inventory::class, 'id', 'inventory_id');
    }
}
