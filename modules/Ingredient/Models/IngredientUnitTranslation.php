<?php

namespace Modules\Ingredient\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class IngredientUnitTranslation
 *
 * @package App\Models
 * @property int $id
 * @property int $ingredient_unit_id
 * @property string $locale
 * @property string $full_name
 * @property string $short_name
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientUnitTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientUnitTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientUnitTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientUnitTranslation whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientUnitTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientUnitTranslation whereIngredientUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientUnitTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientUnitTranslation whereShortName($value)
 * @mixin \Eloquent
 */
class IngredientUnitTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'full_name',
        'short_name'
    ];
}
