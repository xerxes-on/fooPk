<?php

declare(strict_types=1);

namespace Modules\Course\Listeners;

use Illuminate\Support\Facades\Cache;
use Modules\Course\Events\CourseArticleProcessedEvent;
use Modules\Course\Service\ArticleService;

class CourseArticleProcessedListener
{
    public function handle(CourseArticleProcessedEvent $event): void
    {
        Cache::forget(sprintf(ArticleService::COURSE_ARTICLE_CACHE_KEY, $event->id));
    }
}
