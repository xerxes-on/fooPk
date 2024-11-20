<?php

namespace Tests\Feature\Route;

use App\Models;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @testdox Tests for GET /user/dashboard
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /**
     * @test
     * @testdox An unauthenticated user should be redirected to login page.
     */
    public function guest(): void
    {
        $response = $this->get('/user/dashboard');

        $response->assertRedirect('/login');
    }

    /**
     * @test
     * @testdox A user without "user" role should be redirected to /admin.
     */
    public function noRole(): void
    {
        $user = Models\User::factory()->create();

        $response = $this->actingAs($user)->get('/user/dashboard/');

        $response->assertRedirect('/admin');
    }

    /**
     * @test
     * @testdox A user without a formular is expected to create one.
     */
    public function noFormular(): void
    {
        $user = Models\User::factory()->create()->assignRole('user');

        $response = $this->actingAs($user)->get('/user/dashboard/');

        $response->assertRedirect('/user/formular');
    }
}
