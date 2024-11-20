<?php

namespace Tests\Feature\API;

use App\Models;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * @testdox Tests for POST /api/v1/favourite-recipe/{recipe_id}
 */
class FavouritedTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /**
     * @test
     * @testdox Only authenticated users should be able to mark recipes as favourite.
     */
    public function access(): void
    {
        $recipe = Models\Recipe::factory()->create();

        $response = $this->postJson("/api/v1/favourite-recipe/$recipe->id");

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

        $response = $this->postJson('/api/v1/favourite-recipe/3000');

        $response->assertNotFound();
    }

    /**
     * @test
     * @testdox The mark should persist.
     */
    public function success(): void
    {
        $user   = Models\User::factory()->create();
        $recipe = Models\Recipe::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson("/api/v1/favourite-recipe/$recipe->id");

        $response->assertOk();
        $response->assertJson(['message' => 'The recipe added to favourites.']);
        $this->assertTrue($recipe->favorited());
    }

    /**
     * @test
     * @testdox No need to mark a marked recipe again.
     */
    public function repeat(): void
    {
        $user   = Models\User::factory()->create();
        $recipe = Models\Recipe::factory()->create();
        $user->setFavourite($recipe);
        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson("/api/v1/favourite-recipe/$recipe->id");

        $response->assertOk();
        $response->assertJson(['message' => 'The recipe is already favourited.']);
    }
}
