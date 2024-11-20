<?php

declare(strict_types=1);

Route::prefix('v1/purchases')
    ->middleware('auth:sanctum')
    ->group(
        function () {
            Route::controller(\Modules\ShoppingList\Http\Controllers\API\ShoppingListApiController::class)
                ->prefix('list')
                ->group(function () {
                    Route::get('', 'getList');
                    Route::post('', 'generateListForPeriod');
                    Route::get('clear', 'clearList');
                    Route::get('pdf', 'getListInPDF');
                });

            Route::controller(\Modules\ShoppingList\Http\Controllers\API\ShoppingListRecipeApiController::class)
                ->prefix('recipe')
                ->group(function () {
                    Route::post('add', 'add');
                    Route::post('delete', 'destroy');
                    Route::post('servings', 'changeServings');
                });

            Route::controller(\Modules\ShoppingList\Http\Controllers\API\ShoppingListIngredientApiController::class)
                ->prefix('ingredient')
                ->group(function () {
                    Route::post('add', 'add');
                    Route::post('remove', 'destroy');
                    Route::post('status', 'changeStatus');
                });
        }
    );
