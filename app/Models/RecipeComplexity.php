<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Recipe\RecipeComplexityService;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class RecipeComplexity
 *
 * @property-read \App\Models\Recipe $recipes
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @package App\Models
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read int|null $recipes_count
 * @property-read \App\Models\RecipeComplexityTranslation|null $translation
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\RecipeComplexityTranslation[] $translations
 * @property-read int|null $translations_count
 * @method static \Database\Factories\RecipeComplexityFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeComplexity listsTranslations(string $translationField)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeComplexity newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeComplexity newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeComplexity notTranslatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeComplexity orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeComplexity orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeComplexity orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeComplexity query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeComplexity translated()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeComplexity translatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeComplexity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeComplexity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeComplexity whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeComplexity whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeComplexity whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipeComplexity withTranslation()
 * @mixin \Eloquent
 */
final class RecipeComplexity extends Model implements TranslatableContract
{
    use Translatable;
    use HasFactory;

    /**
     * @var array
     */
    public $translatedAttributes = ['title'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['translations'];

    protected $table = 'recipe_complexity';

    /**
     * relation get Recipe
     *
     * @return HasMany
     */
    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class, 'id', 'complexity_id');
    }

    public static function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return app(RecipeComplexityService::class)->getAll();
    }
}
