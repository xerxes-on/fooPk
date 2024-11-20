<?php

Route::prefix('v1')
    ->middleware('auth:sanctum')
    ->controller(\Modules\Ingredient\Http\Controllers\API\IngredientsApiController::class)
    ->group(
        function () {
            Route::get('ingredients', 'getForCurrentUser');
            Route::get('all-ingredients', 'getAll');
            Route::get('main-categories', 'getMainCategories');
        }
    );
