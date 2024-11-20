<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class RecipePriceCategory
 *
 * @package App\Models
 * @method static \Illuminate\Database\Eloquent\Builder|RecipePriceCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipePriceCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipePriceCategory query()
 * @mixin \Eloquent
 */
class RecipePriceCategory extends Model
{
    protected $table = 'recipe_price_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'min_price',
        'max_price'
    ];

}
