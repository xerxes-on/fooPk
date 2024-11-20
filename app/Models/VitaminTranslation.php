<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class VitaminTranslation
 *
 * @package App\Models
 * @property int $id
 * @property int $vitamin_id
 * @property string $locale
 * @property string $name
 * @method static \Illuminate\Database\Eloquent\Builder|VitaminTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VitaminTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VitaminTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|VitaminTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VitaminTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VitaminTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VitaminTranslation whereVitaminId($value)
 * @mixin \Eloquent
 */
class VitaminTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['name'];
}
