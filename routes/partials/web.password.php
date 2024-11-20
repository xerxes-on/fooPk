<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

Route::controller(ForgotPasswordController::class)
    ->prefix('password')
    ->middleware(['guest'])
    ->group(
        function () {
            Route::get('reset', 'showLinkRequestForm')->name('password.request');
            Route::post('email', 'sendResetLinkEmail')->name('password.email');
        }
    );
// Password Reset Routes...
Route::controller(ResetPasswordController::class)
    ->prefix('password')
    ->group(
        function () {
            // TODO:: review it
            Route::get('reset/{token}/{language}', 'showResetForm')->name('password.reset.by_token');
            Route::get('reset/{token}', 'showResetForm')->name('password.reset');
            Route::post('reset', 'reset');
        }
    );
