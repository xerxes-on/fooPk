<?php

namespace Tests\Feature\API;

use App\Models;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @testdox Tests for POST /api/v1/login/
 */
class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /**
     * @test
     * @testdox Response should contain a valid token.
     */
    public function token(): void
    {
        $user = Models\User::factory()->create(
            ['email' => 'someone@foodpunk.com']
        );

        $response = $this->post(
            '/api/v1/login/',
            ['email' => 'someone@foodpunk.com', 'password' => 'password'],
        );
        $user  = $user->fresh();
        $token = $user->tokens->first();

        $this->assertTrue(!is_null($token));
        // Checking only for structure.
        // Exact token value can't be obtained because of security reasons.
        $response->assertJsonStructure(['data' => ['token']]);
    }

    /**
     * @test
     * @testdox Response should contain user data.
     */
    public function data(): void
    {
        $data = [
            'id'         => 123,
            'first_name' => 'Bob',
            'last_name'  => 'Marley',
            'email'      => 'bob@foodpunk.com',
            'lang'       => 'en',
            'status'     => true,
            'notes'      => 'Hello there.'
        ];
        $user = Models\User::factory()->create($data);

        $response = $this->post(
            '/api/v1/login/',
            ['email' => 'bob@foodpunk.com', 'password' => 'password'],
        );

        $response->assertJsonFragment(['user' => $data]);
    }

    /**
     * @test
     * @testdox If a request is invalid, there should be an error.
     */
    public function invalidRequest(): void
    {
        $response = $this->post(
            '/api/v1/login/',
            ['email' => 'not-email', 'password' => '123'],
        );

        $response->assertUnprocessable();
    }

    /**
     * @test
     * @testdox If credentials are invalid, or the user doesn't exist, there should be an error.
     */
    public function invalidCredentials(): void
    {
        $response = $this->post(
            '/api/v1/login/',
            ['email' => 'absent@mail.mail', 'password' => '12345678'],
        );

        $response->assertUnauthorized();
    }
}
