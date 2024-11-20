<?php
/**
 * @copyright   Copyright © 2019 Lindenvalley GmbH (http://www.lindenvalley.de/)
 * @author      Andrey Rayfurak <andrey.rayfurak@lindenvalley.de>
 * @date        14.05.2020
 */

declare(strict_types=1);

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany};
use Modules\Ingredient\Models\Ingredient;
use Modules\Ingredient\Models\IngredientCategory;

/**
 * Class Allergy
 *
 * @property int $id
 * @property string $slug
 * @property int $type_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Diet> $allowedDiets
 * @property-read int|null $allowed_diets_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Ingredient\Models\IngredientCategory> $ingredientCategories
 * @property-read int|null $ingredient_categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Ingredient\Models\Ingredient> $ingredients
 * @property-read int|null $ingredients_count
 * @property-read \App\Models\AllergyTranslation|null $translation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AllergyTranslation> $translations
 * @property-read int|null $translations_count
 * @property-read \App\Models\AllergyTypes $type
 * @method static \Illuminate\Database\Eloquent\Builder|Allergy listsTranslations(string $translationField)
 * @method static \Illuminate\Database\Eloquent\Builder|Allergy newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Allergy newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Allergy notTranslatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Allergy orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Allergy orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Allergy orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|Allergy query()
 * @method static \Illuminate\Database\Eloquent\Builder|Allergy translated()
 * @method static \Illuminate\Database\Eloquent\Builder|Allergy translatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Allergy whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Allergy whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Allergy whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Allergy whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder|Allergy whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Allergy whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Allergy whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Allergy withTranslation()
 * @mixin \Eloquent
 */
final class Allergy extends Model implements TranslatableContract
{
    use Translatable;

    /**
     * Allergy ids which uses in automation user creation
     * TODO: better move to ENUM as we can group it with methods
     */

    /**
     * TODO:: refactor this class, because it's allergy, it's disease, it's bulk exclusions
     */
    public const ALLERGY_BULK_EXCLUSION_FIRST_EXCLUSION_NOT_SEASONAL                   = 36;
    public const ALLERGY_BULK_EXCLUSION_FIRST_EXCLUSION_SOY                            = 47;
    public const ALLERGY_BULK_EXCLUSION_FIRST_EXCLUSION_BAKING_MIXES_AND_SPEC_PRODUCTS = 51;
    public const ALLERGY_BULK_EXCLUSION_FIRST_EXCLUSION_PROTEIN_POWDER                 = 58;

    /**
     * @var array<int, string>
     */
    public $translatedAttributes = ['name'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'allergies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = ['id', 'slug'];

    /**
     * The relations to eager load on every query.
     *
     * @var array<int,string>
     */
    protected $with = ['translations'];

    /**
     * Get allergy allowed diets
     */
    public function allowedDiets(): BelongsToMany
    {
        return $this->belongsToMany(Diet::class, 'allergy_to_diets');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(AllergyTypes::class, 'type_id');
    }

    /**
     * Update exist ingredientCategories or create new
     * TODO: used in admin once. Maybe move to service
     * @param $ingredient_categories
     * @return $this
     */
    public function saveIngredientCategories($ingredient_categories): Allergy
    {
        $this->ingredientCategories()->detach();

        if (is_null($ingredient_categories)) {
            return $this;
        }

        $categories_to_save = [];
        foreach ($ingredient_categories as $category_id) {
            $categories_to_save[] = [
                'ingredient_category_id' => $category_id
            ];
        }

        $this->ingredientCategories()->attach($categories_to_save);

        return $this;
    }

    /**
     * Get allergy ingredient categories
     */
    public function ingredientCategories(): BelongsToMany
    {
        return $this->belongsToMany(IngredientCategory::class, 'allergy_to_ingredient_categories');
    }

    /**
     * save Allowed Diets
     * TODO: used in admin once. Maybe move to service
     * @param $diets
     * @return $this
     */
    public function saveAllowedDiets($diets): Allergy
    {
        $this->allowedDiets()->detach();

        if (is_null($diets)) {
            return $this;
        }

        $this->allowedDiets()->attach($diets);

        return $this;
    }

    /**
     * save Ingredients
     * TODO: used in admin once. Maybe move to service
     * @param $ingredients
     * @return $this
     */
    public function saveIngredients($ingredients): Allergy
    {
        $this->ingredients()->detach();

        if (is_null($ingredients)) {
            return $this;
        }

        $this->ingredients()->attach($ingredients);

        return $this;
    }

    /**
     * Get allergy allowed diets
     */
    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'allergy_to_ingredients');
    }
}
