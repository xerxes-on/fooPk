<?php

declare(strict_types=1);

namespace Modules\Course\Http\View\Components;

use Illuminate\View\View;

final class ArticleTabLinkComponent extends ArticleTab
{
    public function render(): View
    {
        return view('course::components.article-tab-link');
    }
}
