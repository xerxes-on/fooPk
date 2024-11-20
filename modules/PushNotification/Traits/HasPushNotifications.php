<?php

namespace Modules\PushNotification\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Modules\PushNotification\Models\Notification;
use Modules\PushNotification\Models\UserDevice;
use Modules\PushNotification\Models\UserNotification;

trait HasPushNotifications
{
    /**
     * relation for User devices
     */
    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    /**
     * relation for User notifications
     */
    public function pushNotifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    /**
     * Relation for Notifications obtained trough User notifications.
     */
    public function notificationsContent(): HasManyThrough
    {
        return $this->hasManyThrough(
            Notification::class,
            UserNotification::class,
            'user_id',
            'id',
            'id',
            'notification_id'
        );
    }
}
