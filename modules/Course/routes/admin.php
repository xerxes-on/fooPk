<?php

declare(strict_types=1);

use Modules\Course\Http\Controllers\Admin\ClientCourseAdminController;
use Modules\Course\Http\Controllers\Admin\CourseAdminController;

Route::prefix('courses')
    ->controller(CourseAdminController::class)
    ->group(
        function () {
            Route::get('articles', 'find')->name('admin.course.find');
            Route::post('articles', 'store')->name('admin.course.store');
            Route::post('articles/{id?}', 'attachArticleToCourse')->name('admin.course.attach');
            Route::delete('articles/{id}', 'destroy')->name('admin.course.destroy');
        }
    );

Route::prefix('clients/course')
    ->controller(ClientCourseAdminController::class)
    ->group(
        function () {
            Route::post('store', 'store')->name('admin.client.course.store');
            Route::post('edit', 'edit')->name('admin.client.course.edit');
            Route::delete('destroy', 'destroy')->name('admin.client.course.destroy');
        }
    );
