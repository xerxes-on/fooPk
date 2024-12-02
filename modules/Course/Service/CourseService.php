<?php

declare(strict_types=1);

namespace Modules\Course\Service;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection as SupportCollection;
use Modules\Course\Enums\CourseId;
use Modules\Course\Enums\UserCourseStatus;
use Modules\Course\Models\Course;
use Modules\Course\Models\CourseArticle;

final class CourseService
{
    public function getActiveDays(Course $model): int
    {
        $now     = Carbon::now();
        $startAt = Carbon::parse($model->pivot->start_at);
        $endsAt  = Carbon::parse($model->pivot->ends_at);
        $result  = 0;

        if ($now->gt($startAt) && $now->lt($endsAt)) {
            $result = $startAt->diffInDays($now, false) + 1;
        }

        if ($now->gt($endsAt)) {
            $result = $now->diffInDays($startAt, true);
        }

        return $result;
    }

    public function getStatus(Course $model): UserCourseStatus
    {
        $courseIsActive = $this->getActiveDays($model);

        if ($courseIsActive > $model->duration) {
            return UserCourseStatus::FINISHED;
        }

        if ($courseIsActive > 0) {
            return UserCourseStatus::IN_PROGRESS;
        }

        return UserCourseStatus::NOT_STARTED;
    }

    /**
     * Retrieve all available challenges.
     * @return EloquentCollection<int,Course>
     */
    public function all(?User $user = null): EloquentCollection
    {
        $courses = Course::active()->get();

        if ($user === null) {
            $user = \Auth::user();
        }
        if ($user !== null) {
            //added filter TBR2023
            $courses = $user->_prepareCoursesForUser($courses);
        }

        return $courses;
    }

    /**
     * Return all available challenges for purchase
     * @return EloquentCollection<int,Course>
     */
    public function purchasable(User $user): EloquentCollection
    {
        $userCourses = $user->getParticipatedCourseIds()->toArray();
        return $this->all($user)->filter(static fn(Course $course) => !in_array($course->id, $userCourses));
    }

    /**
     * Get single Challenge by its ID.
     */
    public function getSpecific(User $user, int $id): SupportCollection
    {
        //added filter TBR2023
        return $user->_prepareCoursesForUser($user->courses()->where('courses.id', $id)->get());
    }

    public function getActualCoursePrice(Course $model, SupportCollection $userCourses): int
    {
        $foodpoints = (int)$model->foodpoints;
        if ($foodpoints === 0) {
            return 0;
        }
        if ($model->id === CourseId::TBR2024_DE->value && $userCourses->intersect(CourseId::getTBRDiscountRange())->isNotEmpty()) {
            return 0;
        }

        if ($model->id === CourseId::BOOTCAMP->value && $userCourses->contains(CourseId::TBF->value)) {
            return config('course::main.bootcamp_and_fitness_discount', $foodpoints);
        }

        if ($model->id === CourseId::SUGAR_DETOX->value && $userCourses->contains(CourseId::SUGAR_DETOX_2021->value)) {
            return config('course::main.sugar_detox_discount', $foodpoints);
        }

        return $foodpoints;
    }

    /**
     * Get user challenges.
     */
    public function getUserCourses(User $user): SupportCollection
    {
        //added filter TBR2023
        return $user->_prepareCoursesForUser($user->courses()->orderBy('pivot_ends_at', 'desc')->get());
    }

    /**
     * get Articles For Challenge
     */
    public function getCourseArticles(Course $model): array
    {
        # get article IDs
        $articleIDs = CourseArticle::where('course_id', $model->id)->pluck('wp_article_id', 'days')->toArray();
        # get articles content by IDs
        $articleContent = app(ArticleService::class)->getCoursesArticlesContent(array_values($articleIDs), $model->id);

        $articleData = [];
        if (!is_null($articleContent)) {
            foreach ($articleIDs as $days => $articleID) {
                if ($articleContent->has($articleID)) {
                    $current                          = $articleContent->get($articleID);
                    $current['post_unlock']           = $this->getActiveDays($model) >= $days;
                    $articleData[$days]               = $current;
                    $articleData[$days]['day_number'] = $days; // Added for API purposes
                }
            }
            uksort($articleData, static fn(int $a, int $b) => $a <=> $b);
        }
        return array_merge(
            $model->only(
                [
                    'id',
                    'title',
                    'duration',
                    'foodpoints',
                    'description',
                    'status',
                    'image_file_name',
                    'start_at',
                    'ends_at'
                ]
            ),
            ['articles' => $articleData]
        );
    }
}
