<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class RecipeTranslation
 *
 * @package App\Models
 * @property int $id
 * @property int $recipe_id
 * @property string $locale
 * @property string $title
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeTranslation whereRecipeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeTranslation whereTitle($value)
 * @mixin \Eloquent
 */
class RecipeTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['title'];
}
