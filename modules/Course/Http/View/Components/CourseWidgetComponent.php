<?php

declare(strict_types=1);

namespace Modules\Course\Http\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use InvalidArgumentException;

final class CourseWidgetComponent extends Component
{
    public bool $canRender;

    public function __construct(public readonly array $courseData, public readonly string $type = 'normal')
    {
        match ($type) {
            'normal', 'mini_mobile', 'mini_desktop' => true,
            default => throw new InvalidArgumentException('Unsupported type'),
        };
        $this->canRender = isset($courseData['curDay']) && ($courseData['curDay'] <= $courseData['duration']);
    }

    public function render(): View|Closure|string
    {
        return view('course::components.course-widget');
    }
}
