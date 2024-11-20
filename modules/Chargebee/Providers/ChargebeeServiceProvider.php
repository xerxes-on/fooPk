<?php

declare(strict_types=1);

namespace Modules\Chargebee\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Chargebee\Services\ChargebeeService;

final class ChargebeeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->register(RouteServiceProvider::class);
        $this->mergeConfigFrom(__DIR__ . '/../config/chargebee.php', 'chargebee');
    }

    public function register():void {
        $this->app->singleton(ChargebeeService::class, function ($app) {
            return new ChargebeeService();
        });
    }
}
