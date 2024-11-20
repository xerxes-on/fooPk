<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Helpers\Calculation;
use App\Http\Traits\CanGetProperty;
use App\Http\Traits\Queue\HandleLastStartedJob;
use App\Listeners\ClearUserCache;
use App\Models\Recipe;
use App\Models\User;
use App\Models\UserRecipe;
use App\Models\UserRecipeCalculatedPreliminary;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Internal\Enums\JobProcessingEnum;
use Modules\Internal\Models\AdminStorage;

/**
 * Updates users preliminary calculations whenever necessary.
 *
 * @package App\Jobs
 */
class PreliminaryCalculation implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use HandleLastStartedJob;
    use CanGetProperty;

    /**
     * Create a new job instance.
     */
    public function __construct(protected User $user, protected bool $allRecipes = false, $relatedJobHash = false, $couldBeInterrupted = true)
    {
        $this->relatedJobHash     = $relatedJobHash;
        $this->couldBeInterrupted = (bool)$couldBeInterrupted;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->initRelatedJobId(JobProcessingEnum::PRELIMINARY_JOB->value);

        # get related group recipe
        //$relatedGroups = array_keys(\Calculation::getRelatedRecipeGroups());

        // formular not exists
        if (!$this->user->isQuestionnaireExist()) {
            return;
        }
        // formular not approved
        if ($this->user->questionnaireApproved !== true) {
            return;
        }

        // TODO:: check cleanup cache
        app(ClearUserCache::class, ['userId' => $this->user->getKey()])->handle();

        # get all recipes
        $allRecipeIds = Recipe::isActive()->pluck('id')->toArray();

        if ($this->allRecipes) {
            $diffRecipes = $allRecipeIds;
        } else {
            # ======= EXCLUSION OF RELATED RECIPES ======= #
            #
            # get exist recipe and calculations

            // TODO:: @NickMost @Andrew review with Barbara, preliminary takes only recipes outside user's scope only
            $existRecipeIds = $this
                ->user
                ->allRecipes()
                ->leftJoin('user_recipe_calculated', 'recipes.id', '=', 'user_recipe_calculated.recipe_id')
                ->where('user_recipe_calculated.user_id', $this->user->getKey())
                // getting all valid recipes for exclusion from preliminary
//                ->where('user_recipe_calculated.invalid', 0)
                ->groupBy('recipes.id')
                ->pluck('related_recipes', 'recipes.id')
                ->toArray();
            if (!empty($existRecipeIds)) {
                $existRecipeIds = array_keys($existRecipeIds);
            } else {
                $existRecipeIds = [];
            }
            # ======= EXCLUSION OF RELATED RECIPES ======= #

            $diffRecipes = array_diff($allRecipeIds, $existRecipeIds);
        }

        // taking new recipes firstly
        rsort($diffRecipes);

        $preliminaryCalcDataRow = $this->user->preliminaryCalc()->first();
        $preliminaryCalcData    = null;
        if (!empty($preliminaryCalcDataRow)) {
            $preliminaryCalcData = $preliminaryCalcDataRow->toArray();
        }

        if ($this->couldBeInterrupted && $this->verifyOrFinishJob(JobProcessingEnum::PRELIMINARY_JOB->value) === false) {
            return;
        }
        Calculation::calcSuitableRecipe2users(
            $this->user,
            $diffRecipes,
            $preliminaryCalcData,
            JobProcessingEnum::PRELIMINARY_JOB->value,
            $this->relatedJobHash,
            $this->couldBeInterrupted
        );
        $this->markInvisible();

        if ($this->couldBeInterrupted && $this->verifyOrFinishJob(JobProcessingEnum::PRELIMINARY_JOB->value) === false) {
            return;
        }
        $this->resolveDraftRecipes();

        $validCalculatedRecipesIds = $this
            ->user
            ->recipesCalculated()
            ->where('invalid', 0)
            ->whereNotNull('recipe_id')
            ->distinct()
            ->pluck('recipe_id')
            ->toArray();

        if (empty($validCalculatedRecipesIds)) {
            $validCalculatedRecipesIds = [];
        }

        $invalidCalculatedRecipesIds = array_values(array_diff($allRecipeIds, $validCalculatedRecipesIds));

        if ($this->couldBeInterrupted && $this->verifyOrFinishJob(JobProcessingEnum::PRELIMINARY_JOB->value) === false) {
            return;
        }
        UserRecipeCalculatedPreliminary::updateOrCreate(
            [
                'user_id' => $this->user->getKey(),
            ],
            [
                'valid'   => $validCalculatedRecipesIds,
                'invalid' => $invalidCalculatedRecipesIds,
                'counted' => count($allRecipeIds)
            ]
        )->touch();

        if ($this->couldBeInterrupted && $this->verifyOrFinishJob(JobProcessingEnum::PRELIMINARY_JOB->value) === false) {
            return;
        }


        // cleanup mealplan for user
        $this->cleanupMealPlanOlderThan();

        AdminStorage::where('key', JobProcessingEnum::PRELIMINARY_JOB->value . $this->user->getKey())->delete();

        // TODO:: check cleanup cache
        app(ClearUserCache::class, ['userId' => $this->user->getKey()])->handle();
    }

    /**
     * Mark recipes that shouldn't be in All/Personal Recipes list (WEB-149).
     */
    private function markInvisible(): void
    {
        $processed = [];
        $invisible = [];
        $recipes   = $this->user
            ->allRecipes()
            ->leftJoin('user_recipe_calculated', function ($join) {
                $join
                    ->on('recipes.id', '=', 'user_recipe_calculated.recipe_id')
                    ->on('user_recipe.user_id', '=', 'user_recipe_calculated.user_id');
            })
            ->select(
                'recipes.id',
                'recipes.related_recipes',
                'user_recipe_calculated.invalid AS calc_invalid',
                'user_recipe_calculated.created_at AS calc_created_at',
            )
            ->groupBy('recipes.id')
            ->get()
            ->keyBy('id');

        foreach ($recipes as $recipe) {
            if (in_array($recipe->id, $processed)) {
                continue; // group with the recipe is dealt with already.
            }

            $group = $recipes
                ->only((array)$recipe->related_recipes)
                ->concat([$recipe]);
            // TODO: array_merge is resource greedy operation in loops. Consider refactor
            $invisible = array_merge($invisible, $this->excludeFromGroup($group));
            $processed = array_merge($processed, $group->pluck('id')->toArray());
        }

        // mark recipes as invisible
        DB::table('user_recipe')
            ->where('user_id', $this->user->id)
            ->where('visible', true)
            ->whereIn('recipe_id', $invisible)
            ->update(['visible' => false]);

        // mark previously invisible recipes as visible
        // that's in case previously invalid recipes became valid.
        DB::table('user_recipe')
            ->where('user_id', $this->user->id)
            ->where('visible', false)
            ->whereNotIn('recipe_id', $invisible)
            ->update(['visible' => true]);
    }

    /**
     * Decide which recipes in a related group not to list.
     *
     * @return array recipes IDs
     */
    private function excludeFromGroup(Collection $group): array
    {
        if ($group->where('calc_invalid', false)->count() > 0) {
            return $group->where('calc_invalid', true)->pluck('id')->toArray();
        }

        if ($group->where('calc_invalid', true)->count() > 0) {
            return $group->sortByDesc('calc_created_at')->slice(1)->pluck('id')->toArray();
        }

        return [];
    }

    // replace in meal plan recipes which are  gte than 6 months
    // delete from meal plan recipes which are lte than 6 months
    private function resolveDraftRecipes()
    {

        // TODO:: @NickMost review and refactor efectively
        $now6MonthsBefore = Carbon::now()->subMonths(6)->startOfMonth()->startOfDay();
        $userId           = $this->user->getKey();
        if (empty($userId)) {
            return;
        }

        $draftRecipeIds = Recipe::isDraft()->orderBy('id')->pluck('id')->toArray();

        if (empty($draftRecipeIds)) {
            return;
        }

        // replacing recipe which are in user's meal plan
        $mealPlanDraftRecipes = UserRecipe::whereUserId($userId)
            ->whereIn('recipe_id', $draftRecipeIds)
            ->where(function ($query) {
                $query->where('custom_recipe_id', null)
                    ->orWhereNull('custom_recipe_id');
            })
            ->orderBy('meal_date')
            ->get()
            ->toArray();


        if (!empty($mealPlanDraftRecipes)) {
            foreach($mealPlanDraftRecipes as $mealTimeEntity) {
                //                dump($mealTimeEntity);
                $mealDate = Carbon::parse($mealTimeEntity['meal_date']);
                // exists in meal plan less than 6 monhts - we can delete it without any worry
                if ($now6MonthsBefore->gte($mealDate)) {
                    UserRecipe::whereUserId($userId)
                        ->where('meal_date', $mealTimeEntity['meal_date'])
                        ->where('meal_time', $mealTimeEntity['meal_time'])
                        ->where('recipe_id', $mealTimeEntity['recipe_id'])
                        ->where(function ($query) {
                            $query->where('custom_recipe_id', 0)
                                ->orWhereNull('custom_recipe_id');
                        })
                        ->delete();
                    //                    dump('deleted',$mealTimeEntity);
                } else {
                    $mealTimeEntityRecord = UserRecipe::whereUserId($userId)
                        ->where('meal_date', $mealTimeEntity['meal_date'])
                        ->where('meal_time', $mealTimeEntity['meal_time'])
                        ->where('recipe_id', $mealTimeEntity['recipe_id'])
                        ->where(function ($query) {
                            $query->where('custom_recipe_id', 0)
                                ->orWhereNull('custom_recipe_id');
                        })
                        ->get();

                    if ($mealTimeEntityRecord) {
                        $replaceResults = Calculation::replaceRecipesInUserFeed($mealTimeEntity['recipe_id'], $userId, $this->user, $now6MonthsBefore);
                        //                        dump('replacing',$replaceResults);
                    }
                }
            }
        }
        // removing recipes which are in user's scope

        $existDraftRecipeIds = $this
            ->user
            ->allRecipes()
            ->whereIn('recipes.id', $draftRecipeIds)
            ->pluck('recipes.id')
            ->toArray();

        if (!empty($existDraftRecipeIds)) {
            DB::table('user_recipe')
                ->where('user_id', $userId)
                ->whereIn('recipe_id', $draftRecipeIds)
                ->delete();
        }

        // delete from calculated table
        DB::table('user_recipe_calculated')
            ->where('user_id', $userId)
            ->whereIn('recipe_id', $draftRecipeIds)
            ->delete();

    }

    /**
     * @param $months amount of monhts
     * @return void
     */
    private function cleanupMealPlanOlderThan($months = 12)
    {

        $userId = $this->user->getKey();
        if (empty($userId)) {
            return;
        }

        $now6MonthsBefore = Carbon::now()->subMonths($months)->startOfMonth()->startOfDay();
        UserRecipe::whereUserId($userId)
            ->where('meal_date', '<', $now6MonthsBefore)
            ->delete();
    }
}
