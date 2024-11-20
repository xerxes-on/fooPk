<?php

declare(strict_types=1);

namespace Modules\Ingredient\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Ingredient\Events\IngredientProcessed;
use Modules\Ingredient\Listeners\ClearIngredientCache;

final class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        IngredientProcessed::class => [ClearIngredientCache::class]
    ];
}
