<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\PushNotification\Enums\DeviceTypesEnum;
use Modules\PushNotification\Models\NotificationType;
use Random\RandomException;

/**
 * @extends Factory<NotificationType>
 */
final class UserDeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     * @throws RandomException
     */
    public function definition(): array
    {
        $user = User::inRandomOrder()->first();
        return [
            'type_id'     => is_null($user) ? UserFactory::factory() : $user->id,
            'token'       => fake()->iosMobileToken(),
            'type'        => DeviceTypesEnum::values()[random_int(0, 1)],
            'os_version'  => '1.0.0',
            'app_version' => '1.0.0'
        ];
    }
}
