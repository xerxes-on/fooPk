<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\DietService;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Diet
 *
 * @property int $id
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\DietTranslation|null $translation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DietTranslation> $translations
 * @property-read int|null $translations_count
 * @method static \Illuminate\Database\Eloquent\Builder|Diet listsTranslations(string $translationField)
 * @method static \Illuminate\Database\Eloquent\Builder|Diet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Diet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Diet notTranslatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Diet orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Diet orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Diet orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|Diet query()
 * @method static \Illuminate\Database\Eloquent\Builder|Diet translated()
 * @method static \Illuminate\Database\Eloquent\Builder|Diet translatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Diet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Diet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Diet whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Diet whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder|Diet whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Diet whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Diet withTranslation()
 * @mixin \Eloquent
 */
final class Diet extends Model implements TranslatableContract
{
    use Translatable;

    /**
     * Diet Vegan
     *
     * @var string
     */
    public const DIET_VEGAN = 'vegan';

    /**
     * Diet AIP
     *
     * @var string
     */
    public const DIET_AIP = 'aip';

    /**
     * @var array<int,string>
     */
    public array $translatedAttributes = ['name'];

    /**
     * The relations to eager load on every query.
     *
     * @var array<int,string>
     */
    protected $with = ['translations'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'diets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['slug'];

    public static function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return app(DietService::class)->getAll();
    }
}
