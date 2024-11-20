<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

final class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
        $this->routes(function () {
            Route::middleware('web')->group(base_path('modules/ShoppingList/routes/web.php'));

            Route::middleware(['api', 'auth:sanctum'])
                ->prefix('api')
                ->group(base_path('modules/ShoppingList/routes/api.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', static fn(Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));
    }
}
