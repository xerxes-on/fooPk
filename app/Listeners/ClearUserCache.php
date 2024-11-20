<?php

namespace App\Listeners;

use App\Helpers\CacheKeys;
use Cache;
use Log;

class ClearUserCache extends EventBase
{
    /**
     * Handle the event.
     *
     * @param object|null $event
     * @return void
     */
    public function handle($event = null)
    {
        // TODO: need to somehow group required data to pass as one wipe call
        if (empty($this->userId)) {
            return;
        }
        Cache::forget(CacheKeys::userExcludedRecipesIds($this->userId));
        Cache::forget(CacheKeys::allIngredients());
        Cache::forget(CacheKeys::userShoppingListIngredients($this->userId));
        /**
         * TODO. need to inspect the issue later.
         *
         * Clearing the list keeps recipes in place. Recipes in list generates from all recipes.
         * probable need to find a better solution with caching or rework the way recipes obtained for sopping list
         */
        Cache::forget(CacheKeys::allUserRecipes($this->userId));
        //Cache::forget(CacheKeys::recipesToBuy($this->userId)); // TODO: remove as not needed now
        Cache::forget(CacheKeys::allUserRecipes($this->userId));
        Cache::forget(CacheKeys::userWeeklyPlan($this->userId, now()->weekOfYear));
        Log::channel('cache')->info("User cached data {$this->userId} cleared.");
    }
}
