<?php

// User login
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\LoginController;

Route::controller(LoginController::class)
    ->group(
        function () {
            Route::get('login', 'showLoginForm')->name('login');
            Route::post('login', 'login');
            Route::post('logout', 'logout')->name('logout.post');
            Route::get('logout', 'logout')->name('logout.get');
        }
    );

// Admin login
Route::controller(AdminLoginController::class)
    ->group(
        function () {
            Route::get('login/admin', 'showLoginForm')->name('login.admin');
            Route::post('login/admin', 'login')->name('login.admin.submit');
        }
    );
