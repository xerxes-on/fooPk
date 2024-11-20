<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Cache;
use Log;

/**
 * Listener that simple wipe all cache on events.
 *
 * @package App\Listeners
 */
class WipeCache
{
    /**
     * Handle the event.
     *
     * @param object|null $event
     * @return void
     */
    public function handle($event = null)
    {
        Cache::flush();
        Log::channel('cache')->info('Cache flushed successfully.');
    }
}
