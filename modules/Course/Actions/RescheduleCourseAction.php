<?php

declare(strict_types=1);

namespace Modules\Course\Actions;

use App\Http\Traits\CanSendJsonResponse;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Modules\Course\Http\Requests\CourseRescheduleRequest;
use Modules\Course\Models\Course;

/**
 * Action handling rescheduling of a course.
 *
 * @package Modules\Course\Actions
 */
class RescheduleCourseAction
{
    use CanSendJsonResponse;

    private ?Course $course;
    private Carbon $startAt;
    private Carbon $endsAt;

    public function handle(CourseRescheduleRequest $request): JsonResponse
    {
        $user          = $request->user();
        $this->startAt = $request->startDate;

        try {
            $this->getCourse($request->courseId, $user);
        } catch (\Throwable) {
            return $this->sendError('', trans('course::common.not_found'));
        }

        try {
            $this->setupDurationDates();
        } catch (\Throwable $e) {
            logError($e);
            return $this->sendError('', trans('course::common.date_parsing_error'));
        }

        try {
            $user->courses()
                ->updateExistingPivot(
                    $this->course->id,
                    [
                        'start_at' => $this->startAt,
                        'ends_at'  => $this->endsAt
                    ]
                );
        } catch (\Throwable $e) {
            logError($e); //TODO: investigate
        }

        return $this->sendResponse(null, trans('course::common.scheduled'));
    }

    private function getCourse(int $courseId, User $user): void
    {
        $this->course = $user->_prepareCoursesForUser(collect([Course::findOrFail($courseId)]))->first();

        if (is_null($this->course)) {
            throw new ModelNotFoundException('');
        }
    }

    private function setupDurationDates(): void
    {
        if (!empty($this->course->minimum_start_at) && $this->startAt->lt($this->course->minimum_start_at)) {
            $this->startAt = Carbon::parse($this->course->minimum_start_at);
        }
        $this->startAt = $this->startAt->startOfDay();
        $this->endsAt  = $this->startAt->copy()->addDays($this->course->duration)->startOfDay();
    }
}
