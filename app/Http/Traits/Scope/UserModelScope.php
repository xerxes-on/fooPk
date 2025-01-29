<?php

declare(strict_types=1);

namespace App\Http\Traits\Scope;

use App\Enums\Admin\Client\Filters\ClientConsultantFilterEnum;
use App\Enums\Admin\Client\Filters\ClientFormularFilterEnum;
use App\Enums\Admin\Client\Filters\ClientSubscriptionFilterEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Modules\Chargebee\Enums\ClientChargebeeSubscriptionFilter;

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
                    $subQuery->where('users.id', 'LIKE', "%{$whereValue}%")
                        ->orWhere('users.first_name', 'LIKE', "%{$whereValue}%")
                        ->orWhere('users.last_name', 'LIKE', "%{$whereValue}%")
                        ->orWhere('users.email', 'LIKE', "%{$whereValue}%");
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
                            'users.*',
                            'questionnaires.id as formular_id',
                            'questionnaires.user_id',
                            'questionnaires.is_approved'
                        ]
                    )
                    ->join('questionnaires', function (JoinClause $join) {
                        $join
                            ->on('users.id', '=', 'questionnaires.user_id')
                            ->whereRaw(
                                sprintf(
                                    '%1$s.id = (select MAX(%1$s.id) from %1$s where %1$s.user_id = %2$s.id)',
                                    'questionnaires',
                                    'users',
                                )
                            )
                            ->where(
                                'questionnaires.is_approved',
                                '=',
                                0,
                            );
                    }),
                ClientFormularFilterEnum::APPROVED->value => $query
                    ->select(
                        [
                            'users.*',
                            'questionnaires.id as formular_id',
                            'questionnaires.user_id',
                            'questionnaires.is_approved'
                        ]
                    )
                    ->join('questionnaires', function (JoinClause $join) {
                        $join
                            ->on('users.id', '=', 'questionnaires.user_id')
                            ->whereRaw(
                                sprintf(
                                    '%1$s.id = (select MAX(%1$s.id) from %1$s where %1$s.user_id = %2$s.id)',
                                    'questionnaires',
                                    'users',
                                )
                            )
                            ->where(
                                'questionnaires.is_approved',
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
                ClientChargebeeSubscriptionFilter::MISSING->value => $query
                    ->whereDoesntHave(
                        'assignedChargebeeSubscriptions'
                    ),
                ClientChargebeeSubscriptionFilter::EXIST->value => $query
                    ->whereHas('assignedChargebeeSubscriptions', function (Builder $query) {
                        $query
                            ->whereRaw(
                                sprintf(
                                    '%1$s.id = (select MAX(%1$s.id) from %1$s where %1$s.assigned_user_id = %2$s.id)',
                                    'chargebee_subscriptions',
                                    'users'
                                )
                            )
                            ->whereJsonContains('data->status', 'active');
                    }),
                ClientChargebeeSubscriptionFilter::MULTIPLE_ACTIVE->value => $query
                    ->whereHas('assignedChargebeeSubscriptions', function (Builder $query) {
                        $query
                            ->selectSub(
                                sprintf(
                                    'select count(%1$s.id) from %1$s where %2$s.id = %1$s.assigned_user_id and JSON_VALUE(%1$s.data, \'$.status\') = \'active\'',
                                    'chargebee_subscriptions',
                                    'users'
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

        # find by courses
        if (array_key_exists('courses', $conditions)) {
            $whereValue = $conditions['courses'];
            $query->when(
                $whereValue,
                function ($subQuery, $whereValue) {
                    $subQuery->whereHas(
                        'courses',
                        function ($q) use ($whereValue) {
                            $q->where('course_id', $whereValue);
                        }
                    );
                },
                function ($subQuery) {
                    $subQuery->doesntHave('courses');
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
                'user_recipe_calculated',
                'recipes.id',
                '=',
                'user_recipe_calculated.recipe_id'
            )
            ->leftJoin(
                'ingestions',
                'user_recipe_calculated.ingestion_id',
                '=',
                'ingestions.id'
            )
            ->select([
                'recipes.*',
                'user_recipe_calculated.ingestion_id AS calc_ingestion_id',
                'user_recipe_calculated.recipe_data AS calc_recipe_data',
                'user_recipe_calculated.invalid AS calc_invalid',
                'user_recipe_calculated.updated_at AS calc_updated_at',
            ])
            ->where('user_recipe_calculated.user_id', $this->id)
            ->where('user_recipe_calculated.invalid', 0)
            ->where('recipes.id', $recipeId);
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
                'user_recipe_calculated',
                'recipes.id',
                '=',
                'user_recipe_calculated.recipe_id'
            )
            ->leftJoin(
                'ingestions',
                'user_recipe_calculated.ingestion_id',
                '=',
                'ingestions.id'
            )
            ->select(
                'recipes.*',
                'user_recipe_calculated.recipe_data AS calc_recipe_data',
                'user_recipe_calculated.invalid AS calc_invalid',
                'user_recipe_calculated.updated_at AS calc_updated_at',
                'recipes_to_users.meal_date AS meal_date',
                'ingestions.key AS meal_time'
            )
            ->whereColumn(
                'user_recipe_calculated.ingestion_id',
                'recipes_to_users.ingestion_id'
            )
            ->where('user_recipe_calculated.user_id', $this->id)
            ->where('user_recipe_calculated.invalid', 0)
            ->where('recipes_to_users.eat_out', '!=', 1)
            ->whereDate('recipes_to_users.meal_date', '>=', $dateStart)
            ->whereDate('recipes_to_users.meal_date', '<=', $dateEnd);
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
                'user_recipe_calculated',
                'recipes.id',
                '=',
                'user_recipe_calculated.recipe_id'
            )
            ->leftJoin(
                'ingestions',
                'user_recipe_calculated.ingestion_id',
                '=',
                'ingestions.id'
            )
            ->select([
                'recipes.*',
                'user_recipe_calculated.recipe_data AS calc_recipe_data',
                'user_recipe_calculated.invalid AS calc_invalid',
                'user_recipe_calculated.updated_at AS calc_updated_at',
                'recipes_to_users.meal_date AS meal_date',
            ])
            ->whereColumn(
                'user_recipe_calculated.ingestion_id',
                'recipes_to_users.ingestion_id'
            )
            ->where('user_recipe_calculated.user_id', $this->id)
            ->where('user_recipe_calculated.invalid', 0)
            ->where('recipes.id', $recipeID);
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
                'user_recipe_calculated',
                'custom_recipes.id',
                '=',
                'user_recipe_calculated.custom_recipe_id'
            )
            ->leftJoin(
                'ingestions',
                'user_recipe_calculated.ingestion_id',
                '=',
                'ingestions.id'
            )
            ->select([
                'recipes_to_users.custom_recipe_id AS custom_recipe_id',
                'user_recipe_calculated.recipe_data AS calc_recipe_data',
                'user_recipe_calculated.invalid AS calc_invalid',
                'user_recipe_calculated.updated_at AS calc_updated_at',
                'recipes_to_users.meal_date AS meal_date',
            ])
            ->whereColumn(
                'user_recipe_calculated.ingestion_id',
                'recipes_to_users.ingestion_id'
            )
            ->where('user_recipe_calculated.user_id', $this->id)
            ->where('user_recipe_calculated.invalid', 0)
            ->where('custom_recipes.id', $recipeId);
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
                'user_recipe_calculated',
                'custom_recipes.id',
                '=',
                'user_recipe_calculated.custom_recipe_id'
            )
            ->leftJoin(
                'ingestions',
                'user_recipe_calculated.ingestion_id',
                '=',
                'ingestions.id'
            )
            ->select(
                'recipes_to_users.custom_recipe_id AS custom_recipe_id',
                'user_recipe_calculated.recipe_data AS calc_recipe_data',
                'user_recipe_calculated.invalid AS calc_invalid',
                'user_recipe_calculated.updated_at AS calc_updated_at',
                'recipes_to_users.meal_date AS meal_date',
            )
            ->whereColumn(
                'user_recipe_calculated.ingestion_id',
                'recipes_to_users.ingestion_id'
            )
            ->where('user_recipe_calculated.user_id', $this->id)
            ->where('user_recipe_calculated.invalid', 0)
            ->where('recipes_to_users.eat_out', '!=', 1)
            ->whereDate('recipes_to_users.meal_date', '>=', $dateStart)
            ->whereDate('recipes_to_users.meal_date', '<=', $dateEnd);
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
                'user_recipe_calculated',
                'custom_recipes.id',
                '=',
                'user_recipe_calculated.custom_recipe_id'
            )
            ->leftJoin(
                'ingestions',
                'user_recipe_calculated.ingestion_id',
                '=',
                'ingestions.id'
            )
            ->select(
                'recipes_to_users.custom_recipe_id AS custom_recipe_id',
                'user_recipe_calculated.recipe_data AS calc_recipe_data',
                'user_recipe_calculated.invalid AS calc_invalid',
                'user_recipe_calculated.updated_at AS calc_updated_at',
                'recipes_to_users.meal_date AS meal_date'
            )
            ->whereColumn(
                'user_recipe_calculated.ingestion_id',
                'recipes_to_users.ingestion_id'
            )
            ->where('user_recipe_calculated.invalid', 0)
            ->where('user_recipe_calculated.user_id', $this->id)
            ->where('custom_recipes.id', $recipeID);
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
            ->leftJoin('user_recipe_calculated', function ($join) use ($ingestionId) {
                $join
                    ->where('user_recipe_calculated.user_id', '=', $this->id)
                    ->on('user_recipe_calculated.recipe_id', '=', 'recipes.id')
                    ->where('user_recipe_calculated.ingestion_id', '=', $ingestionId);
            })
            ->leftJoin(
                'ingestions',
                'user_recipe_calculated.ingestion_id',
                '=',
                'ingestions.id'
            )
            ->select([
                'recipes.*',
                'user_recipe_calculated.recipe_data AS calc_recipe_data',
                'user_recipe_calculated.invalid AS calc_invalid',
                'user_recipe_calculated.updated_at AS calc_updated_at',
                'recipes_to_users.meal_date AS meal_date'
           ])
            ->whereColumn(
                'user_recipe_calculated.ingestion_id',
                'recipes_to_users.ingestion_id'
            )
            ->where('recipes.id', $recipe_id)
            ->where('recipes_to_users.ingestion_id', $ingestionId)
            //TODO:: @NickMost refactor, trick to show only allowed for user ingestions
            ->whereIn('ingestions'.'.id', $this->allowed_ingestion_ids)
            ->whereDate('recipes_to_users.meal_date', $date);
    }

    public function scopePlannedRecipesForGettingIngredients(
        Builder $query,
        int $recipe_id,
        string $date,
        int $ingestionId
    ): BelongsToMany {
        return $this
            ->recipes()
            ->leftJoin('user_recipe_calculated', function ($join) use ($ingestionId) {
                $join
                    ->where('user_recipe_calculated.user_id', '=', $this->id)
                    ->on('user_recipe_calculated.recipe_id', '=', 'recipes.id')
                    ->where('user_recipe_calculated.ingestion_id', '=', $ingestionId);
            })
            ->leftJoin(
                'ingestions',
                'user_recipe_calculated.ingestion_id',
                '=',
                'ingestions.id'
            )
            ->select('user_recipe_calculated.recipe_data AS calc_recipe_data',)
            ->whereColumn(
                'user_recipe_calculated.ingestion_id',
                'recipes_to_users.ingestion_id'
            )
            ->where('recipes.id', $recipe_id)
            ->where('recipes_to_users.ingestion_id', $ingestionId)
            //TODO:: @NickMost refactor, trick to show only allowed for user ingestions
            ->whereIn('ingestions.id', $this->allowed_ingestion_ids)
            ->whereDate('recipes_to_users.meal_date', $date);
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
                'user_recipe_calculated',
                'custom_recipes.id',
                '=',
                'user_recipe_calculated.custom_recipe_id'
            )
            ->leftJoin(
                'ingestions',
                'user_recipe_calculated.ingestion_id',
                '=',
                'ingestions.id'
            )
            ->select(
                'custom_recipes.*',
                'custom_recipes.recipe_id AS original_recipe_id',
                'recipes_to_users.custom_recipe_id AS custom_recipe_id',
                'user_recipe_calculated.recipe_data AS calc_recipe_data',
                'user_recipe_calculated.invalid AS calc_invalid',
                'user_recipe_calculated.updated_at AS calc_updated_at',
                'recipes_to_users.meal_date AS meal_date',
                'ingestions.key AS meal_time'
            )
            ->whereColumn(
                'user_recipe_calculated.ingestion_id',
                'recipes_to_users.ingestion_id'
            )
            //TODO:: @NickMost refactor, trick to show only allowed for user ingestions
//            ->whereIn('ingestions'.'.id',$this->allowedIngestionsId())
            ->where('user_recipe_calculated.user_id', $this->id)
            ->where('custom_recipes.id', $id);
    }

    public function scopeCustomPlannedRecipeForGettingIngredient(Builder $query, int $id): BelongsToMany
    {
        return $this
            ->datedCustomRecipes()
            ->leftJoin(
                'user_recipe_calculated',
                'custom_recipes.id',
                '=',
                'user_recipe_calculated.custom_recipe_id'
            )
            ->leftJoin(
                'ingestions',
                'user_recipe_calculated.ingestion_id',
                '=',
                'ingestions.id'
            )
            ->select('user_recipe_calculated.recipe_data AS calc_recipe_data',)
            ->whereColumn(
                'user_recipe_calculated.ingestion_id',
                'recipes_to_users.ingestion_id'
            )
            ->where('user_recipe_calculated.user_id', $this->id)
            ->where('custom_recipes.id', $id);
    }

    public function scopePlannedFlexmealForGettingIngredients(
        Builder $query,
        int $id,
        string $date,
        int $ingestionId
    ): BelongsToMany {
        return $this
            ->plannedFlexmeals()
            ->join(
                'user_recipe_calculated',
                'flexmeal_lists.id',
                '=',
                'recipes_to_users.flexmeal_id'
            )
            ->with('ingredients')
            ->select(
                'flexmeal_lists.id'
            )
            ->where('flexmeal_lists.id', $id)
            ->whereDate('recipes_to_users.meal_date', $date)
            ->where('recipes_to_users.ingestion_id', $ingestionId);
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
                'user_recipe_calculated',
                'recipes.id',
                '=',
                'user_recipe_calculated.recipe_id'
            )
            ->leftJoin(
                'ingestions',
                'user_recipe_calculated.ingestion_id',
                '=',
                'ingestions.id'
            )
            ->leftJoin(
                'user_excluded_recipes',
                function ($join) {
                    $join->on('recipes.id', '=', 'user_excluded_recipes.recipe_id')
                        ->on('user_recipe_calculated.user_id', '=', 'user_excluded_recipes.user_id');
                }
            )
            ->select(
                'recipes.*',
                'user_recipe_calculated.ingestion_id AS calc_ingestion_id',
                'user_recipe_calculated.recipe_data AS calc_recipe_data',
                'user_recipe_calculated.invalid AS calc_invalid',
                'user_excluded_recipes.recipe_id AS excluded',
                'user_recipe_calculated.updated_at AS calc_updated_at',
                'ingestions.key AS meal_time'
            )
            ->where('user_recipe_calculated.user_id', $this->id)
            ->where('recipes.id', $recipeId)
            ->where('user_recipe_calculated.recipe_data', '!=', '')
            ->where('user_recipe_calculated.recipe_data', '!=', '[]')
            //TODO:: @NickMost refactor, trick to show only allowed for user ingestions
//            ->whereIn('ingestions'.'.id',$this->allowedIngestionsId())
            ->whereIn('user_recipe_calculated.id', $preferedUserRecipeCalculatedIds);
    }
}
