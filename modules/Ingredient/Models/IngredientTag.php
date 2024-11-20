<?php

declare(strict_types=1);

namespace Modules\Ingredient\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * IngredientTag model.
 *
 * @property int $id
 * @property string $slug
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Ingredient\Models\Ingredient> $ingredients
 * @property-read int|null $ingredients_count
 * @property-read \Modules\Ingredient\Models\IngredientTagTranslation|null $translation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Ingredient\Models\IngredientTagTranslation> $translations
 * @property-read int|null $translations_count
 * @method static Builder|IngredientTag listsTranslations(string $translationField)
 * @method static Builder|IngredientTag newModelQuery()
 * @method static Builder|IngredientTag newQuery()
 * @method static Builder|IngredientTag notTranslatedIn(?string $locale = null)
 * @method static Builder|IngredientTag orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static Builder|IngredientTag orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder|IngredientTag orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static Builder|IngredientTag query()
 * @method static Builder|IngredientTag searchBy(?array $filters, ?string $locale = null, bool $includeSlug = true)
 * @method static Builder|IngredientTag translated()
 * @method static Builder|IngredientTag translatedIn(?string $locale = null)
 * @method static Builder|IngredientTag whereCreatedAt($value)
 * @method static Builder|IngredientTag whereId($value)
 * @method static Builder|IngredientTag whereSlug($value)
 * @method static Builder|IngredientTag whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static Builder|IngredientTag whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder|IngredientTag whereUpdatedAt($value)
 * @method static Builder|IngredientTag withTranslation()
 * @mixin \Eloquent
 */
final class IngredientTag extends Model implements TranslatableContract
{
    use Translatable;

    /**
     * @var array<int, string>
     */
    public $translatedAttributes = ['title'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ingredient_tags';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'slug'
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array<int,string>
     */
    protected $with = ['translations'];

    /**
     * Ingredients relation.
     *
     * @return BelongsToMany<Ingredient>
     */
    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'ingredient_to_tags', 'ingredient_tag_id');
    }

    /**
     * Scope a query to search by filters.
     */
    public function scopeSearchBy(Builder $query, ?array $filters, ?string $locale = null, bool $includeSlug = true): Builder
    {
        $name = $filters['search_name'] ?? null;

        if (empty($name)) {
            return $query;
        }

        $locale = $locale ?? \Auth::user()->language ?? 'de';

        $query->whereHas(
            'translations',
            fn(Builder $query) => $query->where('title', 'LIKE', "%$name%")->where('locale', $locale)
        );

        if ($includeSlug) {
            $query->orWhere('slug', 'LIKE', '%' . $name . '%');
        }

        return $query;
    }
}
