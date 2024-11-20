<?php

namespace Modules\Ingredient\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\{Builder, Model, Relations\BelongsTo};

/**
 * Ingredient Hint model
 *
 * @property int $id
 * @property int $ingredient_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Modules\Ingredient\Models\Ingredient $ingredient
 * @property-read \Modules\Ingredient\Models\IngredientHintTranslation|null $translation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Ingredient\Models\IngredientHintTranslation> $translations
 * @property-read int|null $translations_count
 * @method static Builder|IngredientHint listsTranslations(string $translationField)
 * @method static Builder|IngredientHint newModelQuery()
 * @method static Builder|IngredientHint newQuery()
 * @method static Builder|IngredientHint notTranslatedIn(?string $locale = null)
 * @method static Builder|IngredientHint orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static Builder|IngredientHint orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder|IngredientHint orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static Builder|IngredientHint query()
 * @method static Builder|IngredientHint translated()
 * @method static Builder|IngredientHint translatedIn(?string $locale = null)
 * @method static Builder|IngredientHint whereCreatedAt($value)
 * @method static Builder|IngredientHint whereId($value)
 * @method static Builder|IngredientHint whereIngredientId($value)
 * @method static Builder|IngredientHint whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static Builder|IngredientHint whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder|IngredientHint whereUpdatedAt($value)
 * @method static Builder|IngredientHint withTranslation()
 * @mixin \Eloquent
 */
final class IngredientHint extends Model implements TranslatableContract
{
    use Translatable;

    /**
     * @var array<int, string>
     */
    public $translatedAttributes = ['content', 'link_text', 'link_url'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ingredient_hints';


    /**
     * The relations to eager load on every query.
     *
     * @var array<string>
     */
    protected $with = ['translations'];

    /**
     * Ingredients relation.
     * @return BelongsTo<Ingredient,IngredientHint>
     */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
