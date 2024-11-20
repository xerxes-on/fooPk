<?php

namespace Modules\Ingredient\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Translations model for IngredientTag.
 *
 * @property int $id
 * @property int $ingredient_tag_id
 * @property string $locale
 * @property string $title
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientTagTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientTagTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientTagTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientTagTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientTagTranslation whereIngredientTagId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientTagTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientTagTranslation whereTitle($value)
 * @mixin \Eloquent
 */
final class IngredientTagTranslation extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    public $table = 'ingredient_tags_translations';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['title'];
}
