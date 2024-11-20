<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class IngestionTranslation
 *
 * @package App\Models
 * @property int $id
 * @property int $ingestion_id
 * @property string $locale
 * @property string $title
 * @method static \Illuminate\Database\Eloquent\Builder|IngestionTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IngestionTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IngestionTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|IngestionTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IngestionTranslation whereIngestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IngestionTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IngestionTranslation whereTitle($value)
 * @mixin \Eloquent
 */
class IngestionTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['title'];
}
