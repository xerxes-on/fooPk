<?php

namespace Database\Factories;

use App\Models;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for custom recipes.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomRecipe>
 */
class CustomRecipeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id'      => Models\User::factory(),
            'recipe_id'    => Models\Recipe::factory(),
            'ingestion_id' => fake()->numberBetween(0, 4), // 1-3 are IDs of seeded ingestions
            'title'        => fake()->word(),
            'error'        => false,
        ];
    }
}
