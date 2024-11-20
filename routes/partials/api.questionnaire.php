<?php

use App\Http\Controllers\API\Questionnaire\QuestionnaireAnonymousAPIController;
use App\Http\Controllers\API\Questionnaire\QuestionnaireAPIController;

Route::prefix('v1/questionnaire')
    ->group(
        function () {
            // Anonymous user questionnaire
            Route::controller(QuestionnaireAnonymousAPIController::class)->group(function () {
                Route::post('start', 'start');
                Route::post('save-proceed', 'saveAndProceed');
                Route::post('previous-question', 'goToPreviousQuestion');
                Route::post('search-ingredient', 'searchIngredients');
                Route::delete('delete-temp/{fingerprint}', 'deleteTemp');
            });

            // Authenticated user questionnaire
            Route::controller(QuestionnaireAPIController::class)
                ->middleware('auth:sanctum')
                ->group(
                    function () {
                        Route::get('latest', 'getQuestionnaireData');
                        Route::get('edit-period', 'checkEditPeriod');
                        Route::get('status', 'getStatus');
                        Route::prefix('edit')->group(function () {
                            Route::post('start', 'startEditing');
                            Route::post('save-proceed', 'saveAndProceed');
                            Route::post('previous-question', 'goToPreviousQuestion');
                            Route::post('search-ingredient', 'searchIngredients');
                            Route::post('finalize', 'finalizeEditing');
                            Route::post('buy', 'buyEditing');
                        });
                    }
                );
        }
    );
