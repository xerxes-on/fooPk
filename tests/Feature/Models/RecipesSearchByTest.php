<?php

namespace Tests\Feature\Models;

use App\Models;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @testdox Tests for Recipe::scopeSearchBy()
 */
class RecipesSearchByTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /**
     * @test
     * @testdox If all ingredients of a recipe are for "Any" season, the recipe is always listed.
     */
    public function anySeasonRecipesIncluded(): void
    {
        $ingredient = \Modules\Ingredient\Models\Ingredient::factory()->create();
        $ingredient->seasons()->attach(Models\Seasons::ANY_SEASON_ID);
        $recipe = Models\Recipe::factory()->create();
        $recipe->ingredients()->attach($ingredient->id);
        // it doesn't matter which season to search by,
        // the recipe should be listed anyway.
        $irrelevantSeason = Models\Seasons::factory()->create();

        $recipes = Models\Recipe::searchBy(['seasons' => $irrelevantSeason->id])->get();

        $this->assertTrue($recipes->contains($recipe));
    }

    /**
     * @test
     * @testdox If all ingredients of a recipe are for given season, the recipe is listed.
     */
    public function particularSeasonRecipesIncluded(): void
    {
        $season     = Models\Seasons::factory()->create();
        $ingredient = \Modules\Ingredient\Models\Ingredient::factory()->create();
        $ingredient->seasons()->attach($season->id);
        $recipe = Models\Recipe::factory()->create();
        $recipe->ingredients()->attach($ingredient->id);

        $recipes = Models\Recipe::searchBy(['seasons' => $season->id])->get();

        $this->assertTrue($recipes->contains($recipe));
    }

    /**
     * @test
     * @testdox If all ingredients are for "Any" or given season, the recipe is listed.
     */
    public function anyOrParticularSeasonIncluded(): void
    {
        $season                                              = Models\Seasons::factory()->create();
        list($ingredientWithSeason, $ingredientForAnySeason) = \Modules\Ingredient\Models\Ingredient::factory()->count(2)->create();
        $ingredientWithSeason->seasons()->attach($season->id);
        $ingredientForAnySeason->seasons()->attach(Models\Seasons::ANY_SEASON_ID);
        $recipe = Models\Recipe::factory()->create();
        $recipe->ingredients()->attach([$ingredientWithSeason->id, $ingredientForAnySeason->id]);

        $recipes = Models\Recipe::searchBy(['seasons' => $season->id])->get();

        $this->assertTrue($recipes->contains($recipe));
    }

    /**
     * @test
     * @testdox If at least one ingredient isn't for "Any" or given season, the recipe is excluded.
     */
    public function anyOrParticularSeasonExcluded(): void
    {
        list($givenSeason, $anotherSeason) = Models\Seasons::factory()->count(2)->create();
        $ingredientForGivenSeason          = \Modules\Ingredient\Models\Ingredient::factory()->create();
        $ingredientForGivenSeason->seasons()->attach($givenSeason->id);
        $ingredientForAnotherSeason = \Modules\Ingredient\Models\Ingredient::factory()->create();
        $ingredientForAnotherSeason->seasons()->attach($anotherSeason);
        $ingredientForAnySeason = \Modules\Ingredient\Models\Ingredient::factory()->create();
        $ingredientForAnySeason->seasons()->attach(Models\Seasons::ANY_SEASON_ID);
        $recipe = Models\Recipe::factory()->create();
        $recipe->ingredients()->attach([
            $ingredientForGivenSeason->id,
            $ingredientForAnotherSeason->id,
            $ingredientForAnySeason->id,
        ]);

        $recipes = Models\Recipe::searchBy(['seasons' => $givenSeason->id])->get();

        $this->assertFalse($recipes->contains($recipe));
    }

    /**
     * @test
     * @testdox If an ingredient is for given and some other season, the recipe is listed.
     */
    public function multipleSeasonsIncluded(): void
    {
        list($givenSeason, $anotherSeason) = Models\Seasons::factory()->count(2)->create();
        $ingredient                        = \Modules\Ingredient\Models\Ingredient::factory()->create();
        $ingredient->seasons()->attach([$anotherSeason->id, $givenSeason->id]);
        $recipe = Models\Recipe::factory()->create();
        $recipe->ingredients()->attach($ingredient);

        $recipes = Models\Recipe::searchBy(['seasons' => $givenSeason->id])->get();

        $this->assertTrue($recipes->contains($recipe));
    }
}
