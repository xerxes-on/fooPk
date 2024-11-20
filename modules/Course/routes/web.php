<?php

declare(strict_types=1);

use Modules\Course\Http\Controllers\ArticlesController;
use Modules\Course\Http\Controllers\CourseController;

Route::middleware(['lang.manager', 'auth', 'checkRole.user', 'check.questionnaire'])
    ->prefix('courses')
    ->controller(CourseController::class)
    ->group(
        function () {
            Route::get('all', 'index')->name('course.index');
            Route::get('buy', 'shop')->name('course.buy');
            Route::post('buying', 'buyingChallenge')->name('course.buying');
            Route::post('reschedule', 'reschedule')->name('course.reschedule');
        }
    );

Route::middleware(['lang.manager', 'auth', 'checkRole.user', 'check.questionnaire', 'check.challenge'])
    ->prefix('articles')
    ->controller(ArticlesController::class)
    ->group(
        function () {
            Route::get('/', 'index')->name('articles.list');
            Route::get('/{id}/{days}', 'show')->whereNumber(['id', 'days'])->name('articles.show');
        }
    );
