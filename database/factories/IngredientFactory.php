<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Ingredient\Models\Ingredient>
 */
class IngredientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'category_id'   => fake()->numberBetween(0, 17), // 1-16 - IDs of seeded categories
            'proteins'      => fake()->randomFloat(2, 0.1, 30),
            'fats'          => fake()->randomFloat(2, 0.1, 43),
            'carbohydrates' => fake()->randomFloat(2, 0, 30),
            'calories'      => fake()->randomFloat(1, 0, 700),
            'unit_id'       => fake()->numberBetween(0, 6), // 1-5 - IDs of seeded ingredient units
            'name'          => fake()->word(),
        ];
    }
}
