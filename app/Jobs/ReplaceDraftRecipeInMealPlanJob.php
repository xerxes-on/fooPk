<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\UserRecipe;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Job to replace draft recipe in meal plan for all users.
 *
 * @package App\Jobs
 */
final class ReplaceDraftRecipeInMealPlanJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private array $errors = [];

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly int $recipeId)
    {
        $this->onQueue('high');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        UserRecipe::whereRecipeId($this->recipeId)
            ->with(['user' => function (HasOne $relation) {
                $relation->select('users.id')->with(['excludedRecipes' => function (BelongsToMany $subRelation) {
                    $subRelation->select('recipes.id')->setEagerLoads([]);
                }]);
            }])
            ->chunkById(200, function (Collection $mealPlanRecipe) {
                $mealPlanRecipe->each(function (UserRecipe $recipe) {
                    // Skip if recipe is already excluded.
                    if ($recipe->user->excludedRecipes->contains($recipe->recipe_id)) {
                        return;
                    }
                    $excludedRecipes   = $recipe->user->excludedRecipes->pluck('id')->toArray();
                    $excludedRecipes[] = $recipe->recipe_id;

                    try {
                        $recipe->replaceWithRandom($excludedRecipes, $recipe->user);
                    } catch (\Throwable) {
                        // Cannot replace recipe, delete it and log the error.
                        $recipe->delete();
                        $this->errors[] = $recipe->user->id;
                    }
                });
            });

        $this->maybeReportErrors();
    }

    private function maybeReportErrors(): void
    {
        if ($this->errors === []) {
            return;
        }

        \Log::error("Draft recipe #$this->recipeId was not replaced for the following users", $this->errors);
    }
}
