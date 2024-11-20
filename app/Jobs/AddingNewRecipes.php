<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\LanguagesEnum;
use App\Helpers\Calculation;
use App\Http\Traits\CanGetProperty;
use App\Models\{Recipe, RecipeDistribution, User};
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class AddingNewRecipes
 * @package App\Jobs
 */
class AddingNewRecipes implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use CanGetProperty;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly User $user, private readonly string $type, private readonly bool $debug)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $numberNewRecipes = config("adding-new-recipes.{$this->type}.numberNewRecipes");
        $force            = config("adding-new-recipes.{$this->type}.force");
        $configRecord     = RecipeDistribution::where('is_distributed', false)->orderBy('id')->first();

        if (empty($configRecord)) {
            return;
        }
        // Stop distribution if already happened before
        if ($this->user->obtainedDistribution($configRecord->id)) {
            return;
        }

        $this->user->distributions()->create(['distribution_id' => $configRecord->id]);

        $ranges = $configRecord->recipes;
        $ranges = array_map('intval', $ranges);
        $ranges = [$ranges];
        if ($this->debug) {
            logger(json_encode($ranges));
            return;
        }

        $_count = 0;

        foreach ($ranges as $range) {
            if ($_count >= $numberNewRecipes) {
                continue;
            }
            $_count = $this->addingNewRecipesProcess($this->user, $range, $_count);
        }

        if ($force && $_count < $numberNewRecipes) {
            $_count = $this->addingNewRecipesProcess($this->user, [], $_count, false);
        }

        if ($_count < $numberNewRecipes) {
            $textMessage = "User {$this->user->email} (#{$this->user->id}) only added $_count recipes!";
            send_raw_admin_email($textMessage, 'Not enough recipes!');
        }
    }

    /**
     * TODO: consider refactor
     * @note The method has a Cyclomatic Complexity of 16.
     * @note The method has an NPath complexity of 864.
     */
    public function addingNewRecipesProcess(User $user, array $range, int $count = 0, bool $useRange = true): int
    {
        if (!empty($useRange) && empty($range)) {
            return $count;
        }

        $numberNewRecipes = config("adding-new-recipes.{$this->type}.numberNewRecipes");

        # check exist recipe
        $existRecipeIds = $user->allRecipes()->pluck('recipes.id')->toArray();

        $excluded_recipes_ids_by_user_exclusion = [];
        if (!empty($user->excluded_recipes)) {
            $excluded_recipes_ids_by_user_exclusion = $user->excluded_recipes->toArray();
        }

        $allowAnyLangRecipes = true;
        // checking if user's lang is English
        if (isset($user->lang) && $user->lang == LanguagesEnum::EN) {
            // need to get only fully translated recipes
            $allowAnyLangRecipes = false;
        }

        # get all recipes Ids
        $allRecipeIds = Recipe::isActive()
            ->when(
                $useRange,
                function ($query) use ($range) {
                    if (is_array($range) && count($range) > 0) {
                        return $query->whereIn('id', $range);
                    }
                }
            )
            ->whereNotIn('id', $excluded_recipes_ids_by_user_exclusion);

        if ($allowAnyLangRecipes === false) {
            $allRecipeIds = $allRecipeIds->where('translations_done', 1);
        }

        $allRecipeIds = $allRecipeIds->pluck('id')->toArray();

        $diff = array_diff($allRecipeIds, $existRecipeIds);

        # recipe Id randomize
        shuffle($diff);

        while (!empty($diff) && $count < $numberNewRecipes) {
            $recipeId = array_shift($diff);

            # calc recipe to user processed
            $result = Calculation::_calcRecipe2user(
                $user,
                [$recipeId],
                true,
                ['skip_related_recipes' => 1, 'allow_any_lang_recipes' => $allowAnyLangRecipes]
            );

            if (!$result['success'] && is_null($result['IDs'])) {
                continue;
            }
            //info('added: ' .$result['IDs']);
            //info($recipeId);

            $count++;

            $recipe = Recipe::find($recipeId);

            if (empty($recipe->related_recipes)) {
                continue;
            }
            foreach ($recipe->related_recipes as $relatedRecipeId) {
                $relatedRecipeId = (int)$relatedRecipeId;
                $pos             = array_search($relatedRecipeId, $diff);
                if ($pos !== false) {
                    unset($diff[$pos]);
                }
            }
        }

        return $count;
    }
}
