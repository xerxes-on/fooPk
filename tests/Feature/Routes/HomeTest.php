<?php

namespace Tests\Feature\Route;

use App\Models;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for GET /
 */
class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function testRedirectsUnauthenticatedUserToLogin(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function testRedirectsAuthenticatedUserToDashboard(): void
    {
        $user = Models\User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect('/user/dashboard');
    }
}
