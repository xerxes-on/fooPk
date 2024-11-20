<?php

namespace Tests\Feature;

use App\Models;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * @testdox Tests for GET /api/v1/plan/
 */
class WeeklyPlanTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /**
     * @test
     * @testdox Only authenticated users should have access to their plans.
     */
    public function access(): void
    {
        $response = $this->json(
            'GET',
            '/api/v1/plan/',
            ['year' => 2022, 'week' => 38],
        );

        $response->assertUnauthorized();
    }

    /**
     * @test
     * @testdox The response should contain expected data.
     */
    public function dataStructure(): void
    {
        $user   = Models\User::factory()->create();
        $recipe = Models\Recipe::factory()->create([
            'title'         => 'Tasty Stuff',
            'complexity_id' => 1,
            'cooking_time'  => 5,
            'unit_of_time'  => 'minutes',
        ]);
        $meal = Models\UserRecipe::create([
            'recipe_id'    => $recipe->id,
            'user_id'      => $user->id,
            'ingestion_id' => 1,
            'meal_date'    => '2022-09-05',
            'meal_time'    => 'breakfast',
//			'challenge_id' => $user->challenge->id,
        ]);
        Models\Favorite::create(['user_id' => $user->id, 'recipe_id' => $recipe->id]);
        Sanctum::actingAs($user, ['*']);

        $response = $this->json(
            'GET',
            '/api/v1/plan/',
            ['year' => 2022, 'week' => 36],
        );

        $response->assertOk();
        $response->assertJsonFragment([
            [
                'ingestion' => ['id' => 1, 'key' => 'breakfast', 'title' => 'breakfast'],
                'cooked'    => false,
                'eat_out'   => false,
                'meal_date' => '2022-09-05',
                'meal_time' => 'breakfast',
                'recipe'    => [
                    'id'           => $recipe->id,
                    'custom'       => false,
                    'title'        => 'Tasty Stuff',
                    'complexity'   => ['id' => 1, 'title' => 'Easy'],
                    'favourited'   => true,
                    'cooking_time' => 5,
                    'unit_of_time' => 'minutes',
                    'image'        => 'https://via.placeholder.com/150/00a65a/ffffff/?text=R',
                ],
            ],
        ]);
    }

    /**
     * @test
     * @testdox There should be next/previous weeks suggestions.
     */
    public function weeksSuggestions(): void
    {
        Sanctum::actingAs(
            Models\User::factory()->create(),
            ['*'],
        );

        $response = $this->json(
            'GET',
            '/api/v1/plan/',
            ['year' => 2022, 'week' => 52],
        );

        $response->assertOk();
        $response->assertJsonFragment([
            'previous' => ['year' => 2022, 'week' => 51],
            'next'     => ['year' => 2023, 'week' => 1],
        ]);
    }

    /**
     * @test
     * @testdox Meals for other weeks should be excluded.
     */
    public function excludedDate(): void
    {
        $user   = Models\User::factory()->create();
        $recipe = Models\Recipe::factory()->create([
            'title'         => 'Tasty Stuff',
            'complexity_id' => 1,
            'cooking_time'  => 5,
            'unit_of_time'  => 'minutes',
        ]);
        $meal = Models\UserRecipe::create([
            'recipe_id'    => $recipe->id,
            'user_id'      => $user->id,
            'ingestion_id' => 1,
            'meal_date'    => '2022-09-05',
            'meal_time'    => 'breakfast',
//			'challenge_id' => $user->challenge->id,
        ]);
        Models\Favorite::create(['user_id' => $user->id, 'recipe_id' => $recipe->id]);
        Sanctum::actingAs($user, ['*']);

        $response = $this->json(
            'GET',
            '/api/v1/plan/',
            ['year' => 2022, 'week' => 35],
        );

        $response->assertOk();
        $response->assertJsonMissing([
            [
                'ingestion' => ['id' => 1, 'key' => 'breakfast', 'title' => 'breakfast'],
                'cooked'    => false,
                'eat_out'   => false,
                'meal_date' => '2022-09-05',
                'meal_time' => 'breakfast',
                'recipe'    => [
                    'id'           => $recipe->id,
                    'custom'       => false,
                    'title'        => 'Tasty Stuff',
                    'complexity'   => ['id' => 1, 'title' => 'Easy'],
                    'favourited'   => true,
                    'cooking_time' => 5,
                    'unit_of_time' => 'minutes',
                    'image'        => 'https://via.placeholder.com/150/00a65a/ffffff/?text=R',
                ],
            ],
        ]);
    }

    /**
     * @test
     * @testdox Meals with customized recipes should be included.
     */
    public function customizedRecipes(): void
    {
        $user   = Models\User::factory()->create();
        $recipe = Models\Recipe::factory()->create([
            'title'         => 'Tasty Stuff',
            'complexity_id' => 1,
            'cooking_time'  => 5,
            'unit_of_time'  => 'minutes',
        ]);
        $customization = Models\CustomRecipe::create([
            'user_id'   => $user->id,
            'recipe_id' => $recipe->id,
//			'challenge_id' => $user->challenge->id,
            'ingestion_id' => 2,
            'title'        => 'Tasty Stuff, but Mine',
            'error'        => false,
        ]);
        $meal = Models\UserRecipe::create([
            'recipe_id'        => null,
            'custom_recipe_id' => $customization->id,
            'user_id'          => $user->id,
            'ingestion_id'     => 1,
            'meal_date'        => '2022-09-05',
            'meal_time'        => 'breakfast',
//			'challenge_id'     => $user->challenge->id,
        ]);
        Models\Favorite::create(['user_id' => $user->id, 'recipe_id' => $recipe->id]);
        Sanctum::actingAs($user, ['*']);

        $response = $this->json(
            'GET',
            '/api/v1/plan/',
            ['year' => 2022, 'week' => 36],
        );

        $response->assertOk();
        $response->assertJsonFragment([
            [
                'ingestion' => ['id' => 1, 'key' => 'breakfast', 'title' => 'breakfast'],
                'cooked'    => false,
                'eat_out'   => false,
                'meal_date' => '2022-09-05',
                'meal_time' => 'breakfast',
                'recipe'    => [
                    'id'           => $customization->id,
                    'custom'       => true,
                    'title'        => 'Tasty Stuff',
                    'complexity'   => ['id' => 1, 'title' => 'Easy'],
                    'favourited'   => true,
                    'cooking_time' => 5,
                    'unit_of_time' => 'minutes',
                    'image'        => 'https://via.placeholder.com/150/00a65a/ffffff/?text=R',
                ],
            ],
        ]);
    }

}
