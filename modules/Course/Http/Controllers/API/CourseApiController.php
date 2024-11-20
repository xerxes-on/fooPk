<?php

declare(strict_types=1);

namespace Modules\Course\Http\Controllers\API;

use App\Exceptions\NoData;
use App\Http\Controllers\API\APIBase;
use Illuminate\Http\{JsonResponse, Request};
use Modules\Course\Actions\BuyCourseAction;
use Modules\Course\Actions\RescheduleCourseAction;
use Modules\Course\Http\Requests\BuyCourseRequest;
use Modules\Course\Http\Requests\CourseRescheduleRequest;
use Modules\Course\Http\Resources\CourseResource;
use Modules\Course\Models\Course;
use Modules\Course\Models\CourseArticle;

/**
 * Course API controller.
 *
 * @package Modules\Course\Http\Controllers\API
 */
final class CourseApiController extends APIBase
{
    /**
     * List all available courses to purchase.
     *
     * @route GET /api/v1/challenges/purchase
     */
    public function listPurchasable(Request $request): JsonResponse
    {
        $challenges = Course::getPurchasable($request->user());
        $collection = CourseResource::collection($challenges);

        return $collection->count() === 0 ?
            $this->sendError('No active challenge', trans('course::common.no_courses')) :
            $this->sendResponse($collection, trans('common.success'));
    }

    /**
     * List all user purchased course.
     *
     * @route GET /api/v1/challenges
     */
    public function listPurchased(Request $request): JsonResponse
    {
        return $this->sendResponse(
            CourseResource::collection(Course::getUserCourses($request->user())),
            trans('common.success')
        );
    }

    /**
     * List articles that relates to specific course.
     *
     * @route GET /api/v1/challenges/{id}/articles
     */
    public function listArticles(Request $request, int $id): JsonResponse
    {
        try {
            $data     = CourseArticle::getCourseArticles($request->user(), $id);
            $response = collect($data['articles'])->values();
            return $response->isEmpty() ?
                $this->sendError(trans('course::common.article_not_available'), trans('course::common.list_articles_empty')) :
                $this->sendResponse($response, trans('common.success'));
        } catch (NoData $e) {
            return $this->sendError($e->getMessage());
        }
    }

    /**
     * Get article be requested id and a day of challenge.
     *
     * @route GET /api/v1/challenges/articles/{id}/{days}
     */
    public function getArticle(Request $request, int $id, int $days): JsonResponse
    {
        $response = CourseArticle::getSpecific($request->user(), $id, $days);
        return empty($response) ?
            $this->sendError(trans('course::common.article_not_available'), trans('course::common.article_not_available')) :
            $this->sendResponse($response, trans('common.success'));
    }

    /**
     * Buy selected course.
     *
     * @route POST /api/v1/challenges/purchase
     */
    public function buy(BuyCourseRequest $request, BuyCourseAction $action): JsonResponse
    {
        return $action->handle($request);
    }

    /**
     * Buy selected article.
     *
     * @route POST /api/v1/challenges/reschedule
     */
    public function reschedule(CourseRescheduleRequest $request, RescheduleCourseAction $action): JsonResponse
    {
        return $action->handle($request);
    }
}
