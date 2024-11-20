<?php

namespace App\Models;

use App\Events;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

/**
 * Class RecipeStep
 *
 * @package App\Models
 * @property int $id
 * @property int $recipe_id
 * @property-read \App\Models\RecipeStepTranslation|null $translation
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\RecipeStepTranslation[] $translations
 * @property-read int|null $translations_count
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeStep listsTranslations(string $translationField)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeStep newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeStep newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeStep notTranslatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeStep orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeStep orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeStep orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeStep query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeStep translated()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeStep translatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeStep whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeStep whereRecipeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeStep whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeStep whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeStep withTranslation()
 * @mixin \Eloquent
 */
class RecipeStep extends Model implements TranslatableContract
{
    use Translatable;

    public $timestamps = false;

    /**
     * @var array<string>
     */
    public $translatedAttributes = ['description'];

    protected $table = 'recipe_steps';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['recipe_id'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['translations'];

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
}
