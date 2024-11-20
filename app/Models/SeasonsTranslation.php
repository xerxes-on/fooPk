<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SeasonsTranslation
 *
 * @package App\Models
 * @property int $id
 * @property int $seasons_id
 * @property string $name
 * @property string $locale
 * @method static \Illuminate\Database\Eloquent\Builder|SeasonsTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SeasonsTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SeasonsTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|SeasonsTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SeasonsTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SeasonsTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SeasonsTranslation whereSeasonsId($value)
 * @mixin \Eloquent
 */
class SeasonsTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['name'];
}
