<?php

namespace Database\Factories;

use App\Models;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserRecipeCalculated>
 */
class UserRecipeCalculatedFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $ingredients = [];

        for ($i = 0; $i <= 5; $i++) {
            $ingredients[] = [
                'id'     => fake()->numberBetween(0, 450), // ID of a seeded ingredient
                'type'   => 'fixed',
                'amount' => fake()->numberBetween(0, 100),
            ];
        }

        return [
            'user_id'      => Models\User::factory(),
            'recipe_id'    => Models\Recipe::factory(),
            'invalid'      => false,
            'ingestion_id' => fake()->numberBetween(0, 4), // 1-3 are IDs of seeded ingestions
            'recipe_data'  => ['ingredients' => $ingredients],
        ];
    }
}
