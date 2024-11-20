<?php

namespace Modules\Ingredient\Models;

use App\Models\Diet;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, HasMany};

/**
 * Modules\Ingredient\Models\IngredientCategory
 *
 * @property int $id
 * @property int|null $parent_id
 * @property array|null $tree_information
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Diet> $diets
 * @property-read int|null $diets_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Ingredient\Models\Ingredient> $ingredients
 * @property-read int|null $ingredients_count
 * @property-read \Modules\Ingredient\Models\IngredientCategoryTranslation|null $translation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Ingredient\Models\IngredientCategoryTranslation> $translations
 * @property-read int|null $translations_count
 * @method static Builder|IngredientCategory excludeId($id)
 * @method static Builder|IngredientCategory listsTranslations(string $translationField)
 * @method static Builder|IngredientCategory majorCategories()
 * @method static Builder|IngredientCategory midCategories(int $id, int $mainCategoryId)
 * @method static Builder|IngredientCategory newModelQuery()
 * @method static Builder|IngredientCategory newQuery()
 * @method static Builder|IngredientCategory notTranslatedIn(?string $locale = null)
 * @method static Builder|IngredientCategory ofAllCategories()
 * @method static Builder|IngredientCategory ofIds(array $id)
 * @method static Builder|IngredientCategory orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static Builder|IngredientCategory orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder|IngredientCategory orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static Builder|IngredientCategory query()
 * @method static Builder|IngredientCategory translated()
 * @method static Builder|IngredientCategory translatedIn(?string $locale = null)
 * @method static Builder|IngredientCategory whereCreatedAt($value)
 * @method static Builder|IngredientCategory whereId($value)
 * @method static Builder|IngredientCategory whereParentId($value)
 * @method static Builder|IngredientCategory whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static Builder|IngredientCategory whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder|IngredientCategory whereTreeInformation($value)
 * @method static Builder|IngredientCategory whereUpdatedAt($value)
 * @method static Builder|IngredientCategory withTranslation()
 * @mixin \Eloquent
 */
final class IngredientCategory extends Model implements TranslatableContract
{
    use Translatable;

    // TODO: Replace with enum upon refactoring of Calculation
    public const MAIN_CATEGORIES = [
        '2' => [
            'full'  => 'protein',
            'short' => 'EW'
        ],
        '3' => [
            'full'  => 'fat',
            'short' => 'F'
        ],
        '4' => [
            'full'  => 'carbohydrates',
            'short' => 'KH'
        ]
    ];

    /**
     * @var array<int,string>
     */
    public $translatedAttributes = ['name'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ingredient_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'parent_id',
        'tree_information'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'tree_information' => 'array',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array<string>
     */
    protected $with = ['translations', 'diets'];

    /**
     * Get all ingredients from category
     */
    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class, 'category_id');
    }

    /**
     * Get category diets
     */
    public function diets(): BelongsToMany
    {
        return $this->belongsToMany(Diet::class, 'ingredient_categories_to_diets');
    }

    /**
     * Scope a query to only include ingredients category of certain ids.
     */
    public function scopeOfIds(Builder $query, array $id): Builder
    {
        return $query->whereIn('id', $id);
    }

    /**
     * Scope a query to only include ingredient middle category.
     */
    public function scopeMidCategories(Builder $builder, int $id, int $mainCategoryId): Builder
    {
        return self::whereParentId($mainCategoryId)->where('id', '!=', $id);
    }

    /**
     * Major Categories
     */
    public function scopeMajorCategories(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope a query to only include ingredient middle category.
     */
    public function scopeOfAllCategories(Builder $builder): Builder
    {
        return self::ofIds(array_keys(self::MAIN_CATEGORIES));
    }

    /**
     * Scope a query to only exclude ingredients category of certain id.
     */
    public function scopeExcludeId(Builder $query, $id): Builder
    {
        return $query->where('id', '!=', $id);
    }
}
