<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use App\Models;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * @testdox Tests for POST /api/v1/replace-recipe/.
 */
class RecipeReplacementTest extends TestCase
{
    use RefreshDatabase;

    protected bool                  $seed = true;
    private \App\Services\IngestionService $ingestions_repo;

    public function setUp(): void
    {
        parent::setUp();
        $this->ingestions_repo = $this->app->make('App\Services\IngestionService');
    }

    /**
     * @test
     * @testdox Only authenticated users should be able to replace recipes.
     */
    public function access(): void
    {
        $response = $this->json(
            'POST',
            '/api/v1/replace-recipe/',
            []
        );

        $response->assertUnauthorized();
    }

    /**
     * @test
     * @testdox There should be parameters to find meal and replacement recipe.
     */
    public function wrongInput(): void
    {
        $user = Models\User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $response = $this->json(
            'POST',
            '/api/v1/replace-recipe/',
            [
                'new_recipe_id' => 'not-id',
                'ingestion_key' => 2,
                'meal_date'     => '2022-09-16',
            ]
        );

        $response->assertUnprocessable();
    }

    /**
     * @test
     * @testdox If there's no recipe there should be an error.
     */
    public function missingRecipe(): void
    {
        $user = Models\User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $response = $this->json(
            'POST',
            '/api/v1/replace-recipe/',
            [
                'new_recipe_id' => 23000,
                'ingestion_key' => 'lunch',
                'meal_date'     => '2022-09-16',
            ]
        );

        $response->assertNotFound();
        $response->assertJson(['message' => 'Fail finding recipe with ID 23000.']);
    }

    /**
     * @test
     * @testdox If a recipe has no calculations, there should be an error.
     */
    public function missingCalculations(): void
    {
        $user   = Models\User::factory()->create();
        $recipe = Models\Recipe::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $response = $this->json(
            'POST',
            '/api/v1/replace-recipe/',
            [
                'new_recipe_id' => $recipe->id,
                'ingestion_key' => 'lunch',
                'meal_date'     => '2022-09-16',
            ]
        );

        $response->assertNotFound();
        $response->assertJson(['message' => 'There\'re no calculations for the recipe.']);
    }

    /**
     * @test
     * @testdox If a meal to be changed is absent, there should be an error.
     */
    public function missingMeal(): void
    {
        $user   = Models\User::factory()->create();
        $recipe = Models\Recipe::factory()->create();
        Models\UserRecipeCalculated::factory()->create([
            'user_id'      => $user->id,
            'recipe_id'    => $recipe->id,
            'ingestion_id' => 2,
        ]);
        Sanctum::actingAs($user, ['*']);

        $response = $this->json(
            'POST',
            '/api/v1/replace-recipe/',
            [
                'new_recipe_id' => $recipe->id,
                'ingestion_key' => 'lunch',
                'meal_date'     => '2022-09-16',
            ]
        );

        $response->assertNotFound();
        $response->assertJson(['message' => 'There\'s no planned lunch on 2022-09-16.']);
    }

    /**
     * @test
     * @testdox A recipe should be replaced.
     */
    public function success(): void
    {
        $user      = Models\User::factory()->create();
        $oldRecipe = Models\Recipe::factory()->create();
        $newRecipe = Models\Recipe::factory()->create();
        Models\UserRecipeCalculated::factory()->create([
            'user_id'      => $user->id,
            'recipe_id'    => $newRecipe->id,
            'ingestion_id' => 2,
        ]);
        $user->meals()->create([
            'recipe_id'    => $oldRecipe->id,
            'ingestion_id' => 2,
            'meal_time'    => 'lunch',
            'meal_date'    => '2022-09-16',
            'cooked'       => false,
            'eat_out'      => false,
        ]);
        Sanctum::actingAs($user, ['*']);

        $response = $this->json(
            'POST',
            '/api/v1/replace-recipe/',
            [
                'new_recipe_id' => $newRecipe->id,
                'ingestion_key' => 'lunch',
                'meal_date'     => '2022-09-16',
            ]
        );
        $meal = $user->meals()->first();

        $response->assertOk();
        $this->assertEquals($meal->recipe_id, $newRecipe->id);
    }

    /**
     * @test
     * @testdox A recipe should be replaced in purchase list as well.
     */
    public function purchaseList(): void
    {
        $ingestion = $this->ingestions_repo->getByKey('lunch');
        $user      = Models\User::factory()->create();
        $oldRecipe = Models\Recipe::factory()->create();
        $newRecipe = Models\Recipe::factory()->create();
        Models\UserRecipeCalculated::factory()->create([
            'user_id'      => $user->id,
            'recipe_id'    => $newRecipe->id,
            'ingestion_id' => $ingestion->id,
        ]);
        $user->meals()->create([
            'recipe_id'    => $oldRecipe->id,
            'ingestion_id' => $ingestion->id,
            'meal_time'    => 'lunch',
            'meal_date'    => '2022-09-16',
            'cooked'       => false,
            'eat_out'      => false,
        ]);
        Sanctum::actingAs($user, ['*']);
        // TODO: Will not work. method doesnt exist
        $user->purchaseList->addRecipeToList($oldRecipe->id, null, '2022-09-16', $ingestion->id);

        $response = $this->json(
            'POST',
            '/api/v1/replace-recipe/',
            [
                'new_recipe_id' => $newRecipe->id,
                'ingestion_key' => 'lunch',
                'meal_date'     => '2022-09-16',
            ]
        );
        // TODO: Will not work. method doesnt exist
        $replacement = $user->purchaseList->getRecipePurchaseByMeal(Carbon::parse('2022-09-16'), $ingestion);
        $removed     = !$user->purchaseList->recipes()->where('recipe_id', $oldRecipe->id)->exists();

        $response->assertOk();
        $this->assertTrue($removed);
        $this->assertEquals($replacement->recipe_id, $newRecipe->id);
    }
}
