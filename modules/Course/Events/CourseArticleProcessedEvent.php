<?php

declare(strict_types=1);

namespace Modules\Course\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Modules\Course\Models\CourseArticle;

final class CourseArticleProcessedEvent
{
    use Dispatchable;

    public function __construct(CourseArticle $article)
    {
    }
}
