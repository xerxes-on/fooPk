<?php

declare(strict_types=1);

namespace Modules\Course\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Course\Events\CourseArticleProcessedEvent;
use Modules\Course\Events\UserObtainedCourseEvent;
use Modules\Course\Listeners\CourseArticleProcessedListener;
use Modules\Course\Listeners\UserObtainedCourseListener;

final class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        CourseArticleProcessedEvent::class => [CourseArticleProcessedListener::class],
        UserObtainedCourseEvent::class => [UserObtainedCourseListener::class],
    ];
}
