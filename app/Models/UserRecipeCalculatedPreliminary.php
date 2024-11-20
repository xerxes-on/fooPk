<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserRecipeCalculatedPreliminary
 *
 * @property int $user_id
 * @property array $valid
 * @property int $counted
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @package App\Models
 * @property int $id
 * @property array|null $invalid
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserRecipeCalculatedPreliminary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserRecipeCalculatedPreliminary newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserRecipeCalculatedPreliminary query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserRecipeCalculatedPreliminary whereCounted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRecipeCalculatedPreliminary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRecipeCalculatedPreliminary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRecipeCalculatedPreliminary whereInvalid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRecipeCalculatedPreliminary whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRecipeCalculatedPreliminary whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRecipeCalculatedPreliminary whereValid($value)
 * @mixin \Eloquent
 */
class UserRecipeCalculatedPreliminary extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_recipe_calculated_preliminaries';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'valid',
        'invalid',
        'counted'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'valid'   => 'array',
        'invalid' => 'array'
    ];
}
