<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\PushNotification\Models\NotificationType;
use Random\RandomException;

/**
 * @extends Factory<NotificationType>
 */
final class NotificationFactory extends Factory
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
            'type_id' => NotificationType::factory(),
            'title'   => fake()->words(random_int(3, 6), true),
            'content' => fake()->sentences(asText: true),
        ];
    }
}
