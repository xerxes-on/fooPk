<?php

use Modules\PushNotification\Http\Controllers\API\PushNotificationApiController;

Route::prefix('v1/user-notification')->controller(PushNotificationApiController::class)
    ->group(
        function () {
            Route::get('list', 'getList');
            Route::post('read', 'setReadStatus');
            Route::post('read-all', 'setReadStatusToAll');
        }
    );
