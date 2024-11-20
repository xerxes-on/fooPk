<?php

namespace Modules\Internal\Providers;

use Illuminate\Support\ServiceProvider;
use Symfony\Component\Finder\Finder;

final class InternalServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerCommands('Modules\Internal\Console\Commands');
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
            $class     = $namespace . '\\' . $file->getBasename('.php');
            $classes[] = $class;
        }

        $this->commands($classes);
    }
}
