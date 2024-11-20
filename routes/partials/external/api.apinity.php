<?php

use App\Http\Controllers\API\Integration\Apinity\{AuthController, RecipeController};
use Illuminate\Support\Facades\Route;

Route::prefix('v1/apinity')->group(
    function () {
        // Auth
        Route::post('auth', [AuthController::class, 'login']);

        // Protected routed
        Route::get('get-recipes-data', [RecipeController::class, 'getAvailableRecipes'])
            ->middleware(['auth:sanctum', 'abilities:apinity-integration']);
    }
);
