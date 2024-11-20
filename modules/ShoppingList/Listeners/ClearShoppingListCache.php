<?php

declare(strict_types=1);

namespace Modules\ShoppingList\Listeners;

use App\Helpers\CacheKeys;
use App\Listeners\EventBase;
use Cache;
use Log;

class ClearShoppingListCache extends EventBase
{
    /**
     * Handle the event.
     *
     * @param object|null $event
     * @return void
     */
    public function handle($event = null)
    {
        if (empty($this->userId)) {
            return;
        }
        Cache::forget(CacheKeys::userShoppingListIngredients($this->userId));
        /**
         * TODO. need to inspect the issue later.
         *
         * Clearing the list keeps recipes in place. Recipes in list generates from all recipes.
         * probable need to find a better solution with caching or rework the way recipes obtained for sopping list
         */
        Cache::forget(CacheKeys::allUserRecipes($this->userId));
        Log::channel('cache')->info("Shopping list cache for user {$this->userId} cleared.");
    }
}
