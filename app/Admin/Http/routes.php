<?php

use App\Admin\Http\Controllers\{AdminController,
    AllergyAdminController,
    ArtisanCommandsAdminController,
    ClientNotesAdminController,
    ClientQuestionnaireAdminController,
    ClientsAdminController,
    DataTableAdminController,
    DietsAdminController,
    MediaLibraryAdminController,
    Recipe\RecipeAdminController,
    Recipe\RecipeCalculationsAdminController,
    Recipe\RecipeDestroyAdminController,
    Recipe\RecipeDistributionAdminController,
    Recipe\RecipeSearchAdminController,
    Recipe\RecipeTagAdminController,
    UserDashboardAdminController,
    VitaminsAdminController};
use Barryvdh\Elfinder\ElfinderController;

Route::get('', static fn() => AdminSection::view('Define your dashboard here.', 'Dashboard'))->name('admin.dashboard');

Route::namespace('\App\Admin\Http\Controllers')->group(
    function () {
        Route::get('datatable/async/{adminDisplayName?}', [DataTableAdminController::class, 'async'])->name('admin.datatable.async');

        Route::post('artisan/optimize/clear', [ArtisanCommandsAdminController::class, 'actionOptimizeClear'])
            ->name('admin.artisan.optimize.clear');


        Route::prefix('recipes')
            ->controller(RecipeAdminController::class)
            ->group(
                function () {
                    Route::get('import', 'import')->name('admin.recipes.import.index');

                    Route::get('search', 'searchRecipe')->name('admin.recipe.search');
                    Route::post('store', 'store')->name('admin.recipes.store');

                    Route::get('{id}/copy', 'copyRecipe')->where('id', '[0-9]+')->name('admin.recipes.copy-recipe');

                    Route::post('add-to-user', 'addRecipe2user')->name('admin.recipes.add-to-user');
                    Route::post('add-to-user-random', 'addRandomRecipe2user')->name('admin.recipes.add-to-user-random');
                    Route::post('generate-to-subscription', 'generate2subscription')
                        ->name('admin.recipes.generate-to-subscription');
                }
            );
        Route::prefix('recipes')
            ->controller(RecipeDestroyAdminController::class)
            ->group(
                function () {
                    Route::delete('delete-by-user/{recipeId}/{userId}', 'deleteRecipeByUser')->name('admin.recipes.delete-by-user');
                    Route::delete('delete-all-recipes/{userId}', 'deleteAllRecipeByAdmin')->name('admin.recipes.delete-all-recipes');
                    Route::delete('delete-selected-recipes', 'destroyBulk')->name('admin.recipes.delete-selected-recipes');
                }
            );
        Route::prefix('recipes')
            ->controller(RecipeCalculationsAdminController::class)
            ->group(
                function () {
                    Route::post('calculate-variable-ingredients', 'calculateVariableIngredients');
                    Route::post('get-diets', 'calculateRecipeDiets')->name('admin.recipes.get-diets');
                    Route::post('recalculate-to-user', 'recalculate2user')->name('admin.recipes.recalculate-to-user');
                    Route::post('recalculate-for-all-users', 'recalculateForAllUsers')
                        ->name('admin.recipes.recalculate-for-all-users');
                    Route::get('check-job-status/{userId}', 'checkJobStatus')->name('admin.recipes.check-calculation-status');
                }
            );
        Route::prefix('recipes')
            ->controller(RecipeSearchAdminController::class)
            ->group(
                function () {
                    Route::get('recipe/{recipeId}/for/{userId}', 'getRecipePreview')->name('admin.search-recipes.preview');
                    Route::post('recipe-custom-search/{excludedId}', 'customSearch')->name('admin.search-recipes.select2');
                }
            );

        Route::post('recipe_distribution/store', [RecipeDistributionAdminController::class, 'store'])
            ->name('admin.recipe-distribution.store');

        Route::prefix('recipe_tags')->controller(RecipeTagAdminController::class)->group(
            function () {
                Route::post('store', 'store')
                    ->name('admin.recipes.tags.store');
                Route::get('search', 'searchRecipeTag')->name('admin.recipes.tags.search');
            }
        );

        Route::post('clients/store/admin', [AdminController::class, 'store'])->name('admin.admin.store');

        Route::prefix('clients')->controller(ClientsAdminController::class)->group(
            function () {
                Route::post('ajax-search', 'customSearch')->name('admin.search-client.select2');

                Route::post('create/client/{id?}', 'create')->name('admin.client.create');
                Route::post('update/client/{id?}', 'update')->name('admin.client.store');
                Route::post('assign-chargebee-subscription', 'assignChargebeeSubscription')
                    ->name('admin.client.assign-chargebee-subscription');

                Route::post('{clientId}/profile-image', 'uploadProfileImage')
                    ->whereNumber('clientId')
                    ->name('admin.client.profile_image.upload');
                Route::delete('{clientId}/profile-image', 'deleteProfileImage')
                    ->whereNumber('clientId')
                    ->name('admin.client.profile_image.delete');

                // TODO: review and remove as it should be deprecated
                //				Route::get('{id}/formular/edit', 'formularPage')
                //					 ->whereNumber('id')
                //					 ->middleware(['canEditClientFormular'])
                //					 ->name('admin.clients.formular.edit');
                //				Route::post('{clientId}/formular/toggle', 'toggleFormularVisibility')
                //					 ->whereNumber('clientId')
                //					 ->name('admin.clients.formular.toggle');

                //				Route::post('formular/store', 'storeFormular')->name('admin.clients.formular.store');
                Route::post('calculations/client/{id?}', 'calculations')->name('admin.client.calculations');

                Route::post('subscription/client/{id?}', 'subscription')->name('admin.client.create-subscription');
                Route::put('subscription/{subscription}/edit', 'subscriptionEdit')->name('admin.client.subscription-edit');
                Route::put('subscription/{subscription}/stop', 'subscriptionStop')->name('admin.client.subscription-stop');
                Route::delete('subscription/{subscription}/delete', 'subscriptionDelete')
                    ->name('admin.client.subscription-delete');

                Route::post('deposit', 'deposit')->name('admin.client.deposit');
                Route::post('withdraw', 'withdraw')->name('admin.client.withdraw');
                Route::get('recipes/count-data', 'getRecipesCountData')->name('admin.client.recipes.count-data');


                Route::get('analytics', 'analytics')->name('admin.clients.analytics');
                // @deprecated, needs to be removed, @NickMost
                //Route::post('approve-formular', 'approveFormular')->name('admin.client.approve-formular');
                Route::get('formular-answers', 'getFormularAnswers')->name('admin.client.formular-answers');
                Route::post('calc-auto', 'setCalculateAutomatically')->name('admin.client.calc-auto');

                Route::get('randomize-recipe-template', 'randomizeRecipeTemplate')
                    ->name('admin.client.randomize-recipe-template');
            }
        );

        Route::prefix('clients')->controller(ClientQuestionnaireAdminController::class)->group(
            function () {
                Route::post('questionnaire/approve', 'approveQuestionnaire')->name('admin.client.questionnaire.approve');
                Route::post('questionnaire/toggle', 'toggleQuestionnaireVisibility')->name('admin.clients.questionnaire.toggle');
                Route::get('questionnaire/compare', 'getQuestionnaireAnswers')->name('admin.client.questionnaire.compare');

                Route::get('{clientId}/questionnaire/create', 'create')
                    ->whereNumber('clientId')
                    ->middleware(['canEditClientFormular'])
                    ->name('admin.clients.questionnaire.create');
                Route::get('{clientId}/questionnaire/edit', 'edit')
                    ->whereNumber('clientId')
                    ->middleware(['canEditClientFormular'])
                    ->name('admin.clients.questionnaire.edit');
                Route::post('questionnaire/store', 'store')->name('admin.clients.questionnaire.store');

                Route::post('approve-formular', 'approveQuestionnaire')->name('admin.client.approve-formular');
            }
        );

        Route::apiResource(
            'clients/client-notes',
            ClientNotesAdminController::class,
            ['only' => ['store', 'update', 'destroy'], 'as' => 'admin']
        );

        Route::get('elfinder', [MediaLibraryAdminController::class, 'showElfinder']);
        Route::get('elfinder/popup', [ElfinderController::class, 'showPopup']);

        Route::prefix('media-library')->controller(MediaLibraryAdminController::class)->group(
            function () {
                Route::get('/', 'index')->name('admin.mediaLibrary.index');
                Route::get('import', 'import')->name('admin.mediaLibrary.import');
            }
        );

        Route::post('vitamins/store', [VitaminsAdminController::class, 'store'])->name('admin.vitamins.store');
        Route::post('diets/store', [DietsAdminController::class, 'store'])->name('admin.diets.store');

        Route::post('recipe_tags/store', [RecipeTagAdminController::class, 'store'])
            ->name('admin.recipes.tags.store');

        Route::post('allergies/store', [AllergyAdminController::class, 'store'])->name('admin.allergies.store');

        Route::post('user-dashboard/{user_dashboard}/edit', [UserDashboardAdminController::class, 'postEdit'])
            ->name('admin.user.dashboard.update');

        Route::get('internal/export-users-with-lipedema', [ArtisanCommandsAdminController::class, 'exportUsersWithLipedema']);

        // TODO:: refactor that @NickMost, hardcode
        Route::get('internal/potential_issues', function () {
            $LOG_INSTANCE_TYPE = env('LOG_INSTANCE_TYPE', 'production');
            if ($LOG_INSTANCE_TYPE == 'static' || $LOG_INSTANCE_TYPE == '') {
                Artisan::call('fix20231229');
            }
        });
    }
);
