<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Inventory extends Model implements TranslatableContract
{
    use Translatable;

    /**
     * @var array<int, string>
     */
    public $translatedAttributes = ['title', 'tags'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventories';

    /**
     * The relations to eager load on every query.
     *
     * @var array<string>
     */
    protected $with = ['translations'];

    /**
     * Get the recipes for the RecipeInventory.
     */
    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'recipes_to_inventories');
    }
}
