<?php

use App\Http\Controllers\API\Integration\AOK\{AuthController, RecipeController};
use Illuminate\Support\Facades\Route;

Route::prefix('v1/aok')->group(
    function () {
        // Auth
        Route::post('auth', [AuthController::class, 'login']);

        // Protected routed
        Route::get('get-recipes-data', [RecipeController::class, 'getAvailableRecipes'])
            ->middleware(['auth:sanctum', 'abilities:aok-integration']);
    }
);
