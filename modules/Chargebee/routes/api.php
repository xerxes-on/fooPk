<?php

use Modules\Chargebee\Http\Controllers\API\ChargebeeApiController;

Route::prefix('v1/chargebee')->group(
    function () {
        Route::post('webhook', [ChargebeeApiController::class, 'webhook']);
    });
