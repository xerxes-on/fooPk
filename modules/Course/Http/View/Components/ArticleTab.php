<?php

declare(strict_types=1);

namespace Modules\Course\Http\View\Components;

use Illuminate\View\Component;

abstract class ArticleTab extends Component
{
    public string $id;

    public function __construct(public readonly array $courseArticleData, public bool $isActive = false)
    {
        $this->id = $this->generateId();
    }

    private function generateId(): string
    {
        return md5('course_' . $this->courseArticleData['id']);
    }
}
