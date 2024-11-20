<?php

namespace Modules\Ingredient\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class IngredientCategoryTranslation
 *
 * @package App\Models
 * @property int $id
 * @property int $ingredient_category_id
 * @property string $locale
 * @property string $name
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientCategoryTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientCategoryTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientCategoryTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientCategoryTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientCategoryTranslation whereIngredientCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientCategoryTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientCategoryTranslation whereName($value)
 * @mixin \Eloquent
 */
class IngredientCategoryTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['name'];
}
