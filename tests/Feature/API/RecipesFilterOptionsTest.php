<?php

namespace Tests\Feature\API;

use App\Models;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * @testdox Tests for GET /api/v1/recipes-filter-options/
 */
class RecipesFilterOptionsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /**
     * @test
     * @testdox Filter options should be available only to authenticated users.
     */
    public function access(): void
    {
        $response = $this->getJson('/api/v1/recipes-filter-options/');

        $response->assertUnauthorized();
    }

    /**
     * @test
     * @testdox Proper data structure is expected.
     */
    public function dataStructure(): void
    {
        $user   = Models\User::factory()->create();
        $recipe = Models\Recipe::factory()->create();
        $user->allRecipes()->attach($recipe);
        $season     = Models\Seasons::create(['name' => 'Winter']);
        $ingredient = \Modules\Ingredient\Models\Ingredient::where('id', 1)->first();
        $season->ingredients()->attach($ingredient);
        $ingredient->recipesAsStatic()->attach($recipe);
        $ingestion = Models\Ingestion::create(['active' => true, 'key' => 'snack', 'title' => 'Snack']);
        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/v1/recipes-filter-options/');

        $response->assertJsonFragment([
            'ingestions' => [
                ['id' => $ingestion->id, 'key' => 'snack', 'title' => 'Snack'],
            ],
            'complexities' => [
                ['id' => 1, 'title' => 'Easy'],
                ['id' => 2, 'title' => 'Medium'],
                ['id' => 3, 'title' => 'Complicated'],
            ],
            'costs' => [
                ['id' => 1, 'title' => '$', 'min_price' => 1, 'max_price' => 10],
                ['id' => 2, 'title' => '$$', 'min_price' => 11, 'max_price' => 20],
                ['id' => 3, 'title' => '$$$', 'min_price' => 21, 'max_price' => 50],
            ],
            'diets' => [
                ['id' => 1, 'name' => 'Vegetarian'],
                ['id' => 2, 'name' => 'Vegan'],
                ['id' => 3, 'name' => 'Paleo'],
                ['id' => 4, 'name' => 'Cow\'s milk'],
                ['id' => 5, 'name' => 'Goat milk'],
                ['id' => 6, 'name' => 'Sheep\'s milk'],
                ['id' => 7, 'name' => 'FODMAP'],
                ['id' => 8, 'name' => 'AIP'],
                ['id' => 9, 'name' => 'Bulletproof'],
                ['id' => 10, 'name' => 'Nightshades'],
            ],
            'seasons' => [
                ['id' => $season->id, 'name' => 'Winter'],
            ]
        ]);
    }

    /**
     * @test
     * @testdox Only seasons relevant to a user should be listed.
     */
    public function relevantSeasons(): void
    {
        $user   = Models\User::factory()->create();
        $recipe = Models\Recipe::factory()->create();
        $user->allRecipes()->attach($recipe);
        $included   = Models\Seasons::create(['name' => 'Winter']);
        $ingredient = \Modules\Ingredient\Models\Ingredient::where('id', 1)->first();
        $included->ingredients()->attach($ingredient);
        $ingredient->recipesAsStatic()->attach($recipe);
        $excluded = Models\Seasons::create(['name' => 'Summer']);
        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/v1/recipes-filter-options/');

        $response->assertJsonFragment(['name' => 'Winter']);
        $response->assertJsonMissing(['name' => 'Summer']);
    }
}
