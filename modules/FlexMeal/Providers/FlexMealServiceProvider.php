<?php

declare(strict_types=1);

namespace Modules\FlexMeal\Providers;

use Illuminate\Support\ServiceProvider;

final class FlexMealServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'flexmeal');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->app->register(RouteServiceProvider::class);
    }
}
