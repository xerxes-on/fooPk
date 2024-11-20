<?php

namespace Modules\Ingredient\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modules\Ingredient\Models\IngredientTranslation
 *
 * @property int $id
 * @property int $ingredient_id
 * @property string $locale
 * @property string $name
 * @property string $name_plural
 * @property-read \Modules\Ingredient\Models\Ingredient $ingredient
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientTranslation whereIngredientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientTranslation whereNamePlural($value)
 * @mixin \Eloquent
 */
class IngredientTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'name_plural'];

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class, 'ingredient_id');
    }
}
