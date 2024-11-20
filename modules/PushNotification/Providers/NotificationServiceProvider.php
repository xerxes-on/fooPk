<?php

declare(strict_types=1);

namespace Modules\PushNotification\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\PushNotification\Action\InvalidTokensRemoveAction;
use Modules\PushNotification\Action\ReportGroupingAction;

final class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->register(RouteServiceProvider::class);

        $this->app->singleton(ReportGroupingAction::class);
        $this->app->singleton(InvalidTokensRemoveAction::class);

        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'PushNotification');
        \View::addNamespace('PushNotification', __DIR__ . '/../resources/views');
    }
}
