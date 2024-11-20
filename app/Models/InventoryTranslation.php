<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class InventoryTranslation
 *
 * @package App\Models
 * @property int $id
 * @property int $inventory_id
 * @property string $locale
 * @property string $title
 * @property string|null $tags
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryTranslation whereInventoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryTranslation whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InventoryTranslation whereTitle($value)
 * @mixin \Eloquent
 */
class InventoryTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'title',
        'tags'
    ];
}
