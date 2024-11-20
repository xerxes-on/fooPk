<?php

namespace Modules\Ingredient\Models;

use App\Models\Vitamin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class IngredientVitamin
 *
 * @package App\Models
 * @property int $ingredient_id
 * @property int $vitamin_id
 * @property float|null $value
 * @property-read \Modules\Ingredient\Models\Ingredient|null $ingredient
 * @property-read \App\Models\Vitamin|null $vitamin
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientVitamin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientVitamin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientVitamin query()
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientVitamin whereIngredientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientVitamin whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IngredientVitamin whereVitaminId($value)
 * @mixin \Eloquent
 */
class IngredientVitamin extends Model
{
    protected $table = 'ingredient_vitamins';

    protected $fillable = [
        'ingredient_id',
        'vitamin_id',
        'value'
    ];

    public function ingredient(): HasOne
    {
        return $this->hasOne(Ingredient::class, 'id', 'ingredient_id');
    }

    public function vitamin(): HasOne
    {
        return $this->hasOne(Vitamin::class, 'id', 'vitamin_id');
    }
}
