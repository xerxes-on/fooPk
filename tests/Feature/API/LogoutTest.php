<?php

namespace Tests\Feature\API;

use App\Models;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @testdox Tests for POST /api/v1/logout/
 */
class LogoutTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /**
     * @test
     * @testdox Attempts limit shouldn't be applied to logout.
     */
    public function noThrottling(): void
    {
        $max = config('foodpunk.api_max_login_attempts');

        for ($attempts = 1; $attempts <= $max + 1; $attempts++) {
            $response = $this->post('/api/v1/logout/');
        }

        $this->assertNotEquals($response->status(), 429);
    }

    /**
     * @test
     * @testdox Only logged in users should be able to log out.
     */
    public function noAccess(): void
    {
        $response = $this->postJson('/api/v1/logout/');

        $response->assertUnauthorized();
    }

    /**
     * @test
     * @testdox Successful logout should result in a success message.
     */
    public function success(): void
    {
        $user  = Models\User::factory()->create();
        $token = $user->createToken('unknown device')->plainTextToken;

        $response = $this->postJson(
            '/api/v1/logout',
            headers: ['Authorization' => "Bearer $token"],
        );

        $response->assertStatus(200);
        $response->assertJson(['message' => 'You\'re logged out.']);
    }

    /**
     * @test
     * @testdox After logging out a user shouldn't have a token.
     */
    public function tokenRemoved(): void
    {
        $user  = Models\User::factory()->create();
        $token = $user->createToken('unknown device')->plainTextToken;

        $response = $this->postJson(
            '/api/v1/logout',
            headers: ['Authorization' => "Bearer $token"],
        );
        $hasTokens = $user->tokens()->exists();

        $this->assertFalse($hasTokens);
    }
}
