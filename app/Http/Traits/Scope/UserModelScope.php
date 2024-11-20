<?php

declare(strict_types=1);

namespace App\Http\Traits\Scope;

use Modules\Chargebee\Enums\Admin\Client\Filters\ClientChargebeeSubscriptionFilterEnum;
use App\Enums\Admin\Client\Filters\ClientConsultantFilterEnum;
use App\Enums\Admin\Client\Filters\ClientFormularFilterEnum;
use App\Enums\Admin\Client\Filters\ClientSubscriptionFilterEnum;
use App\Enums\DatabaseTableEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

trait UserModelScope
{
    /**
     * Scope search and filter Users
     * TODO: method is too long need refactor
     * TODO: method has a Cyclomatic Complexity of 11
     * TODO: method has an NPath complexity of 768.
     */
    public function scopeSearchBy(Builder $query, ?array $conditions): Builder
    {
        if (empty($conditions)) {
            return $query;
        }

        # find by ID or first_name or last_name or email
        if (array_key_exists('v_search', $conditions) && $conditions['v_search'] != '') {
            $whereValue = $conditions['v_search'];
            $query->where(
                function (Builder $subQuery) use ($whereValue) {
                    $subQuery->where(DatabaseTableEnum::USERS . '.id', 'LIKE', "%{$whereValue}%")
                        ->orWhere(DatabaseTableEnum::USERS . '.first_name', 'LIKE', "%{$whereValue}%")
                        ->orWhere(DatabaseTableEnum::USERS . '.last_name', 'LIKE', "%{$whereValue}%")
                        ->orWhere(DatabaseTableEnum::USERS . '.email', 'LIKE', "%{$whereValue}%");
                }
            );
        }

        # find by Formular status
        if (array_key_exists('formular_approved', $conditions)) {
            match ((int)$conditions['formular_approved']) {
                ClientFormularFilterEnum::MISSING->value      => $query->doesntHave('questionnaire'),
                ClientFormularFilterEnum::NOT_APPROVED->value => $query
                    ->select(
                        [
                            DatabaseTableEnum::USERS . '.*',
                            DatabaseTableEnum::QUESTIONNAIRE . '.id as formular_id',
                            DatabaseTableEnum::QUESTIONNAIRE . '.user_id',
                            DatabaseTableEnum::QUESTIONNAIRE . '.is_approved'
                        ]
                    )
                    ->join(DatabaseTableEnum::QUESTIONNAIRE, function (JoinClause $join) {
                        $join
                            ->on('users.id', '=', DatabaseTableEnum::QUESTIONNAIRE . '.user_id')
                            ->whereRaw(
                                sprintf(
                                    '%1$s.id = (select MAX(%1$s.id) from %1$s where %1$s.user_id = %2$s.id)',
                                    DatabaseTableEnum::QUESTIONNAIRE,
                                    DatabaseTableEnum::USERS,
                                )
                            )
                            ->where(
                                DatabaseTableEnum::QUESTIONNAIRE . '.is_approved',
                                '=',
                                0,
                            );
                    }),
                ClientFormularFilterEnum::APPROVED->value => $query
                    ->select(
                        [
                            DatabaseTableEnum::USERS . '.*',
                            DatabaseTableEnum::QUESTIONNAIRE . '.id as formular_id',
                            DatabaseTableEnum::QUESTIONNAIRE . '.user_id',
                            DatabaseTableEnum::QUESTIONNAIRE . '.is_approved'
                        ]
                    )
                    ->join(DatabaseTableEnum::QUESTIONNAIRE, function (JoinClause $join) {
                        $join
                            ->on('users.id', '=', 'questionnaires.user_id')
                            ->whereRaw(
                                sprintf(
                                    '%1$s.id = (select MAX(%1$s.id) from %1$s where %1$s.user_id = %2$s.id)',
                                    DatabaseTableEnum::QUESTIONNAIRE,
                                    DatabaseTableEnum::USERS,
                                )
                            )
                            ->where(
                                DatabaseTableEnum::QUESTIONNAIRE . '.is_approved',
                                '=',
                                1,
                            );
                    }),
            };
        }

        # find by Subscription
        if (array_key_exists('subscription', $conditions)) {
            match ((int)$conditions['subscription']) {
                ClientSubscriptionFilterEnum::MISSING->value => $query
                    ->whereDoesntHave('subscriptions', function (Builder $query) {
                        $query->where('active', '>', 0);
                    }),
                ClientSubscriptionFilterEnum::EXIST->value => $query->whereHas('subscriptions', function (Builder $query) {
                    $query->where('active', '>', 0);
                }),
            };
        }

        # find by Chargebee Subscription
        if (array_key_exists('chargebee_subscription', $conditions)) {
            match ((int)$conditions['chargebee_subscription']) {
                ClientChargebeeSubscriptionFilterEnum::MISSING->value => $query
                    ->whereDoesntHave(
                        'assignedChargebeeSubscriptions'
                    ),
                ClientChargebeeSubscriptionFilterEnum::EXIST->value => $query
                    ->whereHas('assignedChargebeeSubscriptions', function (Builder $query) {
                        $query
                            ->whereRaw(
                                sprintf(
                                    '%1$s.id = (select MAX(%1$s.id) from %1$s where %1$s.assigned_user_id = %2$s.id)',
                                    DatabaseTableEnum::CHARGEBEE_SUBSCRIPTIONS,
                                    DatabaseTableEnum::USERS
                                )
                            )
                            ->whereJsonContains('data->status', 'active');
                    }),
                ClientChargebeeSubscriptionFilterEnum::MULTIPLE_ACTIVE->value => $query
                    ->whereHas('assignedChargebeeSubscriptions', function (Builder $query) {
                        $query
                            ->selectSub(
                                sprintf(
                                    'select count(%1$s.id) from %1$s where %2$s.id = %1$s.assigned_user_id and JSON_VALUE(%1$s.data, \'$.status\') = \'active\'',
                                    DatabaseTableEnum::CHARGEBEE_SUBSCRIPTIONS,
                                    DatabaseTableEnum::USERS
                                ),
                                'assigned_chargebee_subscriptions_count'
                            )
                            ->having('assigned_chargebee_subscriptions_count', '>', 1);
                    }),
            };
        }

        # find by status
        if (array_key_exists('status', $conditions)) {
            $query->where('status', $conditions['status']);
        }

        # find by marketing agreement
        if (array_key_exists('allow_marketing', $conditions)) {
            $query->where('allow_marketing', $conditions['allow_marketing']);
        }

        # find by user locale
        if (array_key_exists('lang', $conditions)) {
            $query->where('lang', $conditions['lang']);
        }

        # find by user who was/wasn't assigned to a/the consultant
        if (array_key_exists('consultant', $conditions)) {
            if ($conditions['consultant'] === ClientConsultantFilterEnum::NOT_PRESENT->value) {
                $query->whereNotExists(static function ($query) {
                    $query->select('client_id')
                        ->from('consultants_responsibilities')
                        ->whereRaw('consultants_responsibilities.client_id = users.id');
                });
            } elseif ($conditions['consultant'] === ClientConsultantFilterEnum::PRESENT->value) {
                $query->whereExists(static function ($query) {
                    $query->select('client_id')
                        ->from('consultants_responsibilities')
                        ->whereRaw('consultants_responsibilities.client_id = users.id');
                });
            } elseif (is_numeric($conditions['consultant'])) {
                $query->whereExists(static function ($query) use ($conditions) {
                    $query->select('client_id')
                        ->from('consultants_responsibilities')
                        ->whereRaw('consultants_responsibilities.client_id = users.id')
                        ->where('consultants_responsibilities.admin_id', $conditions['consultant']);
                });
            }
        }

        # find by abo_challenge
        if (array_key_exists('abo_challenge', $conditions)) {
            $whereValue = $conditions['abo_challenge'];
            $query->when(
                $whereValue,
                function ($subQuery, $whereValue) {
                    $subQuery->whereHas(
                        'aboChallenges',
                        function ($q) use ($whereValue) {
                            $q->where('course_id', $whereValue);
                        }
                    );
                },
                function ($subQuery) {
                    $subQuery->doesntHave('aboChallenges');
                }
            );
        }

        return $query;
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    /**
     * Scope a query to only include users by a given email.
     */
    public function scopeOfEmail(Builder $query, string $email): Builder
    {
        return $query->where('email', $email);
    }

    /**
     * Scope a query user specific recipe with calculations.
     */
    public function scopeCalculatedRecipeData(Builder $query, int $recipeId): BelongsToMany
    {
        return $this
            ->allRecipes()
            ->leftJoin(
                DatabaseTableEnum::USER_RECIPE_CALCULATED,
                DatabaseTableEnum::RECIPES . '.id',
                '=',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.recipe_id'
            )
            ->leftJoin(
                DatabaseTableEnum::INGESTIONS,
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.ingestion_id',
                '=',
                DatabaseTableEnum::INGESTIONS . '.id'
            )
            ->select([
                DatabaseTableEnum::RECIPES . '.*',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.ingestion_id AS calc_ingestion_id',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.recipe_data AS calc_recipe_data',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.invalid AS calc_invalid',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.updated_at AS calc_updated_at',
            ])
            ->where(DatabaseTableEnum::USER_RECIPE_CALCULATED . '.user_id', $this->id)
            ->where(DatabaseTableEnum::USER_RECIPE_CALCULATED . '.invalid', 0)
            ->where(DatabaseTableEnum::RECIPES . '.id', $recipeId);
    }

    /**
     * Scope a query of users` specific recipes with calculations by a period of time.
     */
    public function scopeCalculatedRecipesForDatePeriod(Builder $query, string $dateStart, string $dateEnd): BelongsToMany
    {
        return $this
            ->recipes()
            ->withPivot('meal_date', 'meal_time', 'cooked')
            ->leftJoin(
                DatabaseTableEnum::USER_RECIPE_CALCULATED,
                DatabaseTableEnum::RECIPES . '.id',
                '=',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.recipe_id'
            )
            ->leftJoin(
                DatabaseTableEnum::INGESTIONS,
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.ingestion_id',
                '=',
                DatabaseTableEnum::INGESTIONS . '.id'
            )
            ->select(
                DatabaseTableEnum::RECIPES . '.*',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.recipe_data AS calc_recipe_data',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.invalid AS calc_invalid',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.updated_at AS calc_updated_at',
                DatabaseTableEnum::RECIPES_TO_USERS . '.meal_date AS meal_date',
                DatabaseTableEnum::INGESTIONS . '.key AS meal_time'
            )
            ->whereColumn(
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.ingestion_id',
                DatabaseTableEnum::RECIPES_TO_USERS . '.ingestion_id'
            )
            ->where(DatabaseTableEnum::USER_RECIPE_CALCULATED . '.user_id', $this->id)
            ->where(DatabaseTableEnum::USER_RECIPE_CALCULATED . '.invalid', 0)
            ->where(DatabaseTableEnum::RECIPES_TO_USERS . '.eat_out', '!=', 1)
            ->whereDate(DatabaseTableEnum::RECIPES_TO_USERS . '.meal_date', '>=', $dateStart)
            ->whereDate(DatabaseTableEnum::RECIPES_TO_USERS . '.meal_date', '<=', $dateEnd);
    }

    /**
     * Scope a query of recipe by id with calculations.
     */
    public function scopeCalculatedRecipeByID(Builder $query, int $recipeID): BelongsToMany
    {
        return $this
            ->recipes()
            ->withPivot('meal_date', 'meal_time', 'cooked')
            ->leftJoin(
                DatabaseTableEnum::USER_RECIPE_CALCULATED,
                DatabaseTableEnum::RECIPES . '.id',
                '=',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.recipe_id'
            )
            ->leftJoin(
                DatabaseTableEnum::INGESTIONS,
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.ingestion_id',
                '=',
                DatabaseTableEnum::INGESTIONS . '.id'
            )
            ->select([
                DatabaseTableEnum::RECIPES . '.*',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.recipe_data AS calc_recipe_data',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.invalid AS calc_invalid',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.updated_at AS calc_updated_at',
                DatabaseTableEnum::RECIPES_TO_USERS . '.meal_date AS meal_date',
            ])
            ->whereColumn(
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.ingestion_id',
                DatabaseTableEnum::RECIPES_TO_USERS . '.ingestion_id'
            )
            ->where(DatabaseTableEnum::USER_RECIPE_CALCULATED . '.user_id', $this->id)
            ->where(DatabaseTableEnum::USER_RECIPE_CALCULATED . '.invalid', 0)
            ->where(DatabaseTableEnum::RECIPES . '.id', $recipeID);
    }

    /**
     * Scope a query custom recipe with calculations.
     */
    public function scopeCalculatedCustomRecipeData(Builder $query, int $recipeId): BelongsToMany
    {
        return $this
            ->datedCustomRecipes()
            ->withPivot('meal_date', 'meal_time', 'cooked')
            ->leftJoin(
                DatabaseTableEnum::USER_RECIPE_CALCULATED,
                DatabaseTableEnum::CUSTOM_RECIPES . '.id',
                '=',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.custom_recipe_id'
            )
            ->leftJoin(
                DatabaseTableEnum::INGESTIONS,
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.ingestion_id',
                '=',
                DatabaseTableEnum::INGESTIONS . '.id'
            )
            ->select([
                DatabaseTableEnum::RECIPES_TO_USERS . '.custom_recipe_id AS custom_recipe_id',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.recipe_data AS calc_recipe_data',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.invalid AS calc_invalid',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.updated_at AS calc_updated_at',
                DatabaseTableEnum::RECIPES_TO_USERS . '.meal_date AS meal_date',
            ])
            ->whereColumn(
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.ingestion_id',
                DatabaseTableEnum::RECIPES_TO_USERS . '.ingestion_id'
            )
            ->where(DatabaseTableEnum::USER_RECIPE_CALCULATED . '.user_id', $this->id)
            ->where(DatabaseTableEnum::USER_RECIPE_CALCULATED . '.invalid', 0)
            ->where(DatabaseTableEnum::CUSTOM_RECIPES . '.id', $recipeId);
    }

    /**
     * Scope a query of custom recipe with calculations for certain date period.
     */
    public function scopeCalculatedCustomRecipesForDatePeriod(Builder $query, string $dateStart, string $dateEnd): BelongsToMany
    {
        return $this
            ->datedCustomRecipes()
            ->withPivot('meal_date', 'meal_time', 'cooked')
            ->leftJoin(
                DatabaseTableEnum::USER_RECIPE_CALCULATED,
                DatabaseTableEnum::CUSTOM_RECIPES . '.id',
                '=',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.custom_recipe_id'
            )
            ->leftJoin(
                DatabaseTableEnum::INGESTIONS,
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.ingestion_id',
                '=',
                DatabaseTableEnum::INGESTIONS . '.id'
            )
            ->select(
                DatabaseTableEnum::RECIPES_TO_USERS . '.custom_recipe_id AS custom_recipe_id',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.recipe_data AS calc_recipe_data',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.invalid AS calc_invalid',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.updated_at AS calc_updated_at',
                DatabaseTableEnum::RECIPES_TO_USERS . '.meal_date AS meal_date',
            )
            ->whereColumn(
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.ingestion_id',
                DatabaseTableEnum::RECIPES_TO_USERS . '.ingestion_id'
            )
            ->where(DatabaseTableEnum::USER_RECIPE_CALCULATED . '.user_id', $this->id)
            ->where(DatabaseTableEnum::USER_RECIPE_CALCULATED . '.invalid', 0)
            ->where(DatabaseTableEnum::RECIPES_TO_USERS . '.eat_out', '!=', 1)
            ->whereDate(DatabaseTableEnum::RECIPES_TO_USERS . '.meal_date', '>=', $dateStart)
            ->whereDate(DatabaseTableEnum::RECIPES_TO_USERS . '.meal_date', '<=', $dateEnd);
    }

    /**
     * Scope a query of custom recipe by ID with calculations.
     */
    public function scopeCalculatedCustomRecipeByID(Builder $query, int $recipeID): BelongsToMany
    {
        return $this
            ->datedCustomRecipes()
            ->withPivot('meal_date', 'meal_time', 'cooked')
            ->leftJoin(
                DatabaseTableEnum::USER_RECIPE_CALCULATED,
                DatabaseTableEnum::CUSTOM_RECIPES . '.id',
                '=',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.custom_recipe_id'
            )
            ->leftJoin(
                DatabaseTableEnum::INGESTIONS,
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.ingestion_id',
                '=',
                DatabaseTableEnum::INGESTIONS . '.id'
            )
            ->select(
                DatabaseTableEnum::RECIPES_TO_USERS . '.custom_recipe_id AS custom_recipe_id',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.recipe_data AS calc_recipe_data',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.invalid AS calc_invalid',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.updated_at AS calc_updated_at',
                DatabaseTableEnum::RECIPES_TO_USERS . '.meal_date AS meal_date'
            )
            ->whereColumn(
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.ingestion_id',
                DatabaseTableEnum::RECIPES_TO_USERS . '.ingestion_id'
            )
            ->where(DatabaseTableEnum::USER_RECIPE_CALCULATED . '.invalid', 0)
            ->where(DatabaseTableEnum::USER_RECIPE_CALCULATED . '.user_id', $this->id)
            ->where(DatabaseTableEnum::CUSTOM_RECIPES . '.id', $recipeID);
    }

    /**
     * Scope a query of planned flexmeal with ingredients for certain date period.
     */
    public function scopePlannedFlexmealsForDatePeriod(Builder $query, string $dateStart, string $dateEnd): BelongsToMany
    {
        return $this
            ->plannedFlexmeals()
            ->withPivot('meal_date', 'meal_time', 'cooked', 'eat_out')
            ->whereDate('meal_date', '>=', $dateStart)
            ->whereDate('meal_date', '<=', $dateEnd)
            ->where('eat_out', '!=', 1)
            ->with('ingredients');
    }

    /**
     * Scope a query for planned recipes.
     */
    public function scopePlannedRecipes(Builder $query, int $recipe_id, string $date, int $ingestionId): BelongsToMany
    {
        return $this
            ->recipes()
            ->withPivot('meal_date', 'meal_time', 'cooked')
            ->leftJoin(DatabaseTableEnum::USER_RECIPE_CALCULATED, function ($join) use ($ingestionId) {
                $join
                    ->where(DatabaseTableEnum::USER_RECIPE_CALCULATED . '.user_id', '=', $this->id)
                    ->on(DatabaseTableEnum::USER_RECIPE_CALCULATED . '.recipe_id', '=', DatabaseTableEnum::RECIPES . '.id')
                    ->where(DatabaseTableEnum::USER_RECIPE_CALCULATED . '.ingestion_id', '=', $ingestionId);
            })
            ->leftJoin(
                DatabaseTableEnum::INGESTIONS,
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.ingestion_id',
                '=',
                DatabaseTableEnum::INGESTIONS . '.id'
            )
            ->select([
                DatabaseTableEnum::RECIPES . '.*',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.recipe_data AS calc_recipe_data',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.invalid AS calc_invalid',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.updated_at AS calc_updated_at',
                DatabaseTableEnum::RECIPES_TO_USERS . '.meal_date AS meal_date'
           ])
            ->whereColumn(
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.ingestion_id',
                DatabaseTableEnum::RECIPES_TO_USERS . '.ingestion_id'
            )
            ->where(DatabaseTableEnum::RECIPES . '.id', $recipe_id)
            ->where(DatabaseTableEnum::RECIPES_TO_USERS . '.ingestion_id', $ingestionId)
            //TODO:: @NickMost refactor, trick to show only allowed for user ingestions
            ->whereIn(DatabaseTableEnum::INGESTIONS.'.id', $this->allowedIngestionsId())
            ->whereDate(DatabaseTableEnum::RECIPES_TO_USERS . '.meal_date', $date);
    }

    /**
     * Scope a query for custom recipe.
     *
     * Use it to get a custom recipe with additional meal pivot and calculations data.
     */
    public function scopeCustomPlannedRecipe(Builder $query, int $id): BelongsToMany
    {
        return $this
            ->datedCustomRecipes()
            ->withPivot('meal_date', 'meal_time', 'cooked')
            ->leftJoin(
                DatabaseTableEnum::USER_RECIPE_CALCULATED,
                DatabaseTableEnum::CUSTOM_RECIPES . '.id',
                '=',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.custom_recipe_id'
            )
            ->leftJoin(
                DatabaseTableEnum::INGESTIONS,
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.ingestion_id',
                '=',
                DatabaseTableEnum::INGESTIONS . '.id'
            )
            ->select(
                DatabaseTableEnum::CUSTOM_RECIPES . '.*',
                DatabaseTableEnum::CUSTOM_RECIPES . '.recipe_id AS original_recipe_id',
                DatabaseTableEnum::RECIPES_TO_USERS . '.custom_recipe_id AS custom_recipe_id',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.recipe_data AS calc_recipe_data',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.invalid AS calc_invalid',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.updated_at AS calc_updated_at',
                DatabaseTableEnum::RECIPES_TO_USERS . '.meal_date AS meal_date',
                DatabaseTableEnum::INGESTIONS . '.key AS meal_time'
            )
            ->whereColumn(
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.ingestion_id',
                DatabaseTableEnum::RECIPES_TO_USERS . '.ingestion_id'
            )
            //TODO:: @NickMost refactor, trick to show only allowed for user ingestions
//            ->whereIn(DatabaseTableEnum::INGESTIONS.'.id',$this->allowedIngestionsId())
            ->where(DatabaseTableEnum::USER_RECIPE_CALCULATED . '.user_id', $this->id)
            ->where(DatabaseTableEnum::CUSTOM_RECIPES . '.id', $id);
    }

    /**
     * Scope a query for common recipe with calculations.
     */
    public function scopeRecipeWithCalculations(Builder $query, int $recipeId): BelongsToMany
    {
        // TODO:: review and refactor ..., trick to get valid recipes first @NickMost @AndreyNuritdinov
        $preferedUserRecipeCalculatedIds = DB::table('user_recipe_calculated')
            ->where('user_id', $this->id)
            ->where('recipe_id', $recipeId)
            ->orderBy('invalid', 'ASC')
            ->get(['id', 'recipe_id'])
            ->unique('recipe_id')
            ->pluck('id')
            ->toArray();

        return $this
            ->allRecipes()
            ->leftJoin(
                DatabaseTableEnum::USER_RECIPE_CALCULATED,
                DatabaseTableEnum::RECIPES . '.id',
                '=',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.recipe_id'
            )
            ->leftJoin(
                DatabaseTableEnum::INGESTIONS,
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.ingestion_id',
                '=',
                DatabaseTableEnum::INGESTIONS . '.id'
            )
            ->leftJoin(
                'user_excluded_recipes',
                function ($join) {
                    $join->on(DatabaseTableEnum::RECIPES . '.id', '=', 'user_excluded_recipes.recipe_id')
                        ->on(DatabaseTableEnum::USER_RECIPE_CALCULATED . '.user_id', '=', 'user_excluded_recipes.user_id');
                }
            )
            ->select(
                DatabaseTableEnum::RECIPES . '.*',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.ingestion_id AS calc_ingestion_id',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.recipe_data AS calc_recipe_data',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.invalid AS calc_invalid',
                'user_excluded_recipes.recipe_id AS excluded',
                DatabaseTableEnum::USER_RECIPE_CALCULATED . '.updated_at AS calc_updated_at',
                DatabaseTableEnum::INGESTIONS . '.key AS meal_time'
            )
            ->where(DatabaseTableEnum::USER_RECIPE_CALCULATED . '.user_id', $this->id)
            ->where(DatabaseTableEnum::RECIPES . '.id', $recipeId)
            ->where(DatabaseTableEnum::USER_RECIPE_CALCULATED . '.recipe_data', '!=', '')
            ->where(DatabaseTableEnum::USER_RECIPE_CALCULATED . '.recipe_data', '!=', '[]')
            //TODO:: @NickMost refactor, trick to show only allowed for user ingestions
//            ->whereIn(DatabaseTableEnum::INGESTIONS.'.id',$this->allowedIngestionsId())
            ->whereIn(DatabaseTableEnum::USER_RECIPE_CALCULATED . '.id', $preferedUserRecipeCalculatedIds);
    }
}
