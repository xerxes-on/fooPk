<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\IngestionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Meal type.
 *
 * @property-read \App\Models\Recipe $recipes
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @package App\Models
 * @property int $id
 * @property string $key
 * @property int $active
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $title
 * @property-read int|null $recipes_count
 * @property-read \App\Models\IngestionTranslation|null $translation
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\IngestionTranslation[] $translations
 * @property-read int|null $translations_count
 * @method static \Illuminate\Database\Eloquent\Builder|TranslatableStaplerModel listsTranslations(string $translationField)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingestion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ingestion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TranslatableStaplerModel notTranslatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|TranslatableStaplerModel orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|TranslatableStaplerModel orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|TranslatableStaplerModel orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|Ingestion query()
 * @method static \Illuminate\Database\Eloquent\Builder|TranslatableStaplerModel translated()
 * @method static \Illuminate\Database\Eloquent\Builder|TranslatableStaplerModel translatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingestion whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingestion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingestion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingestion whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingestion whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TranslatableStaplerModel whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder|TranslatableStaplerModel whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingestion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TranslatableStaplerModel withTranslation()
 * @method static \Illuminate\Database\Eloquent\Builder active() set by scope to find active Ingestions
 * @method static \Illuminate\Database\Eloquent\Builder|Ingestion ofKey(?string $key)
 * @method static \Illuminate\Database\Eloquent\Builder|Ingestion ofIds(array $ids)
 * @mixin \Eloquent
 */
final class Ingestion extends TranslatableStaplerModel
{
    /**
     * @var array<int,string>
     */
    public $translatedAttributes = ['title'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ingestions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'active',
        'key'
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['translations'];

    /**
     * Get the recipes for the RecipeInventory.
     */
    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'recipes_to_ingestions');
    }

    /**
     * Scope a query to only include active ingestions.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Scope a query to only include active ingestions.
     */
    public function scopeOfKey(Builder $query, ?string $key): Builder
    {
        return $query->where('key', $key);
    }

    /**
     * Scope a query to only include ingestions with certain IDs.
     */
    public function scopeOfIds(Builder $query, array $ids): Builder
    {
        return $query->whereIn('id', $ids);
    }

    public static function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return app(IngestionService::class)->getAll();
    }

    public static function getAllActive(): \Illuminate\Database\Eloquent\Collection
    {
        return app(IngestionService::class)->getAllActive();
    }
}
