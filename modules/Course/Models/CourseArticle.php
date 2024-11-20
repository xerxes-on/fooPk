<?php

declare(strict_types=1);

namespace Modules\Course\Models;

use App\Exceptions\NoData;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Modules\Course\Events\CourseArticleProcessedEvent;
use Modules\Course\Service\ArticleService;

/**
 * Course Article Model.
 *
 * @property int $course_id
 * @property int $wp_article_id
 * @property int $days
 * @method static \Illuminate\Database\Eloquent\Builder|CourseArticle newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseArticle newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseArticle query()
 * @method static \Illuminate\Database\Eloquent\Builder|CourseArticle whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseArticle whereDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CourseArticle whereWpArticleId($value)
 * @mixin \Eloquent
 */
final class CourseArticle extends Model
{
    public $table = 'course_articles';

    public $timestamps = false;

    protected $fillable = ['wp_article_id', 'days'];

    protected $casts = [
        'wp_article_id' => 'int',
        'days'          => 'int',
    ];

    protected $dispatchesEvents = [
        'saved'    => CourseArticleProcessedEvent::class,
        'deleting' => CourseArticleProcessedEvent::class,
    ];

    public static function getAllForUserCourses(User $user): array
    {
        return app(ArticleService::class)->getAllForUserCourses($user);
    }

    /**
     * @throws NoData
     */
    public static function getCourseArticles(User $user, int $courseID): array
    {
        return app(ArticleService::class)->getArticlesForCourse($user, $courseID);
    }

    /**
     * @return Collection<int,Course>|null
     */
    public static function getSpecific(User $user, int|string $wpArticleId, int|string $days): Collection|null
    {
        return app(ArticleService::class)->getSpecific($user, $wpArticleId, $days);
    }
}
