<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Modules\PushNotification\Models\Notification;
use Modules\PushNotification\Models\NotificationType;
use Modules\PushNotification\Models\UserNotification;

/**
 * @extends Factory<NotificationType>
 */
final class UserNotificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = UserNotification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user         = User::inRandomOrder()->first();
        $notification = Notification::inRandomOrder()->first();
        return [
            'user_id'         => is_null($user) ? UserFactory::factory() : $user->id,
            'notification_id' => is_null($notification) ? Notification::factory() : $notification->id,
        ];
    }
}
