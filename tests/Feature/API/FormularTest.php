<?php

namespace Tests\Feature\API;

use App\Models\SurveyQuestion;
use App\Repositories\Formular as FormularRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormularTest extends TestCase
{
    use RefreshDatabase;

    protected bool             $seed = true;
    private FormularRepository $formular;

    public function setUp(): void
    {
        parent::setUp();
        $this->formular = $this->app->make('App\Repositories\Formular');
    }

    /**
     * @test
     * @testdox Questions should be available only to authenticated users.
     */
    public function questionsAccess(): void
    {
        $response = $this->json('GET', '/api/v1/formular/questions', []);

        $response->assertUnauthorized();
    }

    /**
     * @test
     * @testdox A formular should be available only to authenticated users.
     */
    public function formularAccess(): void
    {
        $response = $this->json('GET', '/api/v1/formular/get', []);

        $response->assertUnauthorized();
    }

    /**
     * @test
     * @testdox Only authenticated users should be able to buy formular edit.
     */
    public function buyEditFormularAccess(): void
    {
        $response = $this->json('GET', '/api/v1/formular/buy-edit', []);

        $response->assertUnauthorized();
    }

    /**
     * @test
     * @testdox A response should contain expected data.
     */
    public function questionsDataStructure()
    {
        $question = SurveyQuestion::factory()->create([
            'key_code'    => 'test',
            'label'       => '',
            'description' => '',
            'type'        => 'test',
            'options'     => null,
            'attributes'  => null,
            'required'    => 1,
            'order'       => 1,
            'active'      => 1,
        ]);

        $response = $this->json(
            'GET',
            '/api/v1/formular/questions',
        );

        $response->assertOk();
        $response->assertJsonFragment([
            'id'          => 1,
            'key_code'    => 'test',
            'label'       => '',
            'description' => '',
            'type'        => 'test',
            'options'     => null,
            'attributes'  => null,
            'required'    => 1,
            'order'       => 1,
            'active'      => 1
        ]);
    }
}
