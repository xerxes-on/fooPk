<?php

declare(strict_types=1);

namespace Modules\Course\Http\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

final class CustomCourseComponent extends Component
{
    public string $articleImg;

    /**
     * Create a new component instance.
     */
    public function __construct(public ?Collection $article)
    {
        $this->articleImg = !empty($article['thumbnail_url_full']) ? $article['thumbnail_url_full'] : 'https://via.placeholder.com/300';
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('course::components.custom-course-article');
    }
}
