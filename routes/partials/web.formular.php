<?php

use App\Http\Controllers\QuestionnaireController;

/** @deprecated routes */
//Route::controller(FormularController::class)
//	 ->group(
//		 function () {
//			 Route::get('formular', 'create')->name('formular.create');
//			 Route::post('formular', 'store')->name('formular.store');
//			 Route::get('formular/check', 'checkEditPeriod')->name('formular.checkEditPeriod');
//			 Route::get('formular/edit', 'edit')->name('formular.edit');
//			 Route::post('formular/buy_editing', 'buyEditing')->name('formular.buy_editing');
//		 }
//	 );

Route::middleware(['cache.web.policy.noCache'])->controller(QuestionnaireController::class)
    ->group(
        function () {
            Route::get('questionnaire', 'create')->name('questionnaire.create');
            Route::get('questionnaire/start', 'start')->name('questionnaire.start');
            Route::post('questionnaire/next', 'next')->name('questionnaire.next');
            Route::post('questionnaire/previous', 'goToPreviousQuestion')->name('questionnaire.previous');
            Route::post('questionnaire/ingredients/search', 'searchIngredients')
                ->name('questionnaire.search.ingredients');
            Route::post('questionnaire/store', 'storeFirstQuestionnaire')->name('questionnaire.store.first');

            Route::get('questionnaire/edit', 'edit')->name('questionnaire.edit');
            Route::post('questionnaire', 'store')->name('questionnaire.store');
            Route::get('questionnaire/check', 'checkEditPeriod')->name('questionnaire.checkEditPeriod');
            Route::get('questionnaire/edit/buy', 'buyEditing')->name('questionnaire.edit.buy');
        }
    );
