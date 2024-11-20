<?php

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @testdox Tests for POST /api/v1/replace-ingredient/.
 */
class ReplaceIngredientTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /**
     * @test
     * @testdox Only an authenticated user should be able to replace ingredients.
     */
    public function access(): void
    {
        $response = $this->json('POST', '/api/v1/replace-ingredient/', []);

        $response->assertUnauthorized();
    }
}
