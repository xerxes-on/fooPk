<?php

use App\Http\Controllers\API\Auth\AuthApiController;
use App\Http\Controllers\API\Auth\RegistrationAPIController;
use App\Http\Controllers\Auth\ForgotPasswordController;

Route::prefix('v1')->group(
    function () {
        // Auth (unsecured)
        Route::controller(AuthApiController::class)
            ->group(
                function () {
                    Route::post('login', 'login');
                }
            );
        // Auth (secured)
        Route::controller(AuthApiController::class)
            ->middleware('auth:sanctum')
            ->group(
                function () {
                    Route::post('logout', 'logout');
                    Route::post('change-password', 'changePassword');
                }
            );

        // User registration
        Route::controller(RegistrationAPIController::class)
            ->prefix('registration')
            ->group(
                function () {
                    Route::post('send-email-confirmation', 'resendVerifyEmail')->middleware('throttle:5,1');
                    Route::post('verify-email', 'verify')->middleware('throttle:5,1');
                    Route::post('check-email-verification', 'checkConfirmation');
                    Route::post('finish-registration', 'finalizeRegistration');
                }
            );

        // Reset password
        Route::post('reset-password-email', [ForgotPasswordController::class, 'sendResetLinkEmail']);
    }
);
