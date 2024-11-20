<?php

namespace Tests\Feature\API;

use App\Models;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * @testdox Tests for GET /api/v1/all-recipes/
 */
class AllRecipesTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /**
     * @test
     * @testdox Recipes list should be available only to authenticated users.
     */
    public function access(): void
    {
        $response = $this->json(
            'GET',
            '/api/v1/all-recipes/',
            ['per_page' => 20]
        );

        $response->assertUnauthorized();
    }

    /**
     * @test
     * @testdox The route should return correct data.
     */
    public function dataStructure(): void
    {
        $user   = Models\User::factory()->create();
        $recipe = Models\Recipe::factory()->create([
            'title'         => 'Tasty Stuff',
            'cooking_time'  => 3,
            'unit_of_time'  => 'minutes',
            'complexity_id' => 1,
            'price_id'      => 2,
        ]);
        $user->allRecipes()->attach($recipe);
        $diet1_id = $recipe->diets()->create(['name' => 'Meat Diet'])->id;
        $diet2_id = $recipe->diets()->create(['name' => 'Vegan Diet'])->id;
        $recipe->ingestions()->attach([1, 2]);
        Models\UserRecipeCalculated::factory()->create(['user_id' => $user->id, 'recipe_id' => $recipe->id]);
        Sanctum::actingAs($user, ['*']);

        $response = $this->json(
            'GET',
            '/api/v1/all-recipes/',
            ['per_page' => 20]
        );

        $response->assertJsonFragment(
            [
                'id'           => $recipe->id,
                'is_new'       => true,
                'calc_invalid' => false,
                'image'        => 'https://via.placeholder.com/150/00a65a/ffffff/?text=R',
                'title'        => 'Tasty Stuff',
                'ingestions'   => [
                    ['id' => 1, 'key' => 'breakfast', 'title' => 'breakfast'],
                    ['id' => 2, 'key' => 'lunch', 'title' => 'lunch'],
                ],
                'cooking_time' => 3,
                'unit_of_time' => 'minutes',
                'complexity'   => ['id' => 1, 'title' => 'Easy'],
                'price'        => ['id' => 2, 'title' => '$$', 'min_price' => 11, 'max_price' => 20],
                'diets'        => [
                    ['id' => $diet1_id, 'name' => 'Meat Diet'],
                    ['id' => $diet2_id, 'name' => 'Vegan Diet'],
                ],
            ]
        );
    }

    /**
     * @test
     * @testdox Results should be paginated.
     */
    public function pagination(): void
    {
        $user   = Models\User::factory()->create();
        $recipe = Models\Recipe::factory()->create();
        $user->allRecipes()->attach($recipe);
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $recipe->id]
        );
        Sanctum::actingAs($user, ['*']);

        $response = $this->json(
            'GET',
            '/api/v1/all-recipes/',
            ['page' => 2, 'per_page' => 20],
        );

        $response->assertJsonFragment(
            [
                'data' => [],
            ]
        );
    }

    /**
     * @test
     * @testdox Recipes without calculations for current user shouldn't be listed.
     */
    public function noCalculations(): void
    {
        $user       = Models\User::factory()->create();
        $other_user = Models\User::factory()->create();
        $recipe     = Models\Recipe::factory()->create();
        $user->allRecipes()->attach($recipe);
        $calculations = Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $other_user->id, 'recipe_id' => $recipe->id]
        );
        Sanctum::actingAs($user, ['*']);

        $response = $this->json(
            'GET',
            '/api/v1/all-recipes/',
            ['per_page' => 20],
        );

        $response->assertJsonFragment(
            [
                'data' => [],
            ]
        );
    }

    /**
     * @test
     * @testdox Recipes should be filterable by title.
     */
    public function filterByTitle(): void
    {
        $parameters = ['per_page' => 20, 'search_name' => 'tasty'];
        $user       = Models\User::factory()->create();
        $included   = Models\Recipe::factory()->create(['title' => 'That Stuff is Tasty']);
        $user->allRecipes()->attach($included);
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $included->id]
        );
        $excluded = Models\Recipe::factory()->create(['title' => 'That stuff is disgusting']);
        $user->allRecipes()->attach($excluded);
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $excluded->id]
        );
        Sanctum::actingAs($user, ['*']);

        $response = $this->json(
            'GET',
            '/api/v1/all-recipes/',
            $parameters,
        );

        $response->assertJsonFragment(['id' => $included->id]);
        $response->assertJsonMissing(['id' => $excluded->id]);
    }

    /**
     * @test
     * @testdox Recipes should be filterable by ingredient name.
     */
    public function filterByIngredientName(): void
    {
        $parameters = ['per_page' => 20, 'search_name' => 'tasty'];
        $user       = Models\User::factory()->create();
        $included   = Models\Recipe::factory()->create();
        $included->ingredients()->create(['name' => 'Tasty Ingredient']);
        $user->allRecipes()->attach($included);
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $included->id]
        );
        $excluded = Models\Recipe::factory()->create();
        $excluded->ingredients()->create(['name' => 'Disgusting Ingredient']);
        $user->allRecipes()->attach($excluded);
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $excluded->id]
        );
        Sanctum::actingAs($user, ['*']);

        $response = $this->json(
            'GET',
            '/api/v1/all-recipes/',
            $parameters,
        );

        $response->assertJsonFragment(['id' => $included->id]);
        $response->assertJsonMissing(['id' => $excluded->id]);
    }

    /**
     * @test
     * @testdox Recipes should be filterable by variable ingredient name.
     */
    public function filterByVariableIngredientName(): void
    {
        $parameters          = ['per_page' => 20, 'search_name' => 'avocado'];
        $user                = Models\User::factory()->create();
        $included            = Models\Recipe::factory()->create();
        $included_ingredient = \Modules\Ingredient\Models\Ingredient::where('id', 11)->first();
        $included->variableIngredients()->attach($included_ingredient, ['ingredient_category_id' => 1]);
        $user->allRecipes()->attach($included);
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $included->id]
        );
        $excluded_ingredient = \Modules\Ingredient\Models\Ingredient::where('id', 30)->first();
        $excluded            = Models\Recipe::factory()->create();
        $excluded->variableIngredients()->attach($excluded_ingredient, ['ingredient_category_id' => 1]);
        $user->allRecipes()->attach($excluded);
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $excluded->id]
        );
        Sanctum::actingAs($user, ['*']);

        $response = $this->json(
            'GET',
            '/api/v1/all-recipes/',
            $parameters,
        );

        $response->assertJsonFragment(['id' => $included->id]);
        $response->assertJsonMissing(['id' => $excluded->id]);
    }

    /**
     * @test
     * @testdox Recipes should be filterable by meal type (ingestion).
     */
    public function filterByMealType(): void
    {
        $parameters = ['per_page' => 20, 'ingestion' => 1];
        $user       = Models\User::factory()->create();
        $included   = Models\Recipe::factory()->create();
        $included->ingestions()->attach(1); // breakfast
        $user->allRecipes()->attach($included);
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $included->id]
        );
        $excluded = Models\Recipe::factory()->create();
        $excluded->ingestions()->attach(3); // dinner
        $user->allRecipes()->attach($excluded);
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $excluded->id]
        );
        Sanctum::actingAs($user, ['*']);

        $response = $this->json(
            'GET',
            '/api/v1/all-recipes/',
            $parameters,
        );

        $response->assertJsonFragment(['id' => $included->id]);
        $response->assertJsonMissing(['id' => $excluded->id]);
    }

    /**
     * @test
     * @testdox Recipes should be filterable by cost.
     */
    public function filterByCost(): void
    {
        $parameters = ['per_page' => 20, 'cost' => 2];
        $user       = Models\User::factory()->create();
        $included   = Models\Recipe::factory()->create(['price_id' => 2]); // $$
        $user->allRecipes()->attach($included);
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $included->id]
        );
        $excluded = Models\Recipe::factory()->create(['price_id' => 3]); // $$$
        $user->allRecipes()->attach($excluded);
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $excluded->id]
        );
        Sanctum::actingAs($user, ['*']);

        $response = $this->json(
            'GET',
            '/api/v1/all-recipes/',
            $parameters,
        );

        $response->assertJsonFragment(['id' => $included->id]);
        $response->assertJsonMissing(['id' => $excluded->id]);
    }

    /**
     * @test
     * @testdox Recipes should be filterable by complexity.
     */
    public function filterByComplexity(): void
    {
        $parameters = ['per_page' => 20, 'complexity' => 1];
        $user       = Models\User::factory()->create();
        $included   = Models\Recipe::factory()->create(['complexity_id' => 1]); // Easy
        $user->allRecipes()->attach($included);
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $included->id]
        );
        $excluded = Models\Recipe::factory()->create(['complexity_id' => 3]); // Complicated
        $user->allRecipes()->attach($excluded);
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $excluded->id]
        );
        Sanctum::actingAs($user, ['*']);

        $response = $this->json(
            'GET',
            '/api/v1/all-recipes/',
            $parameters,
        );

        $response->assertJsonFragment(['id' => $included->id]);
        $response->assertJsonMissing(['id' => $excluded->id]);
    }

    /**
     * @test
     * @testdox Recipes should be filterable by diet.
     */
    public function filterByDiet(): void
    {
        $parameters                = ['per_page' => 20, 'diet' => 1];
        $user                      = Models\User::factory()->create();
        list($included, $excluded) = Models\Recipe::factory()->count(2)->create();
        $included->diets()->attach(1); // Vegetarian
        $user->allRecipes()->attach($included);
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $included->id]
        );
        $excluded->diets()->attach(3); // Paleo
        $user->allRecipes()->attach($excluded);
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $excluded->id]
        );
        Sanctum::actingAs($user, ['*']);

        $response = $this->json(
            'GET',
            '/api/v1/all-recipes/',
            $parameters,
        );

        $response->assertJsonFragment(['id' => $included->id]);
        $response->assertJsonMissing(['id' => $excluded->id]);
    }

    /**
     * @test
     * @testdox Recipes should be filterable by Month/Season
     */
    public function filterBySeason(): void
    {
        $season                    = Models\Seasons::create(['key' => 'december', 'name' => 'December']);
        $parameters                = ['per_page' => 20, 'seasons' => $season->id];
        $user                      = Models\User::factory()->create();
        list($included, $excluded) = Models\Recipe::factory()->count(2)->create();
        $user->allRecipes()->attach($included);
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $included->id]
        );
        $ingredient = \Modules\Ingredient\Models\Ingredient::where('id', 1)->first(); // Algen
        $ingredient->seasons()->attach($season);
        $excluded->ingredients()->attach($ingredient);
        $user->allRecipes()->attach($excluded);
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $excluded->id]
        );
        Sanctum::actingAs($user, ['*']);

        $response = $this->json(
            'GET',
            '/api/v1/all-recipes/',
            $parameters,
        );

        $response->assertJsonFragment(['id' => $included->id]);
        $response->assertJsonMissing(['id' => $excluded->id]);
    }

    /**
     * @test
     * @testdox Recipes should be filtered by calculations validity.
     */
    public function filterByValidity(): void
    {
        $parameters                = ['per_page' => 20, 'invalid' => 0];
        $user                      = Models\User::factory()->create();
        list($included, $excluded) = Models\Recipe::factory()->count(2)->create();
        $user->allRecipes()->attach($included);
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $included->id, 'invalid' => false]
        );
        $user->allRecipes()->attach($excluded);
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $excluded->id, 'invalid' => true]
        );
        Sanctum::actingAs($user, ['*']);

        $response = $this->json(
            'GET',
            '/api/v1/all-recipes/',
            $parameters,
        );

        $response->assertJsonFragment(['id' => $included->id]);
        $response->assertJsonMissing(['id' => $excluded->id]);
    }

    /**
     * @test
     * @testdox There shouldn't be duplicated recipes.
     */
    public function duplicates(): void
    {
        $user   = Models\User::factory()->create();
        $recipe = Models\Recipe::factory()->create();
        $user->allRecipes()->attach($recipe);
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $recipe->id]
        );
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $recipe->id]
        );
        Sanctum::actingAs($user, ['*']);

        $response = $this->json(
            'GET',
            '/api/v1/all-recipes/',
            ['per_page' => 20],
        );

        $response->assertJsonCount(1, 'data');
    }

    /**
     * @test
     * @testdox There should be an option to filter out favourited recipes.
     */
    public function filterByFavorite(): void
    {
        $parameters                = ['per_page' => 20, 'favorite' => true];
        $user                      = Models\User::factory()->create();
        list($included, $excluded) = Models\Recipe::factory()->count(2)->create();
        $user->allRecipes()->attach($included);
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $included->id]
        );
        $user->favorites()->attach($included);
        $user->allRecipes()->attach($excluded);
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $excluded->id]
        );
        Sanctum::actingAs($user, ['*']);

        $response = $this->json(
            'GET',
            '/api/v1/all-recipes/',
            $parameters,
        );

        $response->assertJsonFragment(['id' => $included->id]);
        $response->assertJsonMissing(['id' => $excluded->id]);
    }

    /**
     * @test
     * @testdox When recipes are obtained for replacement, they should be filterable for it (breakfast).
     */
    public function filterForBreakfastReplacement(): void
    {
        $user                      = Models\User::factory()->create();
        list($included, $excluded) = Models\Recipe::factory()->count(2)->create();
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $included->id, 'ingestion_id' => 1]
        );
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $excluded->id, 'ingestion_id' => 2]
        );
        $user->allRecipes()->attach([$included->id, $excluded->id]);
        $included->ingestions()->attach(1);
        $excluded->ingestions()->attach(2);
        Sanctum::actingAs($user, ['*']);

        $response = $this->json(
            'GET',
            '/api/v1/all-recipes/',
            ['per_page' => 20, 'replacement_ingestion' => 1],
        );

        $response->assertJsonFragment(['id' => $included->id]);
        $response->assertJsonMissing(['id' => $excluded->id]);
    }

    /**
     * @test
     * @testdox When recipes are obtained for replacement, they should be filterable for it (lunch/dinner).
     */
    public function filterForLunchOrDinnerReplacement(): void
    {
        $user                      = Models\User::factory()->create();
        list($included, $excluded) = Models\Recipe::factory()->count(2)->create();
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $included->id, 'ingestion_id' => 2]
        );
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $included->id, 'ingestion_id' => 3]
        );
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $excluded->id, 'ingestion_id' => 1]
        );
        $user->allRecipes()->attach([$included->id, $excluded->id]);
        $included->ingestions()->attach(2);
        $excluded->ingestions()->attach(1);
        Sanctum::actingAs($user, ['*']);

        $response = $this->json(
            'GET',
            '/api/v1/all-recipes/',
            ['per_page' => 20, 'replacement_ingestion' => 3],
        );

        $response->assertJsonFragment(['id' => $included->id]);
        $response->assertJsonMissing(['id' => $excluded->id]);
    }

    /**
     * @test
     * @testdox Recipes for replacement should have valid calculations.
     */
    public function replacementRecipesCalculations(): void
    {
        $user                      = Models\User::factory()->create();
        list($included, $excluded) = Models\Recipe::factory()->count(2)->create();
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $included->id, 'ingestion_id' => 1]
        );
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $excluded->id, 'ingestion_id' => 1, 'invalid' => true]
        );
        $user->allRecipes()->attach([$included->id, $excluded->id]);
        $included->ingestions()->attach(1);
        $excluded->ingestions()->attach(1);
        Sanctum::actingAs($user, ['*']);

        $response = $this->json(
            'GET',
            '/api/v1/all-recipes/',
            ['per_page' => 20, 'replacement_ingestion' => 1],
        );

        $response->assertJsonFragment(['id' => $included->id]);
        $response->assertJsonMissing(['id' => $excluded->id]);
    }

    /**
     * @test
     * @testdox A recipe is new if it was recently added, not if calculations for it were recently added.
     */
    public function is_new(): void
    {
        $user            = Models\User::factory()->create();
        list($new, $old) = Models\Recipe::factory()->count(2)->create();
        $today           = new Carbon();
        $someTimeAgo     = (new Carbon())->subDay(config('foodpunk.days_recipe_is_new') + 1);
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $new->id, 'created_at' => $someTimeAgo, 'invalid' => false]
        );
        Models\UserRecipeCalculated::factory()->create(
            ['user_id' => $user->id, 'recipe_id' => $old->id, 'created_at' => $today, 'invalid' => false]
        );
        $user->allRecipes()->attach($new, ['created_at' => $today]);
        $user->allRecipes()->attach($old, ['created_at' => $someTimeAgo]);
        Sanctum::actingAs($user, ['*']);

        $response = $this->json('GET', '/api/v1/all-recipes/', ['per_page' => 2]);
        $data     = $response->json()['data'];

        $this->assertTrue($data[0]['id'] == $new->id && $data[0]['is_new'] == true);
        $this->assertTrue($data[1]['id'] == $old->id && $data[1]['is_new'] == false);
    }
}
