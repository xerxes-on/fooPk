<?php

use Modules\Ingredient\Http\Controllers\Admin\IngredientCategoryAdminController;
use Modules\Ingredient\Http\Controllers\Admin\IngredientsAdminController;
use Modules\Ingredient\Http\Controllers\Admin\IngredientTagAdminController;

Route::prefix('ingredients')->controller(IngredientsAdminController::class)->group(
    function () {
        // Route::get('import', 'import')->name('admin.ingredients.import.index');

        Route::get('search', 'searchIngredient')->name('admin.ingredients.search');
        Route::get('search-ajax/{all?}', 'searchIngredientsAjax')->name('admin.search-ingredients.select2');
        Route::post('store', 'store')->name('admin.ingredients.store');

        // Search ingredients via ajax for questionnaire
        Route::get('search/{clientId}/designated', 'searchClientDesignatedIngredients')
            ->whereNumber('clientId')
            ->name('admin.ingredients.search.client-designated');
    }
);

Route::prefix('ingredient_tags')->controller(IngredientTagAdminController::class)->group(
    function () {
        Route::post('store', 'store')->name('admin.ingredients.tags.store');
        Route::post('search-ajax', 'customSearch')->name('admin.search_ingredient_tag.select2');
        Route::get('search', 'searchIngredientTag')->name('admin.search-ingredient-tags.search');
    }
);

Route::prefix('ingredient-categories')->controller(IngredientCategoryAdminController::class)->group(
    function () {
        Route::post('store', 'store')->name('admin.ingredientCategories.store');
        Route::post('get/child', 'getChildCategories')->name('admin.ingredientCategories.getChild');
    }
);
