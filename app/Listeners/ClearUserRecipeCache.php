<?php

namespace App\Listeners;

use App\Helpers\CacheKeys;
use Cache;
use Carbon\Carbon;
use Log;

/**
 * Clean user recipe cache.
 *
 * @package App\Listeners
 */
final class ClearUserRecipeCache extends EventBase
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

        //Cache::forget(CacheKeys::recipesToBuy($this->userId));// TODO: remove as not needed now
        Cache::forget(CacheKeys::allUserRecipes($this->userId));
        Cache::forget(CacheKeys::userWeeklyPlan($this->userId, Carbon::now()->weekOfYear));
        Cache::forget(CacheKeys::userExcludedRecipesIds($this->userId));
        Log::channel('cache')->info("Recipe cache cleared for user {$this->userId} cleared.");
    }
}
