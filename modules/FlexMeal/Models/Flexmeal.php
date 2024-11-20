<?php

declare(strict_types=1);

namespace Modules\FlexMeal\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Ingredient\Models\Ingredient;

/**
 * Users Flexmeal Model.
 *
 * @property int $id
 * @property int $list_id
 * @property int $amount
 * @property int|null $ingredient_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Ingredient|null $ingredient
 * @property-read \Modules\FlexMeal\Models\FlexmealLists $list
 * @method static \Illuminate\Database\Eloquent\Builder|Flexmeal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Flexmeal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Flexmeal query()
 * @method static \Illuminate\Database\Eloquent\Builder|Flexmeal whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Flexmeal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Flexmeal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Flexmeal whereIngredientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Flexmeal whereListId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Flexmeal whereUpdatedAt($value)
 * @mixin \Eloquent
 */
final class Flexmeal extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'flexmeal_to_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'list_id',
        'amount',
        'ingredient_id'
    ];

    /**
     * relation get Ingredient
     * @return BelongsTo<FlexmealLists,Flexmeal>
     */
    public function list(): BelongsTo
    {
        return $this->belongsTo(FlexmealLists::class, 'list_id');
    }

    /**
     * relation get Ingredient
     * @return BelongsTo<Ingredient,Flexmeal>
     */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class, 'ingredient_id');
    }
}
