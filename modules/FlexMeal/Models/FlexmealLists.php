<?php

declare(strict_types=1);

namespace Modules\FlexMeal\Models;

use App\Models\Ingestion;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany, HasOne};
use Neko\Stapler\ORM\{EloquentTrait, StaplerableInterface};

/**
 * Flexmeal recipe model.
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $name
 * @property string|null $mealtime
 * @property string|null $notes
 * @property string|null $image_file_name
 * @property int|null $image_file_size
 * @property string|null $image_content_type
 * @property mixed|null $image_updated_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Ingestion|null $ingestion
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\FlexMeal\Models\Flexmeal> $ingredients
 * @property-read int|null $ingredients_count
 * @property-read User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|FlexmealLists newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FlexmealLists newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FlexmealLists query()
 * @method static \Illuminate\Database\Eloquent\Builder|FlexmealLists whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FlexmealLists whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FlexmealLists whereImageContentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FlexmealLists whereImageFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FlexmealLists whereImageFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FlexmealLists whereImageUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FlexmealLists whereMealtime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FlexmealLists whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FlexmealLists whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FlexmealLists whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FlexmealLists whereUserId($value)
 * @mixin \Eloquent
 */
final class FlexmealLists extends Model implements StaplerableInterface
{
    use EloquentTrait {
        EloquentTrait::getAttribute as getAttributeStapler;
        EloquentTrait::setAttribute as setAttributeStapler;
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'flexmeal_lists';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'mealtime',
        'notes',
        'image'
    ];

    /**
     * DiaryData constructor.
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile(
            'image',
            [
                'styles' => [
                    'large' => [
                        'dimensions'      => '1200x1200',
                        'convert_options' => ['quality' => 90],
                    ],
                    'mobile' => [
                        'dimensions'      => '540x1080', // archive
                        'convert_options' => ['quality' => 90],
                    ],
                    'small' => [
                        'dimensions'      => '180x360',
                        'convert_options' => ['quality' => 90]
                    ],
                    'small_market' => [
                        'dimensions'      => '150x200',
                        'convert_options' => ['quality' => 90]
                    ],
                    'thumb' => [
                        'dimensions'      => '450x450#',
                        'convert_options' => ['quality' => 90],
                    ],
                    'tiny' => [
                        'dimensions'      => '90x100', // weekly meal plan
                        'convert_options' => ['quality' => 90]
                    ],
                ],
                'url'         => '/uploads/flexmeals/:id/:style/:filename',
                'default_url' => '/images/flexmeal.jpg',
            ]
        );

        parent::__construct($attributes);
    }

    /**
     * Set a given attribute on the model.
     *
     * leave to set Model attribute correctly
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        if (array_key_exists($key, $this->attachedFiles)) {
            if (empty($value)) {
                $value = STAPLER_NULL;
            }
            $this->setAttributeStapler($key, $value); //process by Stapler
            return;
        }
        return parent::setAttribute($key, $value);
    }

    /**
     * Owner & creator of current flexmeal.
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    /**
     * get Date FlexMeal List
     */
    public function getDate(): string
    {
        return date('d.m.Y', strtotime($this->updated_at));
    }

    /**
     * Flexmeals ingredients amounts.
     */
    public function ingredients(): HasMany
    {
        return $this->hasMany(Flexmeal::class, 'list_id', 'id');
    }

    /**
     * Meal type a flexmeal is suitable for.
     */
    public function ingestion(): HasOne
    {
        return $this->hasOne(Ingestion::class, 'key', 'mealtime');
    }

    /**
     * Calculate nutritional data for a flexmeal on the fly.
     *
     * For other kinds of recipes such data is precalculated,
     * and is stored in UserRecipeCalculated::recipe_data.
     */
    public function getNutritionalData(): array
    {
        $nutritionalData = [
            'calculated_KH' => 0,
            'calculated_EW' => 0,
            'calculated_F'  => 0,
        ];
        $ingredientCollection = $this->ingredients()
            ->with(['ingredient', 'ingredient.unit'])
            ->get();

        foreach ($ingredientCollection as $ingredientAmount) {
            $ingredient = $ingredientAmount->ingredient;
            $multiplier = $ingredientAmount->amount / $ingredient->unit->default_amount;
            $nutritionalData['calculated_KH'] += $multiplier * $ingredient->carbohydrates;
            $nutritionalData['calculated_EW'] += $multiplier * $ingredient->proteins;
            $nutritionalData['calculated_F'] += $multiplier * $ingredient->fats;
        }

        /**
         * Calculate calories by specified formular:
         * fat grams * 9 plus carb grams * 4 + protein grams * 4
         * @note doest rely on specific data stated in the DB.
         */
        $nutritionalData['calculated_KCal'] = round(
            $nutritionalData['calculated_F'] * 9 + $nutritionalData['calculated_KH'] * 4 + $nutritionalData['calculated_EW'] * 4,
            2
        );

        return $nutritionalData;
    }

    /**
     * Get all current attributes on the model.
     *
     * Allows to correctly obtain attributes for STAPLER package as it collects
     *
     * @see https://github.com/CodeSleeve/laravel-stapler/issues/64#issuecomment-338445440
     * @note Must not be removed.
     * @return array
     */
    public function getAttributes()
    {
        return parent::getAttributes();
    }
}
