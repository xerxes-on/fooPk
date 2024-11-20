<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use App\Models;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * @testdox Tests for GET /api/v1/planned-meal/
 */
class PlannedMealTest extends TestCase
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
     * @testdox A recipe should be available only to authenticated users.
     */
    public function access(): void
    {
        $response = $this->json('GET', '/api/v1/planned-meal/', []);

        $response->assertUnauthorized();
    }

    /**
     * @test
     * @testdox Absence of recipe should be taken into account.
     */
    public function notFound(): void
    {
        $user = Models\User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $response = $this->json(
            'GET',
            '/api/v1/planned-meal/',
            ['id' => 123, 'date' => '2022-09-05', 'ingestion_key' => 'dinner'],
        );

        $response->assertNotFound();
    }

    /**
     * @test
     * @testdox Invalid/missing parameters should result in error.
     */
    public function invalidParameters(): void
    {
        $user = Models\User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $response = $this->json(
            'GET',
            '/api/v1/planned-meal/',
            ['id' => 'not-id', 'ingestion_key' => 'dinner'],
        );

        $response->assertUnprocessable();
    }

    /**
     * @test
     * @testdox A response should contain expected recipe data.
     */
    public function recipeDataStructure(): void
    {
        $complexity = Models\RecipeComplexity::create(['title' => 'Testing']);
        $price      = Models\RecipePrice::create(['title' => '$$', 'min_price' => 1.5, 'max_price' => 3.3]);
        $ingestion1 = $this->ingestions_repo->getByKey('breakfast');
        $user       = Models\User::factory()->create();
        $recipe     = Models\Recipe::factory()
                                   ->create([
                                       'title'         => 'My Recipe',
                                       'cooking_time'  => 2,
                                       'unit_of_time'  => 'minutes',
                                       'complexity_id' => $complexity->id,
                                       'price_id'      => $price->id,
                                   ]);
        Models\Favorite::create(['user_id' => $user->id, 'recipe_id' => $recipe->id]);
        $diet1_id      = $recipe->diets()->create(['name' => 'Meat Diet'])->id;
        $diet2_id      = $recipe->diets()->create(['name' => 'Vegan Diet'])->id;
        $ingestion1_id = $ingestion1->id;
        $recipe->ingestions()->attach($ingestion1);
        $ingestion2_id = $recipe->ingestions()->create(['title' => 'Dinner'])->id;
        $recipe->plannedForUsers()->attach($user, [
            'meal_date'    => '2022-09-05',
            'meal_time'    => 'breakfast',
            'ingestion_id' => $ingestion1_id,
            'cooked'       => true,
            'eat_out'      => false,
        ]);
        Models\UserRecipeCalculated::factory()->create([
            'user_id'      => $user->id,
            'recipe_id'    => $recipe->id,
            'ingestion_id' => $ingestion1_id,
        ]);
        $inventory1_id = $recipe->inventories()->create(['title' => 'Inventory 1', 'tags' => 'A, B, C'])->id;
        $inventory2_id = $recipe->inventories()->create(['title' => 'Inventory 2', 'tags' => 'D, E, F'])->id;
        $step1_id      = $recipe->steps()->create(['description' => 'Take an egg.'])->id;
        $step2_id      = $recipe->steps()->create(['description' => 'Crush it!'])->id;
        Sanctum::actingAs($user, ['*']);

        $response = $this->json(
            'GET',
            '/api/v1/planned-meal/',
            ['id' => $recipe->id, 'date' => '2022-09-05', 'ingestion_key' => 'breakfast']
        );

        $response->assertOk();
        $response->assertJsonFragment([
            'ingestion' => ['id' => 1, 'key' => 'breakfast', 'title' => 'breakfast'],
            'cooked'    => true,
            'eat_out'   => false,
            'meal_date' => '2022-09-05',
            'meal_time' => 'breakfast',
            'recipe'    => [
                'id'           => $recipe->id,
                'title'        => 'My Recipe',
                'calc_invalid' => false,
                'image'        => 'https://via.placeholder.com/150/00a65a/ffffff/?text=R',
                'favourited'   => true,
                'purchased'    => false,
                'ingestions'   => [
                    ['id' => $ingestion1_id, 'title' => 'breakfast', 'key' => 'breakfast'],
                    ['id' => $ingestion2_id, 'title' => 'Dinner', 'key' => ''],
                ],
                'price'        => ['id' => $price->id, 'title' => '$$', 'min_price' => 1.5, 'max_price' => 3.3],
                'complexity'   => ['id' => $complexity->id, 'title' => 'Testing'],
                'cooking_time' => 2,
                'unit_of_time' => 'minutes',
                'diets'        => [
                    ['id' => $diet1_id, 'name' => 'Meat Diet'],
                    ['id' => $diet2_id, 'name' => 'Vegan Diet'],
                ],
                'inventories' => [
                    [
                        'title' => 'Inventory 1',
                        'tags'  => 'A, B, C',
                        'image' => 'https://via.placeholder.com/150/00a65a/ffffff/?text=R'
                    ],
                    [
                        'title' => 'Inventory 2',
                        'tags'  => 'D, E, F',
                        'image' => 'https://via.placeholder.com/150/00a65a/ffffff/?text=R'
                    ],
                ],
                'steps' => [
                    ['id' => $step1_id, 'description' => 'Take an egg.'],
                    ['id' => $step2_id, 'description' => 'Crush it!'],
                ],
            ]
        ]);
    }

    /**
     * @test
     * @testdox A response should contain calculated ingredients.
     */
    public function ingredientsDataStructure(): void
    {
        $ingestion    = $this->ingestions_repo->getByKey('dinner');
        $calculations = Models\UserRecipeCalculated::factory()->create([
            'recipe_data' => [
                'ingredients' => [
                    ['id' => 35, 'type' => 'fixed', 'amount' => 86],
                    ['id' => 203, 'type' => 'fixed', 'amount' => 43]
                ]
            ],
            'ingestion_id' => $ingestion->id,
        ]);
        $calculations->recipe->plannedForUsers()->attach(
            $calculations->user,
            [
                'meal_date'    => '2022-09-05',
                'meal_time'    => 'dinner',
                'ingestion_id' => $ingestion->id,
            ]
        );
        $recipe_id = $calculations->recipe->id;
        Sanctum::actingAs($calculations->user, ['*']);

        $response = $this->json(
            'GET',
            '/api/v1/planned-meal/',
            [
                'id'            => $calculations->recipe->id,
                'date'          => '2022-09-05',
                'ingestion_key' => 'dinner',
            ]
        );

        $response->assertOk();
        $response->assertJsonFragment([
            'ingredient_id'     => 35,
            'ingredient_type'   => 'fixed',
            'main_category'     => 1,
            'ingredient_amount' => 86,
            'ingredient_text'   => 'g. Brokkoli',
            'allow_replacement' => false,
        ]);
        $response->assertJsonFragment([
            'ingredient_id'     => 203,
            'ingredient_type'   => 'fixed',
            'main_category'     => 1,
            'ingredient_amount' => 43,
            'ingredient_text'   => 'g. Majoran gerebelt',
            'allow_replacement' => false,
        ]);
    }

    /**
     * @test
     * @testdox Additional calculated recipe data is expected.
     */
    public function additionalData(): void
    {
        $ingestion    = $this->ingestions_repo->getByKey('dinner');
        $calculations = Models\UserRecipeCalculated::factory()->create([
            'recipe_data' => [
                'ingredients'     => [],
                'notices'         => [],
                'errors'          => false,
                'real_KH'         => 0.47,
                'real_F'          => 9.15,
                'real_EW'         => 1.33,
                'real_KCal'       => 89.8,
                'KCal'            => 89.8,
                'EW'              => 1.33,
                'F'               => 9.15,
                'KH'              => 0.47,
                'calculated_KCal' => 330,
                'calculated_KH'   => 6,
                'calculated_EW'   => 16.4,
                'calculated_F'    => 26.7
            ],
            'ingestion_id' => $ingestion->id,
        ]);
        $calculations->recipe->plannedForUsers()->attach(
            $calculations->user,
            [
                'meal_date'    => '2022-09-05',
                'meal_time'    => 'dinner',
                'ingestion_id' => $ingestion->id,
            ]
        );
        $recipe_id = $calculations->recipe->id;
        Sanctum::actingAs($calculations->user, ['*']);

        $response = $this->json(
            'GET',
            '/api/v1/planned-meal/',
            [
                'id'            => $calculations->recipe->id,
                'date'          => '2022-09-05',
                'ingestion_key' => 'dinner',
            ]
        );

        $response->assertOk();
        $response->assertJsonFragment([
            'calculated_KCal' => 330,
            'calculated_KH'   => 6,
            'calculated_EW'   => 16.4,
            'calculated_F'    => 26.7
        ]);
    }
}
