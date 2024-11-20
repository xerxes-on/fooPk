<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Ratings
 *
 * @package App\Models
 * @property int $id
 * @property int $user_id
 * @property int $recipe_id
 * @property int $rating
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Ratings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ratings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ratings query()
 * @method static \Illuminate\Database\Eloquent\Builder|Ratings whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ratings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ratings whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ratings whereRecipeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ratings whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ratings whereUserId($value)
 * @mixin \Eloquent
 */
class Ratings extends Model
{
    protected $table = 'ratings';

    protected $fillable = [
        'user_id',
        'recipe_id',
        'rating'
    ];
}
