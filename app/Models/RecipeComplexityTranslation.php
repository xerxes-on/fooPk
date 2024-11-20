<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class RecipeComplexityTranslation
 *
 * @package App\Models
 * @property int $id
 * @property int $recipe_complexity_id
 * @property string $locale
 * @property string $title
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeComplexityTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeComplexityTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeComplexityTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeComplexityTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeComplexityTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeComplexityTranslation whereRecipeComplexityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeComplexityTranslation whereTitle($value)
 * @mixin \Eloquent
 */
class RecipeComplexityTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['title'];
}
