<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\MealtimeEnum;
use App\Events\RecipeProcessed;
use App\Events\UserRecipeUpdated;
use App\Exceptions\{AlreadyHidden, NoData, PublicException};
use App\Models;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\{Arr, Collection, Facades\DB};
use Log;

/**
 * Repository for recipes.
 * TODO: refactor to service
 *
 * @package App\Repositories
 */
final class Recipes
{
    /**
     * Get all (filtered and paginated) users recipes.
     */
    public function getAll(Models\User $user, int $per_page, array $filters): LengthAwarePaginator
    {
        if (array_key_exists('favorite', $filters) && $filters['favorite']) {
            $favoriteRecipes = $user->favorites()->pluck('recipe_id')->toArray();
            $relatedRecipes  = DB::table('recipes')
                ->select('id', 'related_recipes')
                ->whereIn('id', $favoriteRecipes)
                ->get(['id', 'related_recipes'])
                ->map(fn(\stdClass $collection) => array_map(
                    'intval',
                    [$collection->id, ...!empty($collection->related_recipes) ? (json_decode($collection->related_recipes, true) ?? []) : []]
                ))
                ->flatten()
                ->toArray();
            $filters['favorite'] = array_unique(array_merge($favoriteRecipes, $relatedRecipes));
            sort($filters['favorite']);
        }

        $daysNew = config('foodpunk.days_recipe_is_new');

        $recipes = $user
            ->allRecipes()
            ->with(['diets', 'price', 'complexity', 'tags'])
            ->leftJoin('user_recipe_calculated', 'recipes.id', '=', 'user_recipe_calculated.recipe_id')
            ->leftJoin(
                'user_excluded_recipes',
                function (JoinClause $join) {
                    $join->on('recipes.id', '=', 'user_excluded_recipes.recipe_id')
                        ->on('user_recipe_calculated.user_id', '=', 'user_excluded_recipes.user_id');
                }
            )
            ->select([
                'user_recipe_calculated.invalid AS calc_invalid',
                'user_recipe_calculated.created_at AS calc_created_at',
                'user_recipe_calculated.updated_at AS calc_updated_at',
                'user_excluded_recipes.recipe_id AS excluded',
                'user_recipe.created_at AS added_at',
            ])
            ->selectRaw(
                "(DATEDIFF(CURDATE(), user_recipe.created_at) <= $daysNew AND user_recipe_calculated.invalid = 0) as is_new"
            )
            ->where('user_recipe_calculated.user_id', $user->id)
            ->where('user_recipe.visible', true);

        // special filter for recipe replacement.
        if (array_key_exists('replacement_ingestion', $filters)) {
            $ingestionID = (int)$filters['replacement_ingestion'];

            $recipes = in_array($ingestionID, [MealtimeEnum::LUNCH->value, MealtimeEnum::DINNER->value], true) ?
                $recipes->whereIn('user_recipe_calculated.ingestion_id', [MealtimeEnum::LUNCH->value, MealtimeEnum::DINNER->value]) :
                $recipes->where('user_recipe_calculated.ingestion_id', $ingestionID);

            $recipes = $recipes->where('user_recipe_calculated.invalid', 0);
        }

        // filtering out hidden recipes
        if (!array_key_exists('excluded', $filters)) {
            $recipes->whereNull('user_excluded_recipes.recipe_id');
        } elseif ($filters['excluded']) {
            $recipes->whereNotNull('user_excluded_recipes.recipe_id');
        }


        $isNew = "DATEDIFF(CURDATE(), added_at) <= $daysNew";

        // TODO:: review case when recipe is valid for dinner but invalid for lunch... @NickMost
        // in some parts of UI (list of all recipes, single recipe page) we have dinner/lunch as label,
        // but a lot of cases when dinner or lunch is invalid/unavailable for user
        // internal query for proper processing valid/invalid recipes, because groupBy does not take orderBy
        $preferedUserRecipeCalculatedIds = DB::table('user_recipe_calculated')
            ->where('user_id', $user->id)
            ->orderBy('invalid', 'ASC')
            ->get(['id', 'recipe_id'])
            ->unique('recipe_id')
            ->pluck('id')
            ->toArray();

        $recipes = $recipes
            ->searchBy($filters)
            ->whereIn('user_recipe_calculated.id', $preferedUserRecipeCalculatedIds)
            ->groupBy('recipes.id')
            ->orderBy('user_recipe_calculated.invalid', 'ASC')
            ->orderByRaw("$isNew DESC")
            // TODO:: @NickMost review what is going on here??
            ->orderByRaw('is_new DESC')
            ->orderBy('user_excluded_recipes.recipe_id', 'ASC')
            ->orderBy('added_at', 'DESC');

        return $recipes->paginate($per_page);
    }

