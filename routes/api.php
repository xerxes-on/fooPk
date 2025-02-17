<?php

use App\Http\Controllers;
use App\Http\Controllers\API as APIControllers;
use Illuminate\Support\Facades\Route;
use Modules\PushNotification\Http\Controllers\API\UserDeviceApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(
    function () {
        // Old routes
        Route::get('foodpoints', [APIControllers\ProfileApiController::class, 'getFoodpoints']);
        Route::get('menu-data', [APIControllers\ProfileApiController::class, 'getMenuData']);

        // Reset password
        Route::post('reset-password-email', [Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail']);

        // Auth
        Route::controller(APIControllers\Auth\AuthApiController::class)
            ->group(
                function () {
                    Route::post('login', 'login');
                    Route::post('logout', 'logout')->middleware('auth:sanctum');
                    Route::post('change-password', 'changePassword')->middleware('auth:sanctum');
                }
            );

        // Dictionary
        Route::controller(APIControllers\DictionaryApiController::class)
            ->prefix('dictionary')
            ->group(
                function () {
                    Route::get('/', 'get');
                    Route::post('/', 'upload')->middleware('auth:sanctum');
                }
            );

        // Protected routed
        Route::middleware('auth:sanctum')
            ->group(
                function () {
                    // Recipes
                    Route::controller(APIControllers\RecipesApiController::class)
                        ->group(
                            function () {
                                Route::get('recipe/{id}', 'get')->middleware('throttle:10,1');
                                Route::get('all-recipes', 'getAllRecipes');
                                Route::get('recipes-filter-options', 'getFilterOptions');
                                Route::post('favourite-recipe/{recipe}', 'favourite');
                                Route::post('unfavourite-recipe/{recipe}', 'unfavourite');
                                Route::post('replace-ingredient', 'replaceIngredient');
                                Route::post('restore-recipe', 'restore')->middleware('check.customRecipeAccess');
                                Route::post('recipe/{id}/exclude', 'exclude');
                                Route::delete('recipe/{id}/restore', 'removeFromExcluded');
                                Route::get('recipes-to-buy', 'getRecipesToBuy');
                                Route::post('buy-recipe/{recipe_id}', 'buy');
                            }
                        );

                    // Custom categories
                    Route::controller(APIControllers\CustomCategoryApiController::class)
                        ->group(
                            function () {
                                Route::post('add-recipe-category', 'addToRecipe');
                                Route::delete('delete-recipe-category/{category_id}', 'delete');
                                Route::get('list-recipe-categories', 'list');
                                Route::post('edit-recipe-category', 'edit');
                                Route::post('detach-recipe-category', 'detach');
                            }
                        );

                    // Meals
                    Route::controller(APIControllers\MealsApiController::class)
                        ->group(
                            function () {
                                Route::get('planned-meal', 'getPlanned');
                                Route::get('plan', 'getPlan');
                                Route::post('replace-recipe', 'replace');
                                Route::post('eat-out', 'eatOutRecipe');
                                Route::get('planned-meal-ingredients', 'getPlannedMealIngredients');
                            }
                        );

                    // Formular
                    Route::controller(APIControllers\FormularApiController::class)
                        ->prefix('formular')
                        ->group(
                            function () {
                                Route::get('questions', 'getQuestions');
                                Route::get('get', 'getFormular');
                                Route::get('check-free-edit', 'checkEditPeriod');
                                Route::get('buy-edit', 'buyEditing');
                                Route::post('store', 'store');
                                Route::get('status', 'getStatus');
                                Route::delete('delete', 'clearUserFormular');
                            }
                        );

                    // Profile
                    Route::controller(APIControllers\ProfileApiController::class)
                        ->prefix('profile')
                        ->group(
                            function () {
                                Route::get('foodpoints', 'getFoodpoints');
                                Route::get('menu-data', 'getMenuData');
                                Route::get('diet-data', 'getDietData');
                                Route::get('all', 'getProfileData');
                                Route::post('update', 'update');
                                Route::post('image', 'updateAvatar');
                                Route::delete('image', 'deleteAvatar');
                                Route::post('delete', 'deleteProfile');
                            }
                        );

                    // Diary
                    Route::controller(APIControllers\DiaryApiController::class)
                        ->prefix('diary')
                        ->group(
                            function () {
                                Route::get('get', 'get');
                                Route::post('store', 'store');
                                Route::get('edit/{date}', 'getForEdit');
                                Route::post('update/{id}', 'update')->whereNumber('id');
                                Route::delete('delete/{id}', 'destroy')->whereNumber('id');
                                Route::post('charts', 'getChartData');
                            }
                        );

                    // Posts
                    Route::controller(APIControllers\PostsApiController::class)
                        ->prefix('posts')
                        ->group(
                            function () {
                                Route::get('/', 'getAll');
                                Route::post('store', 'store');
                                Route::post('{id}/update', 'update')->whereNumber('id');
                                Route::delete('{id}/delete', 'destroy')->whereNumber('id');
                            }
                        );

                    // User devices
                    Route::controller(UserDeviceApiController::class)
                        ->prefix('user-device')
                        ->group(
                            function () {
                                Route::post('register', 'register');
                            }
                        );
                }
            );
    }
);

require_once 'partials/api.auth.php';
require_once 'partials/api.questionnaire.php';

/*
|--------------------------------------------------------------------------
| External API Routes
|--------------------------------------------------------------------------
|
| These routes are used for external API integrations.
|
*/

require_once 'partials/external/api.aok.php';
require_once 'partials/external/api.apinity.php';
