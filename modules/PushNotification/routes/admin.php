<?php

use Modules\PushNotification\Http\Controllers\Admin\NotificationAdminController;
use Modules\PushNotification\Http\Controllers\Admin\NotificationTypeAdminController;

Route::post('notification_types/store', [NotificationTypeAdminController::class, 'store'])
    ->name('admin.notifications.type.store');
Route::prefix('notifications')->controller(NotificationAdminController::class)->group(
    function () {
        Route::get('get/options/form/{notificationId}', 'getConfigForm')
            ->whereNumber('notificationId')
            ->name('admin.notifications.config');
        Route::post('store', 'store')->name('admin.notifications.store');
        Route::post('dispatch', 'sendNotification')->name('admin.notifications.dispatch');
    }
);
