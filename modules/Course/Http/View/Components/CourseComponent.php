<?php

declare(strict_types=1);

namespace Modules\Course\Http\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Modules\Course\Enums\CourseId;
use Modules\Course\Enums\UserCourseStatus;
use Modules\Course\Models\Course;

final class CourseComponent extends Component
{
    public string $coursePrice    = '';
    public bool $courseIsFinished = false;
    public bool $isForPurchase    = false;
    public UserCourseStatus $courseStatus;
    public bool $isGuide;

    /**
     * Create a new component instance.
     */
    public function __construct(public Course $course, public Collection $userCoursesId)
    {
        $this->isForPurchase = isset($course->minimum_start_at_for_js);
        $this->courseStatus  = UserCourseStatus::NOT_PURCHASED;
        $this->isGuide       = CourseId::isGuide($course->id);
        if ($this->isForPurchase === false) {
            $this->courseStatus     = $course->getStatus();
            $this->courseIsFinished = $this->courseStatus === UserCourseStatus::FINISHED;
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $this->setUpPrice();
        return view('course::components.course-component');
    }

    private function setUpPrice(): void
    {
        $actualPrice = $this->course->getActualPrice($this->userCoursesId);
        $textFp      = ' FP';
        $text        = $actualPrice;
        if ($actualPrice === 0) {
            $textFp = '';
            $text   = trans('course::common.free');
        }

        $this->coursePrice = sprintf('<b id="foodpoint" data-price="%s">%s</b>%s', $actualPrice, $text, $textFp);
    }
}
