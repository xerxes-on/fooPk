<?php

declare(strict_types=1);

namespace Modules\Foodpoints\Providers;

use Illuminate\Support\ServiceProvider;
use Symfony\Component\Finder\Finder;

final class FoodpointsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/foodpoints.php', 'foodpoints');
        $this->registerCommands('Modules\Foodpoints\Console\Commands');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    /**
     * Register commands
     */
    protected function registerCommands(string $namespace = ''): void
    {
        $finder = new Finder();
        $finder->files()->name('*.php')->in(__DIR__ . '/../Console');

        $classes = [];
        foreach ($finder as $file) {
            $class = $namespace . '\\' . $file->getBasename('.php');
            $classes[] = $class;
        }

        $this->commands($classes);
    }
}
