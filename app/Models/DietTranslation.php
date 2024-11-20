<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DietTranslation
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $diet_id
 * @property string $locale
 * @property string $name
 * @method static \Illuminate\Database\Eloquent\Builder|DietTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DietTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DietTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|DietTranslation whereDietId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DietTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DietTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DietTranslation whereName($value)
 * @mixin \Eloquent
 */
class DietTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['name'];
}
