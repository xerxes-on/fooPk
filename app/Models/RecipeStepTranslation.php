<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class RecipeStepTranslation
 *
 * @package App\Models
 * @property int $id
 * @property int $recipe_step_id
 * @property string $locale
 * @property string $description
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeStepTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeStepTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeStepTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeStepTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeStepTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeStepTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeStepTranslation whereRecipeStepId($value)
 * @mixin \Eloquent
 */
class RecipeStepTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['description'];
}
