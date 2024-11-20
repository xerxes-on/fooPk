<?php

declare(strict_types=1);

use Modules\Course\Http\Controllers\API\CourseApiController;

Route::controller(CourseApiController::class)
    ->prefix('v1/challenges') // note: keep the prefix as it is ensures backward compatibility
    ->group(
        function () {
            Route::get('purchase', 'listPurchasable');
            Route::post('purchase', 'buy');
            Route::get('/', 'listPurchased');
            Route::get('{id}/articles', 'listArticles')->whereNumber('id');
            Route::get('articles/{id}/{days}', 'getArticle')->middleware('check.challenge');
            Route::post('reschedule', 'reschedule');
        }
    );
