<?php

declare(strict_types=1);

namespace Modules\Course\Http\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class DailyCourseArticleComponent extends Component
{
    public string $articleImg;
    public bool $canRender;

    public function __construct(public readonly ?array $article, array $courseData)
    {
        $this->articleImg = !empty($article['thumbnail_url_full']) ?
            $article['thumbnail_url_full'] :
            config('stapler.api_url') . '/300';
        $this->canRender = isset($courseData['curDay']) &&
            ($courseData['curDay'] <= $courseData['duration']) &&
            ($courseData['curDay'] > 0) &&
            !is_null($article) &&
            !is_null($article['ID']);
    }

    public function render(): View|Closure|string
    {
        return view('course::components.daily-course-article');
    }
}
