<?php

declare(strict_types=1);

namespace App\Models;

use App\Http\Traits\HasAllowedIngestions;
use App\Http\Traits\HasProfileImage;
use App\Http\Traits\Questionnaire\Model\HasQuestionnaire;
use App\Http\Traits\Recipe\Model\HasExcludedRecipes;
use App\Http\Traits\Recipe\Model\HasFavoriteRecipes;
use App\Http\Traits\Scope\UserModelDeprecations;
use App\Http\Traits\Scope\UserModelScope;
use App\Http\Traits\Subscription\Model\HasChargebeeSubscription;
use App\Http\Traits\Subscription\Model\HasSubscription;
use App\Notifications\MailResetPasswordToken;
use App\Notifications\VerifyEmailOverApiNotification;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Traits\HasWallet;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, HasMany};
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Course\Models\Course;
use Modules\Course\Traits\HasCourse;
use Modules\FlexMeal\Models\FlexmealLists;
use Modules\Foodpoints\Models\FoodpointsDistribution;
use Modules\Ingredient\Traits\HasUserSpecificIngredients;
use Modules\PushNotification\Traits\HasPushNotifications;
use Modules\ShoppingList\Traits\HasShoppingList;
use Spatie\Permission\Traits\HasRoles;

/**
 * Class User
 *
 * TODO: Consider refactoring by splitting into smaller module trait
 * TODO: severe problem with formular as it provides a lot of duplicated queries, need to cache it
 *
 * @property int $id
 * @property string $first_name
 * @property string|null $last_name
 * @property string $email
 * @property string|null $email_verified_at
 * @property string|null $chargebee_id
 * @property string $password
 * @property array|null $dietdata
 * @property string|null $remember_token
 * @property string $lang
 * @property bool $calc_auto
 * @property bool $status
 * @property bool $allow_marketing User agreement to send various marketing data
 * @property string|null $profile_picture_path
 * @property string $push_notifications User allowed notifications - all: all notifications, important: only important notifications, null: no notifications
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property mixed $avatar
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserSubscription> $activeSubscriptions
 * @property-read int|null $active_subscriptions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Recipe> $allRecipes
 * @property-read int|null $all_recipes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Recipe> $allRecipesPure
 * @property-read int|null $all_recipes_pure_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Chargebee\Models\ChargebeeSubscription> $assignedChargebeeSubscriptions
 * @property-read int|null $assigned_chargebee_subscriptions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Allergy> $bulkExclusions
 * @property-read int|null $bulk_exclusions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Chargebee\Models\ChargebeeSubscription> $chargebeeSubscriptions
 * @property-read int|null $chargebee_subscriptions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ClientNote> $clientNotes
 * @property-read int|null $client_notes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Course> $courses
 * @property-read int|null $courses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CustomRecipeCategory> $customRecipeCategories
 * @property-read int|null $custom_recipe_categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CustomRecipe> $customRecipes
 * @property-read int|null $custom_recipes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CustomRecipe> $datedCustomRecipes
 * @property-read int|null $dated_custom_recipes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\PushNotification\Models\UserDevice> $devices
 * @property-read int|null $devices_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DiaryData> $diaryDates
 * @property-read int|null $diary_dates_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RecipeDistributionToUser> $distributions
 * @property-read int|null $distributions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Ingredient\Models\Ingredient> $excludedIngredients
 * @property-read int|null $excluded_ingredients_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Recipe> $excludedRecipes
 * @property-read int|null $excluded_recipes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Recipe> $favorites
 * @property-read int|null $favorites_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, FlexmealLists> $flexmealLists
 * @property-read int|null $flexmeal_lists_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, FoodpointsDistribution> $foodpointsDistribution
 * @property-read int|null $foodpoints_distribution_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Formular> $formulars
 * @property-read int|null $formulars_count
 * @property-read array $allowed_ingestion_ids
 * @property-read array $allowed_ingestion_keys
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ingestion> $allowed_ingestions
 * @property-read string|null $avatar_url
 * @property-read string $avatar_url_or_blank
 * @property-read string $balance
 * @property-read int $balance_int
 * @property-read \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null $challenge
 * @property-read \Modules\Course\Models\Course|null $course
 * @property-read mixed $formular
 * @property-read string $full_name
 * @property-read bool $has_changed_meal_per_day
 * @property-read array|null $latest_questionnaire_answers
 * @property-read array|null $latest_questionnaire_diets
 * @property-read array|null $latest_questionnaire_excluded_ingredients
 * @property-read array|null $latest_questionnaire_full_answers
 * @property-read string|null $latest_questionnaire_goal
 * @property-read array|null $previous_approved_questionnaire_answers
 * @property-read bool $questionnaire_approved
 * @property-read \App\Models\UserSubscription|null $subscription
 * @property-read \Bavix\Wallet\Models\Wallet $wallet
 * @property-read \App\Models\Questionnaire|null $latestQuestionnaireRelation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Admin> $liableAdmin
 * @property-read int|null $liable_admin_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserRecipe> $meals
 * @property-read int|null $meals_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\PushNotification\Models\Notification> $notificationsContent
 * @property-read int|null $notifications_content_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Recipe> $originalRecipes
 * @property-read int|null $original_recipes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, FlexmealLists> $plannedFlexmeals
 * @property-read int|null $planned_flexmeals_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Post> $posts
 * @property-read int|null $posts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserRecipeCalculatedPreliminary> $preliminaryCalc
 * @property-read int|null $preliminary_calc_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\Ingredient\Models\Ingredient> $prohibitedIngredients
 * @property-read int|null $prohibited_ingredients_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\PushNotification\Models\UserNotification> $pushNotifications
 * @property-read int|null $push_notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Questionnaire> $questionnaire
 * @property-read int|null $questionnaire_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Recipe> $ratings
 * @property-read int|null $ratings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Recipe> $recipes
 * @property-read int|null $recipes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Recipe> $recipesByChallenge
 * @property-read int|null $recipes_by_challenge_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserRecipeCalculated> $recipesCalculated
 * @property-read int|null $recipes_calculated_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Modules\ShoppingList\Models\ShoppingList|null $shoppingList
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserSubscription> $subscriptions
 * @property-read int|null $subscriptions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Bavix\Wallet\Models\Transaction> $transactions
 * @property-read int|null $transactions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Bavix\Wallet\Models\Transfer> $transfers
 * @property-read int|null $transfers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Bavix\Wallet\Models\Transaction> $walletTransactions
 * @property-read int|null $wallet_transactions_count
 * @method static Builder|User active()
 * @method static Builder|User calculatedCustomRecipeByID(int $recipeID)
 * @method static Builder|User calculatedCustomRecipeData(int $recipeId)
 * @method static Builder|User calculatedCustomRecipesForDatePeriod(string $dateStart, string $dateEnd)
 * @method static Builder|User calculatedRecipeByID(int $recipeID)
 * @method static Builder|User calculatedRecipeData(int $recipeId)
 * @method static Builder|User calculatedRecipesForDatePeriod(string $dateStart, string $dateEnd)
 * @method static Builder|User customPlannedRecipe(int $id)
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static Builder|User latestBaseQuestionnaire()
 * @method static Builder|User latestBaseQuestionnaireWithAllRequiredAnswers()
 * @method static Builder|User latestQuestionnaire()
 * @method static Builder|User latestQuestionnaireSpecificAnswer(int $questionId)
 * @method static Builder|User marketingQuestionnaire()
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User ofEmail(string $email)
 * @method static Builder|User permission($permissions)
 * @method static Builder|User plannedFlexmealsForDatePeriod(string $dateStart, string $dateEnd)
 * @method static Builder|User plannedRecipes(int $recipe_id, string $date, int $ingestionId)
 * @method static Builder|User query()
 * @method static Builder|User recipeWithCalculations(int $recipeId)
 * @method static Builder|User role($roles, $guard = null)
 * @method static Builder|User searchBy(?array $conditions)
 * @method static Builder|User shoppingListWithIngredientsAndCategory()
 * @method static Builder|User whereAllowMarketing($value)
 * @method static Builder|User whereCalcAuto($value)
 * @method static Builder|User whereChargebeeId($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereDietdata($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereFirstName($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereLang($value)
 * @method static Builder|User whereLastName($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereProfilePicturePath($value)
 * @method static Builder|User wherePushNotifications($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereStatus($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
final class User extends Authenticatable implements Wallet, HasLocalePreference, MustVerifyEmail
{
    use Notifiable;
    use HasRoles;
    use HasWallet;
    use HasFactory;
    use HasFavoriteRecipes;
    use HasChargebeeSubscription;
    use HasProfileImage;
    use HasApiTokens;
    use HasQuestionnaire;
    use HasShoppingList;
    use UserModelScope;
    use UserModelDeprecations;
    use HasCourse;
    use HasUserSpecificIngredients;
    use HasExcludedRecipes;
    use HasSubscription;
    use HasPushNotifications;
    use HasAllowedIngestions;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'dietdata',
        'lang',
        'calc_auto',
        'status',
        'chargebee_id',
        'push_notifications',
        'profile_picture_path',
        'allow_marketing',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int,string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'avatar'          => 'image',
        'dietdata'        => 'array',
        'calc_auto'       => 'boolean',
        'status'          => 'boolean',
        'know_us'         => 'array',
        'allow_marketing' => 'boolean',
    ];

    /**
     * Get user full name
     * @note used only in one place
     */
    public function getFullNameAttribute(): string
    {
        return "$this->first_name $this->last_name";
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailOverApiNotification($this));
    }

    /**
     * Override to fix error 'Trait method getAttribute'
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        // need for edit user
        if ($key == 'role') {
            $role = $this?->roles?->pluck('id')->toArray();
            return $role[0] ?? null;
        }

        if ($key == 'role_name') {
            $role = $this?->roles?->pluck('name')->toArray();
            return $role[0] ?? null;
        }

        if ($key == 'excluded_ingredients') {
            return $this->excludedIngredients()->pluck('ingredients.id');
        }

        if ($key == 'excluded_recipes') {
            return $this->excludedRecipes()->pluck('recipe_id');
        }

        if ($key == 'active_challenge') {
            return (int)!is_null($this->subscription);
        }

        return parent::getAttribute($key);
    }

    /**
     * relation get diaryDates
     */
    public function diaryDates(): HasMany
    {
        return $this->hasMany(DiaryData::class);
    }

    /**
     * relation get Posts
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * relation for client notes
     */
    public function clientNotes(): HasMany
    {
        return $this->hasMany(ClientNote::class, 'client_id');
    }

    /**
     * relation get Custom Recipes
     */
    public function customRecipes(): HasMany
    {
        return $this->hasMany(CustomRecipe::class);
    }

    /**
     * Recipes for planned users meals.
     */
    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'recipes_to_users')
            ->withPivot('meal_date', 'meal_time', 'cooked', 'eat_out');
    }

    /**
     * All recipe custom categories (hashtags) created by a user.
     */
    public function customRecipeCategories(): HasMany
    {
        return $this->hasMany(CustomRecipeCategory::class, 'user_id', 'id');
    }

    /**
     * Planned users meals.
     */
    public function meals(): HasMany
    {
        return $this->hasMany(UserRecipe::class, 'user_id', 'id');
    }

    /**
     * All users recipes.
     */
    public function allRecipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'user_recipe')->with('ingestions');
    }

    /**
     * All users recipes pure, without translations.
     */
    public function allRecipesPure(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'user_recipe')->withTimestamps();
    }

    /**
     * *new logic*
     *
     * relations get all recipes by challenge
     */
    public function recipesByChallenge(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'recipes_to_users')
            ->withPivot('meal_date', 'meal_time', 'cooked', 'eat_out');
    }

    /**
     * Common recipes added to meal plan.
     * Pivot contains planned meal details.
     */
    public function originalRecipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'recipes_to_users', 'user_id', 'original_recipe_id')
            ->withPivot('recipe_id', 'original_recipe_id', 'meal_date', 'meal_time', 'cooked', 'eat_out');
    }

    /**
     * Custom users recipes added to meal plan.
     * Pivot contains planned meal details.
     */
    public function datedCustomRecipes(): BelongsToMany
    {
        return $this->belongsToMany(CustomRecipe::class, 'recipes_to_users')
            ->withPivot(
                'recipe_id',
                'custom_recipe_id',
                'original_recipe_id',
                'meal_date',
                'meal_time',
                'cooked',
                'eat_out'
            );
    }

    /**
     * Get all users flexmeals.
     */
    public function flexmealLists(): HasMany
    {
        return $this->hasMany(FlexmealLists::class)->with(['ingestion']);
    }

    /**
     * Get users planned flexmeals.
     */
    public function plannedFlexmeals(): BelongsToMany
    {
        return $this->belongsToMany(FlexmealLists::class, 'recipes_to_users', 'user_id', 'flexmeal_id')
            ->withPivot('meal_date', 'meal_time', 'cooked', 'eat_out');
    }

    /**
     * Recipes a user rated.
     */
    public function ratings(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'ratings')->withTimeStamps();
    }

    /**
     * relation bulk Exclusions
     */
    public function bulkExclusions(): BelongsToMany
    {
        return $this->belongsToMany(Allergy::class, 'user_bulk_exclusions', 'user_id', 'allergy_id');
    }

    /**
     * relation get preliminary Calculation
     */
    public function preliminaryCalc(): HasMany
    {
        return $this->hasMany(UserRecipeCalculatedPreliminary::class);
    }

    /**
     * relation get recipes calculated
     */
    public function recipesCalculated(): HasMany
    {
        return $this->hasMany(UserRecipeCalculated::class);
    }

    /**
     * Send a password reset email to the user
     *
     * @param string $token
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new MailResetPasswordToken($token));
    }

    /**
     * Get the preferred locale of the entity.
     */
    public function preferredLocale(): ?string
    {
        return $this->lang;
    }

    public function liableAdmin()
    {
        return $this->morphedByMany(
            Admin::class,
            'model',
            'consultants_responsibilities',
            'client_id',
            'admin_id',
        );
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(RecipeDistributionToUser::class);
    }

    public function obtainedDistribution(int $distributionId): bool
    {
        return $this->distributions()->where('distribution_id', $distributionId)->exists();
    }

    public function foodpointsDistribution(): HasMany
    {
        return $this->hasMany(FoodpointsDistribution::class);
    }
}
