<?php

namespace Modules\Chargebee\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

final class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->routes(function () {
            Route::middleware('web')->group(base_path('modules/Chargebee/routes/web.php'));

//            Route::middleware(['api', 'auth:sanctum'])
            Route::middleware(['api'])
                ->prefix('api')
                ->group(base_path('modules/Chargebee/routes/api.php'));
        });
    }
}
