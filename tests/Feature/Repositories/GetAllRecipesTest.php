<?php

namespace Tests\Feature\Repositories;

use App\Jobs\PreliminaryCalculation;
use App\Models;
use App\Repositories;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Internal\Models\AdminStorage;
use Tests\TestCase;

/**
 * @testdox Tests for App\Repositories\Recipes::getAll()
 */
class GetAllRecipesTest extends TestCase
{
    use RefreshDatabase;

    protected bool               $seed = true;
    private Repositories\Recipes $recipesRepo;

    public function setUp(): void
    {
        parent::setUp();
        $this->recipesRepo = $this->app->make(Repositories\Recipes::class);
    }

    /**
     * @test
     * @testdox If there're several invalid related recipes, only the latest one should be listed.
     */
    public function several_invalid(): void
    {
        $user       = Models\User::factory()->create();
        $recipes    = Models\Recipe::factory()->count(3)->create();
        $recipesIDs = $recipes->pluck('id')->toArray();

        foreach ($recipes as $recipe) {
            $recipe->related_recipes = array_diff($recipesIDs, [$recipe->id]);
            $recipe->save();
        }

        $user->allRecipes()->attach($recipesIDs);
        $calcData = [
            'user_id'    => $user->id,
            'created_at' => new Carbon(),
            'invalid'    => true,
        ];
        $expected = collect($recipesIDs[0]);

        foreach ($recipesIDs as $recipeID) {
            $calcData['recipe_id']  = $recipeID;
            $calcData['created_at'] = $calcData['created_at']->subDay();
            Models\UserRecipeCalculated::factory()->create($calcData);
        }

        $jobStartHash = AdminStorage::generatePreliminaryJobHash($user->getKey());
        PreliminaryCalculation::dispatchSync($user, false, $jobStartHash);

        $listedIDs = $this->recipesRepo->getAll($user, 5, [])->pluck('id');

        $this->assertEquals($expected, $listedIDs);
    }

    /**
     * @test
     * @testdox If there're related valid and invalid recipes, all invalid ones should be excluded.
     */
    public function valid_n_invalid(): void
    {
        $user       = Models\User::factory()->create();
        $valid      = Models\Recipe::factory()->count(2)->create();
        $invalid    = Models\Recipe::factory()->count(2)->create();
        $related    = $valid->concat($invalid);
        $relatedIDs = $related->pluck('id')->toArray();
        $user->allRecipes()->attach($relatedIDs);
        $expected = $valid->pluck('id');

        foreach ($related as $recipe) {
            $recipe->related_recipes = array_diff($relatedIDs, [$recipe->id]);
            $recipe->save();
        }

        foreach ($valid as $recipe) {
            Models\UserRecipeCalculated::factory()->create(
                ['invalid' => false, 'user_id' => $user->id, 'recipe_id' => $recipe->id]
            );
        }

        foreach ($invalid as $recipe) {
            Models\UserRecipeCalculated::factory()->create(
                ['invalid' => true, 'user_id' => $user->id, 'recipe_id' => $recipe->id]
            );
        }

        $jobStartHash = AdminStorage::generatePreliminaryJobHash($user->getKey());
        PreliminaryCalculation::dispatchSync($user, false, $jobStartHash);

        $listedIDs = $this->recipesRepo->getAll($user, 5, [])->pluck('id');

        $this->assertEquals($expected, $listedIDs);
    }

    /**
     * @test
     * @testdox Recipes should be sorted by dates they were added, not by calculations creation dates.
     */
    public function sortingByDate(): void
    {
        $user                 = Models\User::factory()->create();
        list($first, $second) = Models\Recipe::factory()->count(2)->create();
        $today                = new Carbon();
        $someTimeAgo          = (new Carbon())->subDay(config('foodpunk.days_recipe_is_new') + 1);
        $user->allRecipes()->attach($first, ['created_at' => $today]);
        $user->allRecipes()->attach($second, ['created_at' => $someTimeAgo]);
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $first->id, 'created_at' => $someTimeAgo, 'invalid' => false]
        );
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $second->id, 'created_at' => $today, 'invalid' => false]
        );
        $expected = collect([$first->id, $second->id]);

        $listed = $this->recipesRepo->getAll($user, 2, [])->pluck('id');

        $this->assertEquals($expected, $listed);
    }
}
