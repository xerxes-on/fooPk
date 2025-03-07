<?php

declare(strict_types=1);

namespace Modules\Course\Traits;

use App\Helpers\CacheKeys;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Course\Enums\CourseId;
use Modules\Course\Models\Course;

trait HasCourse
{
    public function courses(): BelongsToMany
    {
        return $this
            ->belongsToMany(Course::class, 'course_users', 'user_id', 'course_id')
            ->withPivot('id', 'start_at', 'counter', 'ends_at');
    }

    public function getCourseAttribute(): ?Course
    {
        return $this->courses()->orderBy('pivot_ends_at', 'desc')->first();
    }

    public function getParticipatedCourseIds(): Collection
    {
        $courses = Cache::get(CacheKeys::userParticipatedCourses($this->getKey()));

        if (!empty($courses)) {
            return $courses;
        }

        $courses = $this->courses()->pluck('course_id');
        Cache::put(CacheKeys::userParticipatedCourses($this->getKey()), $courses, config('cache.lifetime_short'));

        return $courses;
    }

    /**
     * get Course Data
     */
    public function getCourseData(): ?array
    {
        if (!$this->isQuestionnaireExist()) {
            return null;
        }

        $course = $this->course;
        if (is_null($course)) {
            return null;
        }

        $isActive = $course->getActiveDays();
        if (
            !empty($course) &&
            !empty($course->duration) &&
            ($isActive > $course->duration)
        ) {
            return null;
        }

        return [
            'from'     => parseDateString($course->pivot->start_at, 'd.m.Y'),
            'to'       => parseDateString($course->pivot->ends_at, 'd.m.Y'),
            'curDay'   => $isActive,
            'duration' => $course->duration,
            'title'    => $course->title
        ];
    }

    public function courseExists(int $courseId = 0): bool
    {
        return $this->courses()->where('course_id', $courseId)->exists();
    }


    /**
     * Set first user challenge QUICK_GUIDE_CHALLENGE, there are introduction, manual etc.
     */
    public function setFirstTimeCourse(): void
    {
        $challengeId               = CourseId::getFirstTimeChallengeId();
        $existsQuickGuideChallenge = $this->courseExists($challengeId);

        if (!$existsQuickGuideChallenge) {
            $course = Course::findOrFail($challengeId);

            $now     = Carbon::now();
            $startAt = $now->subDays($course->duration);
            $this->addCourse($challengeId, $startAt);
        }
    }

    public function addCourseIfNotExists(int $challengeId = 0, $startAt = null): bool
    {
        $existsQuickGuideChallenge = $this->courseExists($challengeId);
        if (!$existsQuickGuideChallenge) {
            $this->addCourse($challengeId, $startAt);
        }
        return !$existsQuickGuideChallenge;
    }

    /**
     * Set challenge
     * @param int $challengeId
     * @param string|null $startAt
     */
    public function addCourse(int $challengeId = 0, $startAt = null): void
    {
        $course = Course::findOrFail($challengeId);

        if (is_null($startAt)) {
            $startAt = Carbon::now()->startOfDay();

            switch ($challengeId) {
                // requirements from task WEB-291
                // requirements from task WEB-415 for CHALLENGES_CHALLENGE_HAPPY_BELLY_ID
                // requirements from task WEB-546 for CHALLENGES_CHALLENGE_TBR2024_ID
                case CourseId::TBR2023->value:
                case CourseId::BOOTCAMP->value:
                case CourseId::HAPPY_BELLY->value:
                case CourseId::TBR2024_DE->value:
                case CourseId::TBR2024_EN->value:
                    $startAt = Carbon::now()->addDays(3)->startOfDay();
                    break;
                    // requirements from task WEB-513
                case CourseId::SEELENHUNGER->value:
                    $startAt = Carbon::now()->addDays(2)->startOfDay();
                    break;
                    // requirements from task WEB-846
                case CourseId::LONGEVITY->value:
                    $startAt = Carbon::now()->startOfDay();
                    break;
            }
        }

        if (!empty($course->minimum_start_at) && $startAt < $course->minimum_start_at) {
            $startAt = Carbon::parse($course->minimum_start_at)->startOfDay();
        }

        $endsAt = $startAt->copy()->addDays($course->duration)->startOfDay();

        $this->courses()
            ->attach(
                $course,
                [
                    'start_at' => $startAt,
                    'ends_at'  => $endsAt
                ]
            );
    }
}
