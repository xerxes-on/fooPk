<?php

namespace Tests\Feature\API;

use App\Models;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * @testdox Tests for POST /api/v1/unfavourite-recipe/{recipe_id}
 */
class UnfavouriteTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /**
     * @test
     * @testdox Only authenticated users should be able to remove favourite mark.
     */
    public function access(): void
    {
        $recipe = Models\Recipe::factory()->create();

        $response = $this->postJson("/api/v1/unfavourite-recipe/$recipe->id");

        $response->assertUnauthorized();
    }

    /**
     * @test
     * @testdox If a recipe doesn't exist, there should be an error.
     */
    public function noRecipe(): void
    {
        $user = Models\User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/v1/unfavourite-recipe/3000');

        $response->assertNotFound();
    }

    /**
     * @test
     * @testdox There should be no mark afterwards.
     */
    public function success(): void
    {
        $user   = Models\User::factory()->create();
        $recipe = Models\Recipe::factory()->create();
        $user->setFavourite($recipe);
        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson("/api/v1/unfavourite-recipe/$recipe->id");

        $response->assertOk();
        $response->assertJson(['message' => 'The recipe is removed from favourites.']);
        $this->assertFalse($recipe->favorited());
    }

    /**
     * @test
     * @testdox No need to unmark an unmarked recipe.
     */
    public function repeat(): void
    {
        $user   = Models\User::factory()->create();
        $recipe = Models\Recipe::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson("/api/v1/unfavourite-recipe/$recipe->id");

        $response->assertOk();
        $response->assertJson(['message' => 'The recipe is already removed from favourites.']);
    }
}