    /**
     * Exclude a recipe from meal plan.
     *
     * Also, when recipe is excluded it is substituted with another random one.
     * @throws PublicException
     * @throws AlreadyHidden
     */
    public function excludeRecipe(Models\User $user, Models\Recipe $recipe): void
    {
        $excludedRecipesIds = $user->excludedRecipes()->pluck('recipe_id')->toArray();

        if (in_array($recipe->id, $excludedRecipesIds)) {
            throw new AlreadyHidden(trans('common.cant_hide'));
        }

        $meals = $user
            ->meals()
            ->where(
                [
                    ['recipe_id', $recipe->id],
                    //					['challenge_id', $user?->subscription?->id]
                ]
            )
            ->orderBy('ingestion_id')
            ->get();
        $excludedRecipesIds = array_merge([$recipe->id], $excludedRecipesIds);

        try {
            DB::transaction(
                static function () use ($meals, $user, $excludedRecipesIds) {
                    $user->saveExcludedRecipes($excludedRecipesIds);

                    foreach ($meals as $meal) {
                        $meal->replaceWithRandom($excludedRecipesIds);
                    }
                },
                config('database.transaction_attempts')
            );
        } catch (PublicException $e) {
            throw $e;
        } catch (\Throwable $e) {
            logError($e, ['user' => $user->id, 'recipe' => $recipe->id]);
            throw new PublicException(trans('api.exclude_meal_public_error'));
        }

        Log::channel('excluded_recipes')
            ->info(
                "User #$user->id added recipe #$recipe->id to excluded list.",
                [
                    'excluded_recipe_id'          => $recipe->id,
                    'excluded_related_recipe_ids' => $excludedRecipesIds,
                    'user_id'                     => $user->id
                ]
            );
        RecipeProcessed::dispatch();
        UserRecipeUpdated::dispatch($user);
    }

    /**
     * Remove recipe from exclude list.
     *
     * @throws NoData
     */
    public function removeRecipeFromExcluded(Models\User $user, int $recipeId): void
    {
        // obtain related recipes and form an array of Original and related recipe ids
        $relatedRecipesIds = Models\Recipe::where('id', $recipeId)
            ->pluck('related_recipes')
            ->flatten()
            ->toArray();
        $excludedRecipesIds = array_unique(array_merge([$recipeId], $relatedRecipesIds));
        $excludedRecipesIds = Arr::map($excludedRecipesIds, static fn($value, $key) => (int)$value);

        $deleted = DB::table('user_excluded_recipes')
            ->where('user_id', $user->id)
            ->whereIn('recipe_id', $excludedRecipesIds)
            ->delete();

        if (0 === $deleted) {
            throw new NoData(trans('api.remove_from_excluded_public_error'));
        }

        RecipeProcessed::dispatch();
        Log::channel('excluded_recipes')
            ->info(
                "User #{$user->id} restored recipe #$recipeId from excluded list.",
                [
                    'excluded_recipe_id'          => $recipeId,
                    'excluded_related_recipe_ids' => $excludedRecipesIds,
                    'user_id'                     => $user->id
                ]
            );
    }

    /**
     * Get recipes a user should be able to buy.
     */
    public function getRecipesToBuy(
        Models\User $user,
        array       $filters,
        int         $perPage = 20
    ): ?LengthAwarePaginator {
        $perPage = $perPage < 20 ? 20 : (min($perPage, 40));

        if (array_key_exists('favorite', $filters) && $filters['favorite']) {
            $favoriteRecipes = $user->favorites()->pluck('recipe_id')->toArray();
            $relatedRecipes  = DB::table('recipes')
                ->select('id', 'related_recipes')
                ->whereIn('id', $favoriteRecipes)
                ->get(['id', 'related_recipes'])
                ->map(fn(\stdClass $collection) => array_map(
                    'intval',
                    [$collection->id, ...(json_decode($collection->related_recipes, true) ?? [])]
                ))
                ->flatten()
                ->toArray();
            $filters['favorite'] = array_unique(array_merge($favoriteRecipes, $relatedRecipes));
            sort($filters['favorite']);
        }

        if (is_null($validRecipeIds = $user->preliminaryCalc()->first()?->valid)) {
            return null;
        }
        // TODO: need to user_recipe_calculated_preliminaries in order to perform diff as a subquery
        // for showing to user firstly new recipes
        rsort($validRecipeIds);
        $existRecipeIds = $user->allRecipes()->setEagerLoads([])->pluck('recipes.id')->toArray();
        $diffRelated    = array_values(array_unique(array_diff($validRecipeIds, $existRecipeIds)));
        $recipes        = Models\Recipe::whereIntegerInRaw('recipes.id', $diffRelated)
            ->with([
                'ingestions'          => static fn(Relation $query) => $query->select(['ingestions.id', 'ingestions.key']),
                'diets'               => static fn(Relation $query) => $query->select('diets.id'),
                'ingredients'         => static fn(Relation $query) => $query->select('ingredients.id'),
                'variableIngredients' => static fn(Relation $query) => $query->select('ingredients.id'),
                'complexity'          => static fn(Relation $query) => $query->withOnly('translations')->select('id'),
                'price'               => static fn(Relation $query) => $query->select(['id', 'title']),
            ])
            ->searchBy($filters)
            // TODO:: to think about user_recipe_calculated and what and how it uses
//            ->select([
//                'recipes.id',
//                'recipes.complexity_id',
//                'recipes.price_id',
//                'recipes.cooking_time',
//                'recipes.unit_of_time',
//                'recipes.image_file_name',
//                'user_recipe_calculated.invalid AS calc_invalid',
//                'user_recipe_calculated.ingestion_id',
//            ])
            ->orderBy('recipes.created_at', 'desc');
        return $recipes->paginate($perPage);
    }

    /**
     * Get custom categories of the recipe including its related ones.
     */
    public function getRecipeCustomCategories(Models\Recipe $recipe, Models\User $user): Collection
    {
        return \DB::table('custom_recipe_categories')
            ->join(
                'recipes_to_custom_categories',
                'custom_recipe_categories.id',
                '=',
                'recipes_to_custom_categories.category_id'
            )
            ->whereIn('recipe_id', $recipe->related_scope)
            ->where('user_id', $user->id)
            ->select(
                [
                    'custom_recipe_categories.*',
                    'recipes_to_custom_categories.recipe_id',
                    'recipes_to_custom_categories.category_id'
                ]
            )
            ->get();
    }
}
