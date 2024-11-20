<?php

namespace Tests\Feature\API;

use App\Models;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * @testdox Tests for POST /api/v1/restore-recipe/{custom_recipe_id}
 */
class RestoreRecipeTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /**
     * @test
     * @testdox Only authenticated users should be able to restore recipes.
     */
    public function access(): void
    {
        $response = $this->postJson('/api/v1/restore-recipe/123');

        $response->assertUnauthorized();
    }

    /**
     * @test
     * @testdox A user should be able to restore only own recipes.
     */
    public function accessToRecipe(): void
    {
        $user = Models\User::factory()->create();
        $id   = Models\CustomRecipe::factory()->create()->id;
        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson("/api/v1/restore-recipe/$id");

        $response->assertNotFound();
    }
}
