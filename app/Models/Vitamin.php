<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Ingredient\Models\Ingredient;
use Modules\Ingredient\Models\IngredientVitamin;

/**
 * Class Vitamin
 *
 * @package App\Models
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Modules\Ingredient\Models\Ingredient[] $ingredients
 * @property-read int|null $ingredients_count
 * @property-read \App\Models\VitaminTranslation|null $translation
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\VitaminTranslation[] $translations
 * @property-read int|null $translations_count
 * @method static \Illuminate\Database\Eloquent\Builder|Vitamin listsTranslations(string $translationField)
 * @method static \Illuminate\Database\Eloquent\Builder|Vitamin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vitamin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vitamin notTranslatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Vitamin orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Vitamin orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Vitamin orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|Vitamin query()
 * @method static \Illuminate\Database\Eloquent\Builder|Vitamin translated()
 * @method static \Illuminate\Database\Eloquent\Builder|Vitamin translatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Vitamin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vitamin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vitamin whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder|Vitamin whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Vitamin whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vitamin withTranslation()
 * @mixin \Eloquent
 */
final class Vitamin extends Model implements TranslatableContract
{
    use Translatable;

    /**
     * @var array<int, string>
     */
    public $translatedAttributes = ['name'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'vitamins';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name'];

    /**
     * relation Get the ingredients for the IngredientVitamin.
     */
    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'ingredient_vitamins')->withPivot('value');
    }

    /**
     * Add new vitamin to all ingredients
     */
    public function addVitaminToOldIngredients(int $vitamin_id = null): void
    {
        if (is_null($vitamin_id)) {
            $vitamin_id = $this->id;
        }

        $ingredients = Ingredient::setEagerLoads([])->get(['id']);
        $connections = [];
        foreach ($ingredients as $ingredient) {
            $connections[] = [
                'ingredient_id' => $ingredient->id,
                'vitamin_id'    => $vitamin_id,
                'value'         => 0,
            ];
        }

        // save
        IngredientVitamin::insert($connections);
    }
}
