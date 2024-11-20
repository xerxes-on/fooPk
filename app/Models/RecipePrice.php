<?php

declare(strict_types=1);

namespace App\Models;

use App\Events;
use App\Services\Recipe\RecipePriceService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class RecipePrice
 *
 * @property-read \App\Models\Recipe $recipe
 * @property-read \App\Models\RecipePriceCategory $price
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @package App\Models
 * @property int $id
 * @property string $title
 * @property float|null $min_price
 * @property float|null $max_price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|RecipePrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipePrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipePrice query()
 * @method static \Illuminate\Database\Eloquent\Builder|RecipePrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipePrice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipePrice whereMaxPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipePrice whereMinPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipePrice whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RecipePrice whereUpdatedAt($value)
 * @mixin \Eloquent
 */
final class RecipePrice extends Model
{
    protected $table = 'recipe_prices';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'min_price',
        'max_price',
    ];

    /**
     * The event map for the model.
     *
     * Allows for object-based events for native Eloquent events.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'saved'   => Events\RecipeProcessed::class,
        'deleted' => Events\RecipeProcessed::class,
    ];

    /**
     * relation get Recipe
     */
    public function recipe(): HasOne
    {
        return $this->hasOne(Recipe::class, 'id', 'recipe_id');
    }

    /**
     * relation get Price
     */
    public function price(): HasOne
    {
        return $this->hasOne(RecipePriceCategory::class, 'id', 'price_id');
    }

    public static function getAll(): \Illuminate\Database\Eloquent\Collection
    {
        return app(RecipePriceService::class)->getAll();
    }
}
