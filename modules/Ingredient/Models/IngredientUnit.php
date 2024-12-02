<?php

declare(strict_types=1);

namespace Modules\Ingredient\Models;

use App\Enums\Recipe\RecipeStatusEnum;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Ingredient\Enums\IngredientUnitType;

/**
 * Unit of measurement of an ingredient.
 *
 * @property int $id
 * @property int $type
 * @property int $visibility Describes if the unit is visible on frontend
 * @property int|null $next_unit_id
 * @property float|null $max_value
 * @property float $default_amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\Ingredient\Models\IngredientUnitTranslation|null $translation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Ingredient\Models\IngredientUnitTranslation> $translations
 * @property-read int|null $translations_count
 * @method static Builder|IngredientUnit isPrimary()
 * @method static Builder|IngredientUnit isSecondary()
 * @method static Builder|IngredientUnit listsTranslations(string $translationField)
 * @method static Builder|IngredientUnit newModelQuery()
 * @method static Builder|IngredientUnit newQuery()
 * @method static Builder|IngredientUnit notTranslatedIn(?string $locale = null)
 * @method static Builder|IngredientUnit orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static Builder|IngredientUnit orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder|IngredientUnit orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static Builder|IngredientUnit query()
 * @method static Builder|IngredientUnit translated()
 * @method static Builder|IngredientUnit translatedIn(?string $locale = null)
 * @method static Builder|IngredientUnit whereCreatedAt($value)
 * @method static Builder|IngredientUnit whereDefaultAmount($value)
 * @method static Builder|IngredientUnit whereId($value)
 * @method static Builder|IngredientUnit whereMaxValue($value)
 * @method static Builder|IngredientUnit whereNextUnitId($value)
 * @method static Builder|IngredientUnit whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static Builder|IngredientUnit whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder|IngredientUnit whereType($value)
 * @method static Builder|IngredientUnit whereUpdatedAt($value)
 * @method static Builder|IngredientUnit whereVisibility($value)
 * @method static Builder|IngredientUnit withTranslation()
 * @mixin \Eloquent
 */
final class IngredientUnit extends Model implements TranslatableContract
{
    use Translatable;

    /**
     * @var array<int,string>
     */
    public $translatedAttributes = [
        'full_name',
        'short_name'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ingredient_units';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'type',
        'unit',
        'next_unit_id',
        'max_value',
        'default_amount'
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array<int,string>
     */
    protected $with = ['translations'];

    public function scopeIsPrimary(Builder $query): Builder
    {
        return $query->where('type', IngredientUnitType::PRIMARY->value);
    }
    public function scopeIsSecondary(Builder $query): Builder
    {
        return $query->where('type', IngredientUnitType::SECONDARY->value);
    }
}
