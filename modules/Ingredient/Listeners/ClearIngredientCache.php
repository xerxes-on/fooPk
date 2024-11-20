<?php

namespace Modules\Ingredient\Listeners;

use App\Helpers\CacheKeys;
use App\Listeners\EventBase;
use Cache;
use Log;
use Modules\Ingredient\Events\IngredientProcessed;

final class ClearIngredientCache extends EventBase
{
    public function handle(?IngredientProcessed $event = null): void
    {
        Cache::forget(CacheKeys::allIngredients());
        Cache::forget(CacheKeys::allIngredientIds());
        Log::channel('cache')->info('Ingredients cache cleared');
    }
}
