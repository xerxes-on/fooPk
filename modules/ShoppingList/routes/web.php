<?php

declare(strict_types=1);

Route::middleware(['lang.manager', 'auth', 'checkRole.user', 'check.questionnaire'])
    ->prefix('user/purchases')
    ->group(
        function () {
            Route::controller(\Modules\ShoppingList\Http\Controllers\WEB\ShoppingListController::class)
                ->prefix('list')
                ->group(function () {
                    Route::get('', 'index')->name('purchases.list');
                    Route::post('', 'createListByDates')->name('purchases.list.createByDate');
                    Route::get('clear', 'clearList')->name('purchases.list.clear');
                    Route::get('print', 'printList')->name('purchases.list.print');
                });

            Route::controller(\Modules\ShoppingList\Http\Controllers\WEB\ShoppingListRecipeController::class)
                ->prefix('recipe')
                ->group(function () {
                    Route::post('add-to-shopping-list', 'add')->name('purchases');
                    Route::post('delete', 'destroy')->name('purchases.recipe.delete');
                    Route::post('changeServings', 'changeServings')->name('purchases.recipe.changeServings');
                });

            Route::controller(\Modules\ShoppingList\Http\Controllers\WEB\ShoppingListIngredientController::class)
                ->prefix('ingredient')
                ->group(function () {
                    Route::post('add', 'add')->name('purchases.ingredient.new');
                    Route::post('delete', 'destroy')->name('purchases.ingredient.delete');
                    Route::post('check', 'changeStatus')->name('purchases.ingredient.check');
                });
        }
    );
