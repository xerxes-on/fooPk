<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Recipe Tag Model.
 *
 * @property int $id
 * @property string $slug
 * @property bool $filter 0 - tag is for internal admin usage, 1 - public usage
 * @property bool $is_internal Determine whether tag is applicable for recipe distribution: 0 - no, 1 - yes
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Recipe> $recipes
 * @property-read int|null $recipes_count
 * @property-read \App\Models\RecipeTagTranslation|null $translation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RecipeTagTranslation> $translations
 * @property-read int|null $translations_count
 * @method static Builder|RecipeTag adminOnly()
 * @method static Builder|RecipeTag forDistribution()
 * @method static Builder|RecipeTag listsTranslations(string $translationField)
 * @method static Builder|RecipeTag newModelQuery()
 * @method static Builder|RecipeTag newQuery()
 * @method static Builder|RecipeTag notTranslatedIn(?string $locale = null)
 * @method static Builder|RecipeTag orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static Builder|RecipeTag orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder|RecipeTag orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static Builder|RecipeTag publicOnly()
 * @method static Builder|RecipeTag query()
 * @method static Builder|RecipeTag searchBy(?array $filters)
 * @method static Builder|RecipeTag translated()
 * @method static Builder|RecipeTag translatedIn(?string $locale = null)
 * @method static Builder|RecipeTag whereCreatedAt($value)
 * @method static Builder|RecipeTag whereFilter($value)
 * @method static Builder|RecipeTag whereId($value)
 * @method static Builder|RecipeTag whereIsInternal($value)
 * @method static Builder|RecipeTag whereSlug($value)
 * @method static Builder|RecipeTag whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static Builder|RecipeTag whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder|RecipeTag whereUpdatedAt($value)
 * @method static Builder|RecipeTag withTranslation()
 * @mixin \Eloquent
 */
final class RecipeTag extends Model implements TranslatableContract
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
    protected $table = 'recipe_tags';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'slug',
        'filter',
        'is_internal',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'filter'      => 'boolean',
        'is_internal' => 'boolean',
    ];

    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'recipes_to_tags', 'recipe_tag_id');
    }

    public function scopePublicOnly(Builder $query): Builder
    {
        return $query->where('filter', 1);
    }

    public function scopeAdminOnly(Builder $query): Builder
    {
        return $query->where('filter', 0);
    }

    public function scopeForDistribution(Builder $query): Builder
    {
        return $query->where('is_internal', 1);
    }

    /**
     * Scope a query to search by filters.
     */
    public function scopeSearchBy(Builder $query, ?array $filters): Builder
    {
        $name = $filters['search_name'] ?? null;

        if (empty($name)) {
            return $query;
        }

        $query->whereHas('translations', fn(Builder $query) => $query->where('title', 'LIKE', '%' . $name . '%'))
            ->orWhere('slug', 'LIKE', '%' . $name . '%');

        return $query;
    }
}
