<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class RecipeTagTranslation extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    public $table = 'recipe_tags_translations';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['title'];
}
