<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class RoleTranslation
 *
 * @package App\Models
 * @method static \Illuminate\Database\Eloquent\Builder|RoleTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoleTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoleTranslation query()
 * @mixin \Eloquent
 */
class RoleTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['name'];
}
