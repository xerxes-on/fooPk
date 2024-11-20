<?php
declare(strict_types=1);

Route::middleware(['lang.manager', 'auth', 'checkRole.user'])
    ->prefix('user/chargebee')
    ->group(
        function () {
            Route::post(
                'update-subscription-data',
                [Modules\Chargebee\Http\Controllers\ChargebeeController::class, 'updateSubscriptionData'])->name('user.chargebee.update_subscription_data');
        });
