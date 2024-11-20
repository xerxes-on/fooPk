<?php

use App\Http\Controllers\Recipes\FavoriteController;
use App\Http\Controllers\Recipes\CustomRecipeController;
use App\Http\Controllers\Recipes\RecipeController;
use App\Http\Controllers\Recipes\RecipeCookingPlannerController;
use App\Http\Controllers\Recipes\RecipeReplacementController;
use App\Http\Controllers\Recipes\RecipeShopController;
use App\Http\Controllers\Recipes\RecipeSkipController;

// Recipes
Route::controller(RecipeController::class)
    ->prefix('recipes')
    ->group(
        function () {
            // Display
            Route::get('list', 'listView')->name('recipes.list');
            Route::get('grid', 'gridView')->name('recipes.grid');
            Route::get('{id}/{date?}/{ingestion?}', 'show')->where('id', '[0-9]+')->name('recipe.show');

            // Single item
            Route::get('view/{id}', 'showFromAllRecipes')
                ->where('id', '[0-9]+')
                ->name('recipe.allRecipes.show');
            Route::get('custom/{id}/{date}/{ingestion}', 'showCustomCommon')
                ->where('id', '[0-9]+')
                ->name('recipe.show.custom.common');
            Route::get('/custom/{id}', 'showCustom')->where('id', '[0-9]+')->name('recipe.show.custom');

            // All recipes
            Route::get('all', 'allRecipes')->name('recipes.all.get');
            Route::post('all', 'allRecipes')->name('recipes.all.post');
            Route::post('get_user_recipes', 'getRecipesByRationFood')->name('recipes.ration_food');
        }
    );

// Buy recipes
Route::controller(RecipeShopController::class)
    ->prefix('recipes')
    ->group(
        function () {
            Route::get('buy', 'recipesToBuy')->name('recipes.buy.get');
            Route::post('buy', 'recipesToBuy')->name('recipes.buy.post');
            Route::post('buying', 'buyingRecipes')->name('recipes.buying');
        }
    );

// Recipe Cooked
Route::controller(RecipeCookingPlannerController::class)
    ->prefix('recipes')
    ->group(
        function () {
            Route::post('to-cook', 'toCookRecipe')->name('toCook');
            Route::post('uncook', 'unCookRecipe')->name('unCook');
        }
    );

// Recipe Skip/eatOut
Route::controller(RecipeSkipController::class)
    ->prefix('recipes')
    ->group(
        function () {
            Route::post('eatout', 'eatOutRecipe')->name('eatOut');
        }
    );

// Recipe replacement
Route::controller(RecipeReplacementController::class)
    ->prefix('recipes')
    ->group(
        function () {
            Route::post('replacement', 'recipeReplacement')->name('recipes.replacement');
            Route::post('apply_to_date', 'applyRecipe2date')->name('recipes.apply_to_date');
            Route::post('replace-with-flexmeal', 'replaceWithFlexmeal')->name('recipes.replace_with_flexmeal');
        }
    );

// Favorite recipes
Route::controller(FavoriteController::class)
    ->group(
        function () {
            Route::post('favorite/{recipe}', 'favoriteRecipe')->name('favorites');
            Route::post('unfavorite/{recipe}', 'unFavoriteRecipe')->name('unFavorites');
        }
    );

// User custom recipes
Route::controller(CustomRecipeController::class)
    ->prefix('recipes/custom')
    ->middleware('check.challenge') // TODO: do we need it?
    ->group(
        function () {
            Route::post('create', 'createFromCommonRecipe')->name('recipes.own.create-from-recipe');

            Route::get('{id}/restore', 'restoreRecipe')
                ->middleware('check.customRecipeAccess')
                ->where('id', '[0-9]+')
                ->name('recipes.own.restore');
        }
    );
