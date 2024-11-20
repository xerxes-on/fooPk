<?php

namespace Tests\Feature\API;

use App\Models;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Tests for GET /api/v1/ingredients/.
 */
class IngredientsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /**
     * @test
     * @testdox Only authenticated users should be able to get list of ingredients.
     */
    public function test_example(): void
    {
        $response = $this->getJson('/api/v1/ingredients/');

        $response->assertUnauthorized();
    }

    /**
     * @test
     * @testdox User should have a filled formular.
     */
    public function formularCheck(): void
    {
        $user = Models\User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/v1/ingredients/');

        $response->assertNotFound();
        $response->assertJson(
            ['message' => 'You have to answer survey questions to detect which ingredients are suitable for you.']
        );
    }

    /**
     * @test
     * @testdox List of ingredients is expected.
     */
    public function dataStructure(): void
    {
        $user = Models\User::factory()->hasFormulars(1)->create();
        Models\SurveyAnswer::factory()->create([
            'user_id'            => $user->id,
            'formular_id'        => $user->formular->id,
            'survey_question_id' => 16, // question about allergies
        ]);
        Models\SurveyAnswer::factory()->create([
            'user_id'            => $user->id,
            'formular_id'        => $user->formular->id,
            'survey_question_id' => 15, // question about diseases
        ]);
        $ingredient = \Modules\Ingredient\Models\Ingredient::factory()->create([
            'category_id'   => 2,
            'proteins'      => 2.5,
            'fats'          => 13.2,
            'carbohydrates' => 15,
            'calories'      => 26.1,
            'unit_id'       => 2,
            'name'          => 'Salt',
        ]);
        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/v1/ingredients/');

        $response->assertOk();
        $response->assertJsonFragment([
            [
                'id'            => $ingredient->id,
                'calories'      => 26.1,
                'carbohydrates' => 15,
                'category'      => ['id' => 2, 'name' => 'Protein'],
                'fats'          => 13.2,
                'proteins'      => 2.5,
                'unit'          => ['id' => 2, 'full_name' => 'Milliliters', 'short_name' => 'ml.'],
                'name'          => 'Salt',
            ]
        ]);
    }
}
