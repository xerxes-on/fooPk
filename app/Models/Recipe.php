<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Recipe\RecipeStatusEnum;
use App\Events;
use App\Http\Traits\Recipe\Model\HasCustomCategories;
use App\Http\Traits\Recipe\Model\HasDiets;
use App\Http\Traits\Recipe\Model\HasIngestions;
use App\Http\Traits\Recipe\Model\HasIngredients;
use App\Http\Traits\Recipe\Model\HasInventory;
use App\Http\Traits\Recipe\Model\HasRelatedRecipes;
use App\Http\Traits\Recipe\Model\HasSeasons;
use App\Http\Traits\Recipe\Model\HasSteps;
use App\Http\Traits\Recipe\Model\HasTags;
use App\Http\Traits\Scope\RecipeModelScope;
use Illuminate\Database\Eloquent\{Builder, Collection as EloquentCollection};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, HasMany, HasOne};
use Illuminate\Support\Facades\Auth;

/**
 * Common recipe.
 *
 * @property int $id
 * @property \App\Models\User|null $creator
 * @property int|null $complexity_id
 * @property int|null $price_id
 * @property float|null $proteins
 * @property float|null $fats
 * @property float|null $carbohydrates
 * @property float|null $cooking_time
 * @property string|null $unit_of_time
 * @property float|null $calories
 * @property float|null $max_kh
 * @property float|null $min_kh
 * @property float|null $max_kcal
 * @property float|null $min_kcal
 * @property string|null $image_file_name
 * @property int|null $image_file_size
 * @property string|null $image_content_type
 * @property string|null $image_updated_at
 * @property RecipeStatusEnum $status
 * @property bool $translations_done Flag to indicate that recipe translations are fully completed
 * @property array|null $related_recipes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read EloquentCollection<int, \App\Models\CustomRecipeCategory> $allCustomCategories
 * @property-read int|null $all_custom_categories_count
 * @property-read EloquentCollection<int, \App\Models\User> $allUsers
 * @property-read int|null $all_users_count
 * @property-read EloquentCollection<int, \App\Models\UserRecipeCalculated> $calculations
 * @property-read int|null $calculations_count
 * @property-read \App\Models\RecipeComplexity|null $complexity
 * @property-read EloquentCollection<int, \App\Models\Diet> $diets
 * @property-read int|null $diets_count
 * @property-read \App\Models\Favorite|null $favorite
 * @property-read EloquentCollection<int, \App\Models\Ingestion> $ingestions
 * @property-read \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|null $related
 * @property-read array $related_scope
 * @property-read EloquentCollection<int, \App\Models\Seasons> $seasons
 * @property-read int|null $ingestions_count
 * @property-read EloquentCollection<int, \Modules\Ingredient\Models\Ingredient> $ingredients
 * @property-read int|null $ingredients_count
 * @property-read EloquentCollection<int, \App\Models\Inventory> $inventories
 * @property-read int|null $inventories_count
 * @property-read EloquentCollection<int, \App\Models\User> $plannedForUsers
 * @property-read int|null $planned_for_users_count
 * @property-read \App\Models\RecipePrice|null $price
 * @property-read EloquentCollection<int, \App\Models\RecipeTag> $publicTags
 * @property-read int|null $public_tags_count
 * @property-read int|null $seasons_count
 * @property-read EloquentCollection<int, \App\Models\RecipeStep> $steps
 * @property-read int|null $steps_count
 * @property-read EloquentCollection<int, \App\Models\RecipeTag> $tags
 * @property-read int|null $tags_count
 * @property-read \App\Models\RecipeTranslation|null $translation
 * @property-read EloquentCollection<int, \App\Models\RecipeTranslation> $translations
 * @property-read int|null $translations_count
 * @property-read EloquentCollection<int, \Modules\Ingredient\Models\Ingredient> $variableIngredients
 * @property-read int|null $variable_ingredients_count
 * @method static \Database\Factories\RecipeFactory factory($count = null, $state = [])
 * @method static Builder|Recipe inActiveStatus()
 * @method static Builder|Recipe isActive()
 * @method static Builder|Recipe isDraft()
 * @method static Builder|Recipe isOutdated()
 * @method static Builder|TranslatableStaplerModel listsTranslations(string $translationField)
 * @method static Builder|Recipe newModelQuery()
 * @method static Builder|Recipe newQuery()
 * @method static Builder|TranslatableStaplerModel notTranslatedIn(?string $locale = null)
 * @method static Builder|TranslatableStaplerModel orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static Builder|TranslatableStaplerModel orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder|TranslatableStaplerModel orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static Builder|Recipe query()
 * @method static Builder|Recipe searchBy(?array $conditions)
 * @method static Builder|TranslatableStaplerModel translated()
 * @method static Builder|TranslatableStaplerModel translatedIn(?string $locale = null)
 * @method static Builder|Recipe whereCalories($value)
 * @method static Builder|Recipe whereCarbohydrates($value)
 * @method static Builder|Recipe whereComplexityId($value)
 * @method static Builder|Recipe whereCookingTime($value)
 * @method static Builder|Recipe whereCreatedAt($value)
 * @method static Builder|Recipe whereCreator($value)
 * @method static Builder|Recipe whereFats($value)
 * @method static Builder|Recipe whereId($value)
 * @method static Builder|Recipe whereImageContentType($value)
 * @method static Builder|Recipe whereImageFileName($value)
 * @method static Builder|Recipe whereImageFileSize($value)
 * @method static Builder|Recipe whereImageUpdatedAt($value)
 * @method static Builder|Recipe whereMaxKcal($value)
 * @method static Builder|Recipe whereMaxKh($value)
 * @method static Builder|Recipe whereMinKcal($value)
 * @method static Builder|Recipe whereMinKh($value)
 * @method static Builder|Recipe wherePriceId($value)
 * @method static Builder|Recipe whereProteins($value)
 * @method static Builder|Recipe whereRelatedRecipes($value)
 * @method static Builder|Recipe whereStatus($value)
 * @method static Builder|TranslatableStaplerModel whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static Builder|TranslatableStaplerModel whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static Builder|Recipe whereTranslationsDone($value)
 * @method static Builder|Recipe whereUnitOfTime($value)
 * @method static Builder|Recipe whereUpdatedAt($value)
 * @method static Builder|TranslatableStaplerModel withTranslation()
 * @mixin \Eloquent
 */
