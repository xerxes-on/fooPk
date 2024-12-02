<?php

declare(strict_types=1);

namespace Modules\Course\Listeners;

use App\Helpers\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Modules\Course\Events\UserObtainedCourseEvent;

class UserObtainedCourseListener
{
    public function handle(UserObtainedCourseEvent $event): void
    {
        Cache::forget(CacheKeys::userParticipatedCourses($event->user->id));
    }
}
