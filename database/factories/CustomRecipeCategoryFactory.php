<?php

namespace Database\Factories;

use App\Models;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for a custom category for a recipe.
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomRecipeCategory>
 */
class CustomRecipeCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => Models\User::factory(),
            'name'    => fake()->word(),
        ];
    }
}
