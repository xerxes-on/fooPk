<?php

declare(strict_types=1);

namespace Modules\Course\Service;

use App\Exceptions\NoData;
use App\Models\User;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Cache;
use Modules\Course\Models\Course;

/**
 * Article service.
 *
 * @package Modules\Course\Service
 */
final class ArticleService
{
    public const COURSE_ARTICLE_CACHE_KEY = 'course-%d-articles';
    public const ARTICLE_CACHE_KEY        = 'article-%s';

    /**
     * Retrieve list of courses related articles.
     * @note due to the fact that we are using WP API, we need to fetch all articles for separate courses.
     */
    public function getAllForUserCourses(User $user): array
    {
        $courses             = Course::getUserCourses($user);
        $coursesWithArticles = [];

        if (is_null($courses) || $courses->count() === 0) {
            return $coursesWithArticles;
        }

        foreach ($courses as $aboChallenge) {
            /**@var Course $aboChallenge */
            $coursesWithArticles[] = $aboChallenge->getCourseArticle();
        }

        return $coursesWithArticles;
    }

    /**
     * Get articles for one particular course.
     * @throws NoData
     */
    public function getArticlesForCourse(User $user, int $id): array
    {
        $course = Course::getSpecific($user, $id)->first();

        if (is_null($course)) {
            throw new NoData(trans('course::common.not_found'));
        }

        return $course->getCourseArticle();
    }

    /**
     * Get an article with provided ID and day of challenge.
     */
    public function getSpecific(User $user, int|string $wpArticleId, int|string $days): SupportCollection|null
    {
        $course = $user
            ->courses()
            ->leftJoin(
                'course_articles',
                'courses.id',
                '=',
                'course_articles.course_id'
            )
            ->where('course_articles.wp_article_id', $wpArticleId)
            ->where('course_articles.days', $days)
            ->first();

        $exists = !is_null($course) && $course->getActiveDays() >= $days;

        return $exists ? $this->getPost((int)$wpArticleId) : null;
    }

    /**
     * Get single cached post.
     */
    private function getPost(string|int $articleID): SupportCollection|null
    {
        $posts = Cache::get($this->getArticlesCacheKey($articleID));
        if (!empty($posts)) {
            return $posts;
        }

        $posts = WpApi::getPost($articleID);
        Cache::put($this->getArticlesCacheKey($articleID), $posts, config('cache.lifetime_day'));
        return $posts;
    }

    public function getCoursesArticlesContent(array $articleIDs, int $modelID): SupportCollection|null
    {
        $posts = Cache::get($this->getCourseArticlesCacheKey($modelID));
        if (!empty($posts)) {
            return $posts;
        }

        $posts = WpApi::getPosts($articleIDs);
        Cache::put($this->getCourseArticlesCacheKey($modelID), $posts, config('cache.lifetime_day'));
        return $posts;
    }

    private function getCourseArticlesCacheKey(int $modelID): string
    {
        return sprintf(self::COURSE_ARTICLE_CACHE_KEY, $modelID);
    }

    private function getArticlesCacheKey(int $articleID): string
    {
        return sprintf(self::ARTICLE_CACHE_KEY, $articleID);
    }
}
