<?php

namespace Tests\Feature\API;

use App\Models;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * @testdox Tests for GET /api/v1/recipe/{id}
 */
class SingleRecipeTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /**
     * @test
     * @testdox A recipe should be available only to authenticated users.
     */
    public function access(): void
    {
        $response = $this->getJson('/api/v1/recipe/1');

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

        $response = $this->getJson('/api/v1/recipe/123');

        $response->assertNotFound();
    }

    /**
     * @test
     * @testdox A response should contain expected recipe data.
     */
    public function recipeDataStructure(): void
    {
        $complexity = Models\RecipeComplexity::create(['title' => 'Testing']);
        $price      = Models\RecipePrice::create(['title' => '$$', 'min_price' => 1.5, 'max_price' => 3.3]);
        $recipe     = Models\Recipe::factory()
                                   ->hasAllUsers(1)
                                   ->create([
                                       'title'         => 'My Recipe',
                                       'cooking_time'  => 2,
                                       'unit_of_time'  => 'minutes',
                                       'complexity_id' => $complexity->id,
                                       'price_id'      => $price->id,
                                   ]);
        $user = $recipe->allUsers()->first();
        Models\UserRecipeCalculated::create([
            'user_id'      => $user->id,
            'recipe_id'    => $recipe->id,
            'invalid'      => false,
            'ingestion_id' => 1,
            'recipe_data'  => ['a' => 1, 'b' => 2],
        ]);
        Models\Favorite::create(['user_id' => $user->id, 'recipe_id' => $recipe->id]);
        $diet1_id      = $recipe->diets()->create(['name' => 'Meat Diet'])->id;
        $diet2_id      = $recipe->diets()->create(['name' => 'Vegan Diet'])->id;
        $ingestion1_id = $recipe->ingestions()->create(['title' => 'Breakfast'])->id;
        $ingestion2_id = $recipe->ingestions()->create(['title' => 'Dinner'])->id;
        $inventory1_id = $recipe->inventories()->create(['title' => 'Inventory 1', 'tags' => 'A, B, C'])->id;
        $inventory2_id = $recipe->inventories()->create(['title' => 'Inventory 2', 'tags' => 'D, E, F'])->id;
        $step1_id      = $recipe->steps()->create(['description' => 'Take an egg.'])->id;
        $step2_id      = $recipe->steps()->create(['description' => 'Crush it!'])->id;
        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson("/api/v1/recipe/$recipe->id");

        $response->assertOk();
        $response->assertJsonFragment(
            [
                'id'           => $recipe->id,
                'title'        => 'My Recipe',
                'calc_invalid' => false,
                'image'        => 'https://via.placeholder.com/150/00a65a/ffffff/?text=R',
                'favourited'   => true,
                'purchased'    => false,
                'ingestions'   => [
                    ['id' => $ingestion1_id, 'title' => 'Breakfast', 'key' => ''],
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
        );
    }

    /**
     * @test
     * @testdox A response should contain calculated ingredients.
     */
    public function ingredientsDataStructure(): void
    {
        $calculations = Models\UserRecipeCalculated::factory()->create([
            'recipe_data' => [
                'ingredients' => [
                    ['id' => 35, 'type' => 'fixed', 'amount' => 86],
                    ['id' => 203, 'type' => 'fixed', 'amount' => 43]
                ]
            ],
        ]);
        $calculations->recipe->allUsers()->attach($calculations->user);
        $recipe_id = $calculations->recipe->id;
        Sanctum::actingAs($calculations->user, ['*']);

        $response = $this->getJson("/api/v1/recipe/$recipe_id");

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
}
