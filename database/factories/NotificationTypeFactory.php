<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\PushNotification\Models\NotificationType;
use Random\RandomException;

/**
 * @extends Factory<NotificationType>
 */
final class NotificationTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     * @throws RandomException
     */
    public function definition(): array
    {
        return [
            'slug' => fake()->slug(1),
            'name' => fake()->words(random_int(1, 3), true),
        ];
    }
}
