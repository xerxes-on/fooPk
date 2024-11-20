<?php

Route::middleware(['lang.manager', 'auth', 'checkRole.user', 'check.questionnaire'])
    ->prefix('user/ingredients')
    ->controller(\Modules\Ingredient\Http\Controllers\IngredientsController::class)
    ->group(
        function () {
            Route::get('', 'getUserAllowedIngredients')->name('ingredients.get');
            Route::get('all', 'index')->name('ingredients.all');
            Route::get('search', 'searchIngredientsViaSelect2')->name('ingredients.search.all')->withoutMiddleware('check.questionnaire');
            Route::get('search/designated', 'searchUserDesignated')->name('ingredients.search.client-designated');
        }
    );
