<?php

Route::prefix('v1/flex-meal')
    ->middleware('auth:sanctum')
    ->controller(\Modules\FlexMeal\Http\Controllers\API\FlexMealApiController::class)
    ->group(
        function () {
            Route::post('save', 'store');
            Route::post('update/{list_id}', 'update');
            Route::post('update/image/{list_id}', 'updateImage');
            Route::delete('{list_id}', 'destroy');
            Route::delete('delete/image/{flexmealID}', 'destroyImage');
            Route::post('replace', 'replace');
            Route::post('check-flexmeal', 'check');
        }
    );

Route::prefix('v1/flex-meal')
    ->middleware('auth:sanctum')
    ->controller(\Modules\FlexMeal\Http\Controllers\API\FlexMealPreviewApiController::class)
    ->group(
        function () {
            Route::get('edit/{id}', 'get');
            Route::get('all', 'getArchive');
        }
    );
