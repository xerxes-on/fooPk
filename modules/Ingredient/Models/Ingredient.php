<?php

namespace Modules\Ingredient\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Modules\Ingredient\Services\IngredientService;
use Modules\Ingredient\Traits\Model\Ingredient\HasModelRelationships;
use Modules\Ingredient\Traits\Model\Ingredient\HasModelScope;

/**
 * Ingredient Model
 *
 * @property int $id
 * @property int $category_id
 * @property float $proteins
 * @property float $fats
 * @property float $carbohydrates
 * @property float $calories
 * @property int $unit_id
 * @property int|null $alternative_unit_id
 * @property int $unit_amount How many unit amount is required to have in piece of ingredient
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\Ingredient\Models\IngredientUnit|null $alternativeUnit
 * @property-read \Modules\Ingredient\Models\IngredientCategory|null $category
 * @property-read bool $exist_in_recipes
 * @property-read array $present_in_recipes
 * @property-read \Modules\Ingredient\Models\IngredientHint|null $hint
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Recipe> $recipesAsStatic
 * @property-read int|null $recipes_as_static_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Recipe> $recipesAsVariable
 * @property-read int|null $recipes_as_variable_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Seasons> $seasons
 * @property-read int|null $seasons_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Ingredient\Models\IngredientTag> $tags
 * @property-read int|null $tags_count
 * @property-read \Modules\Ingredient\Models\IngredientTranslation|null $translation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Ingredient\Models\IngredientTranslation> $translations
 * @property-read int|null $translations_count
 * @property-read \Modules\Ingredient\Models\IngredientUnit|null $unit
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Vitamin> $vitamins
 * @property-read int|null $vitamins_count
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient allowedForUser(int $userId)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient listsTranslations(string $translationField)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient notTranslatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient ofIds(array $ids)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient query()
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient searchBy(array $filters)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient translated()
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient translatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient whereAlternativeUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient whereCalories($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient whereCarbohydrates($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient whereFats($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient whereProteins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient whereUnitAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingredient withTranslation()
 * @mixin \Eloquent
 */
final class Ingredient extends Model implements TranslatableContract
{
    use Translatable;
    use HasFactory;
    use HasModelScope;
    use HasModelRelationships;

    public array $translatedAttributes = ['name', 'name_plural'];

    protected $with = ['translations', 'category.diets', 'unit'];

    protected $table = 'ingredients';

    protected $dispatchesEvents = [
        'saved'   => \Modules\Ingredient\Events\IngredientProcessed::class,
        'deleted' => \Modules\Ingredient\Events\IngredientProcessed::class,
    ];

    protected $fillable = [
        'category_id',
        'proteins',
        'fats',
        'carbohydrates',
        'calories',
        'unit_id',
        'alternative_unit_id',
        'unit_amount',
        'image'
    ];

    public static function boot(): void
    {
        parent::boot();
        // Clean model relations
        self::deleting(static function (Ingredient $model) {
            $model->vitamins()->sync([]);
            $model->recipesAsStatic()->sync([]);
            $model->recipesAsVariable()->sync([]);
        });
    }

    /**
     * @return Collection<int,Ingredient>
     */
    public static function getAll(): Collection
    {
        return app(IngredientService::class)->getAll();
    }

    public static function getOnlyIds(): array
    {
        return app(IngredientService::class)->getIds();
    }

    public function getExistInRecipesAttribute(): bool
    {
        return $this->where('id', $this->id)->where(function ($query) {
            $query->has('recipesAsStatic')->orWhereHas('recipesAsVariable');
        })->exists();
    }

    public function getPresentInRecipesAttribute(): array
    {
        $static = $this->relationLoaded('recipesAsStatic') ?
            $this->getRelation('recipesAsStatic') :
            $this->recipesAsStatic()->pluck('recipes.id');
        $variable = $this->relationLoaded('recipesAsVariable') ?
            $this->getRelation('recipesAsVariable') :
            $this->recipesAsVariable()->pluck('recipes.id');

        return $static->merge($variable)
            ->unique()
            ->sort(static fn(int $first, int $second) => $first <=> $second)
            ->values()
            ->toArray();
    }
}
