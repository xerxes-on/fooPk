<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AllergyTranslation
 *
 * @package App\Models
 * @property int $id
 * @property int $allergy_id
 * @property string $locale
 * @property string $name
 * @method static \Illuminate\Database\Eloquent\Builder|AllergyTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AllergyTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AllergyTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|AllergyTranslation whereAllergyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllergyTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllergyTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllergyTranslation whereName($value)
 * @mixin \Eloquent
 */
class AllergyTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name'
    ];
}
