<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        \Modules\ShoppingList\Events\ShoppingListProcessed::class => [\Modules\ShoppingList\Listeners\ClearShoppingListCache::class],
    ];
}
