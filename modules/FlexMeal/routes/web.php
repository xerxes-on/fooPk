<?php

Route::middleware(['lang.manager', 'auth', 'checkRole.user', 'check.questionnaire'])
    ->controller(\Modules\FlexMeal\Http\Controllers\FlexMealController::class)
    ->group(
        function () {
            Route::post('/', 'store')->name('recipes.flexmeal.save');
            Route::patch('/', 'update')->name('recipes.flexmeal.update');
            Route::delete('delete', 'destroy')->name('recipes.flexmeal.destroy');
            Route::post('check-flexmeal', 'check')->name('recipes.flexmeal.check');
        }
    );

Route::middleware(['lang.manager', 'auth', 'checkRole.user', 'check.questionnaire'])
    ->controller(\Modules\FlexMeal\Http\Controllers\FlexMealPreviewController::class)
    ->group(
        function () {
            Route::get('/', 'index')->name('recipes.flexmeal');

            Route::get('archive', 'show')->name('recipes.flexmeal.show');
            Route::get('show/{id}/{date}/{ingestion}', 'showSingle')->name('recipes.flexmeal.show_one');

            Route::get('for-mealtime', 'showByIngestion')->name('recipes.flexmeal.get_for_mealtime');
        }
    );
