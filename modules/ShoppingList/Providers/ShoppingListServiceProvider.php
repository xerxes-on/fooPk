<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\ShoppingList\View\Components\ShoppingListRecipeComponent;

final class ShoppingListServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->register(RouteServiceProvider::class);
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'shopping-list');
        $this->mergeConfigFrom(__DIR__ . '/../config/shopping-list.php', 'shopping-list');
        \View::addNamespace('shopping-list', __DIR__ . '/../resources/views');
        Blade::component('shopping-list-recipe', ShoppingListRecipeComponent::class);
    }
}