final class Recipe extends TranslatableStaplerModel
{
    use HasFactory;
    use RecipeModelScope;
    use HasInventory;
    use HasRelatedRecipes;
    use HasIngestions;
    use HasDiets;
    use HasIngredients;
    use HasSteps;
    use HasSeasons;
    use HasTags;
    use HasCustomCategories;

    /**
     * @var array<string>
     */
    public $translatedAttributes = ['title'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'recipes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'creator',
        'complexity_id',
        'price_id',
        'proteins',
        'fats',
        'carbohydrates',
        'calories',
        'min_kcal',
        'max_kcal',
        'min_kh',
        'max_kh',
        'cooking_time',
        'unit_of_time',
        'image',
        'status',
        'translations_done',
        'related_recipes'
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array<int,string>
     */
    protected $with = ['translations'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'status'            => RecipeStatusEnum::class,
        'translations_done' => 'boolean',
        'related_recipes'   => 'array'
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
     * Bootstrap the model and its traits.
     */
    public static function boot(): void
    {
        parent::boot();
        // Clean model relations
        self::deleting(static function (Recipe $model) {
            // TODO: clean all possible places, @NickMost, please review as well
            \DB::transaction(
                static function () use ($model) {
                    $model->ingredients()->sync([]);
                    \DB::table('recipe_variable_ingredients')->where('recipe_id', $model->id)->delete();
                    $model->diets()->sync([]);
                    $model->inventories()->sync([]);
                    $model->seasons()->sync([]);
                    $model->steps()->delete();
                },
                config('database.transaction_attempts')
            );
        });
    }

    /**
     * Recipe constructor.
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile(
            'image',
            [
                'styles' => [
                    'large' => [
                        'dimensions'      => '900',
                        'convert_options' => ['quality' => 100]
                    ],
                    'medium' => [
                        'dimensions'      => '900x300#',
                        'convert_options' => ['quality' => 100]
                    ],
                    'medium_square' => [
                        'dimensions'      => '1200x1200',
                        'convert_options' => ['quality' => 100]
                    ],
                    'mobile' => [
                        'dimensions'      => '300x300#',
                        'convert_options' => ['quality' => 100]
                    ],
                    'small' => [
                        'dimensions'      => '540x1080',
                        'convert_options' => ['quality' => 100]
                    ],
                    'small_all' => [
                        'dimensions'      => '540x600',
                        'convert_options' => ['quality' => 100]
                    ],
                    'small_market' => [
                        'dimensions'      => '450x600',
                        'convert_options' => ['quality' => 100]
                    ],
                    'thumb' => [
                        'dimensions'      => '150x150#',
                        'convert_options' => ['quality' => 100]
                    ],
                    'tiny' => [
                        'dimensions'      => '270x300',
                        'convert_options' => ['quality' => 100]
                    ],
                ],
                'url'         => '/uploads/recipe/:id/:style/:filename',
                'default_url' => config('stapler.api_url') . '/150/00a65a/ffffff/?text=R'
            ]
        );

        parent::__construct($attributes);
    }

    /**
     * Trait collision override for get Attribute.
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (array_key_exists($key, $this->attachedFiles)) {
            return $this->getAttributeStapler($key); //process by Stapler
        }

        if (in_array($key, $this->translatedAttributes)) {
            return $this->getAttributeTranslatable($key); //process by Translatable
        }

        if ($key === 'inventory') {
            return $this->inventories()->pluck('inventory_id');
        }

        return parent::getAttribute($key);
    }

    /**
     * relation get creator
     */
    public function creator(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'creator');
    }

    /**
     * relation get Complexity
     */
    public function complexity(): HasOne
    {
        return $this->hasOne(RecipeComplexity::class, 'id', 'complexity_id');
    }

    /**
     * relation get Price
     */
    public function price(): HasOne
    {
        return $this->hasOne(RecipePrice::class, 'id', 'price_id');
    }

    /**
     * Users who planned meals with current recipe.
     */
    public function plannedForUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'recipes_to_users');
    }

    /**
     * new logic relations from All Recipes
     */
    public function allUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_recipe');
    }

    /**
     * Check whether recipe (or its related ones) marked as Favorite for user.
     */
    public function favorited(): bool
    {
        return Favorite::where('user_id', Auth::id())
            ->whereIn('recipe_id', $this->relatedScope)
            ->exists();
    }

    /**
     * Is recipe favorite relation.
     */
    public function favorite(): HasOne
    {
        return $this->hasOne(Favorite::class, 'recipe_id')->where('user_id', Auth::id());
    }

    /**
     * Calculations for current recipe for various users.
     */
    public function calculations(): HasMany
    {
        return $this->hasMany(UserRecipeCalculated::class, 'recipe_id', 'id');
    }
}
