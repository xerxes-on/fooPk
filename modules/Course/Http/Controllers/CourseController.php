<?php

declare(strict_types=1);

namespace Modules\Course\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Course\Actions\BuyCourseAction;
use Modules\Course\Actions\RescheduleCourseAction;
use Modules\Course\Http\Requests\BuyCourseRequest;
use Modules\Course\Http\Requests\CourseRescheduleRequest;
use Modules\Course\Models\Course;

/**
 * Course controller.
 *
 * @package Modules\Course\Http\Controllers
 */
final class CourseController extends Controller
{
    public function index(Request $request): View|Factory
    {
        return view('course::index', [
            'courses'       => Course::getUserCourses($request->user()),
            'userCoursesId' => $request->user()->courses()->pluck('course_id'),
        ]);
    }

    public function shop(Request $request): View|Factory
    {
        $user = $request->user();
        $now  = Carbon::now()->format('d.m.Y');
        return view('course::shop', [
            'courses' => Course::getPurchasable($user)
                ->map(
                    // need to filter data for correct display in date picker, 0d or date in format d.m.Y
                    function (Course $challenge) use ($now) {
                        $challenge->minimum_start_at_for_js = empty($challenge->minimum_start_at) ?
                            $now :
                            $challenge->minimum_start_at->startOfDay()->format('d.m.Y');
                        return $challenge;
                    }
                ),
            'userCoursesId' => $user->courses()->pluck('course_id'),
        ]);
    }

    public function buyingChallenge(BuyCourseRequest $request, BuyCourseAction $action): JsonResponse
    {
        return $action->handle($request);
    }

    public function reschedule(CourseRescheduleRequest $request, RescheduleCourseAction $action): JsonResponse
    {
        return $action->handle($request);
    }
}
