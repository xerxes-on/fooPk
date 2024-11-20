<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for recipe complexities.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecipeComplexity>
 */
final class RecipeComplexityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->word()
        ];
    }
}
