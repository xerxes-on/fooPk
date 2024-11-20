<?php

declare(strict_types=1);

namespace Modules\Ingredient\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\Ingredient\View\Components\IngredientTipComponent;
use Modules\Ingredient\View\Components\RecipeIngredientsComponent;

final class IngredientServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'ingredient');
        \View::addNamespace('ingredient', __DIR__ . '/../resources/views');
        Blade::component(IngredientTipComponent::class, 'ingredient-tip');
//        Blade::component(RecipeIngredientsComponent::class, 'recipe-ingredient');
    }
}
