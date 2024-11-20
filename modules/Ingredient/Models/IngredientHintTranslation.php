<?php

namespace Modules\Ingredient\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Ingredient Hint Translation model.
 *
 * @property int $id
 * @property int $ingredient_hint_id
 * @property string $locale
 * @property string $content
 * @property string $link_url
 * @property string $link_text
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientHintTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientHintTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientHintTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientHintTranslation whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientHintTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientHintTranslation whereIngredientHintId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientHintTranslation whereLinkText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientHintTranslation whereLinkUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientHintTranslation whereLocale($value)
 * @mixin \Eloquent
 */
final class IngredientHintTranslation extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    public $table = 'ingredient_hint_translations';

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
    protected $fillable = ['content', 'link_text', 'link_url'];
}
