<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Ingredient\Models\Ingredient;

/**
 * Month/season
 *
 * @property-read \Modules\Ingredient\Models\Ingredient $ingredients
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @package App\Models
 * @property int $id
 * @property string $key
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read int|null $ingredients_count
 * @property-read \App\Models\SeasonsTranslation|null $translation
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SeasonsTranslation[] $translations
 * @property-read int|null $translations_count
 * @method static \Illuminate\Database\Eloquent\Builder|TranslatableStaplerModel listsTranslations(string $translationField)
 * @method static \Illuminate\Database\Eloquent\Builder|Seasons newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Seasons newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TranslatableStaplerModel notTranslatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|TranslatableStaplerModel orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|TranslatableStaplerModel orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|TranslatableStaplerModel orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|Seasons query()
 * @method static \Illuminate\Database\Eloquent\Builder|TranslatableStaplerModel translated()
 * @method static \Illuminate\Database\Eloquent\Builder|TranslatableStaplerModel translatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Seasons whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Seasons whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Seasons whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Seasons whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TranslatableStaplerModel whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder|TranslatableStaplerModel whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Seasons whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TranslatableStaplerModel withTranslation()
 * @method static \Database\Factories\SeasonsFactory factory(...$parameters)
 * @mixin \Eloquent
 */
final class Seasons extends TranslatableStaplerModel
{
    use HasFactory;

    /**
     * Intentionally set to DB default season ID.
     *
     * @var int
     */
    public const ANY_SEASON_ID = 0;

    /**
     * @var array<string>
     */
    public $translatedAttributes = ['name'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'seasons';

    /**
     * The relations to eager load on every query.
     *
     * @var array<string>
     */
    protected $with = ['translations'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['key'];

    /**
     * Get allergy allowed diets
     */
    final public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'ingredient_seasons', 'season_id');
    }
}
