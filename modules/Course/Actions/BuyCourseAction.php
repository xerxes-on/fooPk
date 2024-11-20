<?php

declare(strict_types=1);

namespace Modules\Course\Actions;

use App\Http\Traits\CanSendJsonResponse;
use App\Models\User;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Modules\Course\Http\Requests\BuyCourseRequest;
use Modules\Course\Models\Course;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Action handling purchase of a course.
 *
 * @package Modules\Course\Actions
 */
class BuyCourseAction
{
    use CanSendJsonResponse;

    private ?Course $course;
    private Carbon $startAt;
    private Carbon $endsAt;

    public function handle(BuyCourseRequest $request): JsonResponse
    {
        $link = config('adding-new-recipes.purchase_url');
        if (is_null($link)) {
            return $this->sendError('', 'Purchase url is not configured');
        }

        $user = $request->user();

        try {
            $this->getCourse((int)$request->challengeId, $user);
        } catch (\Throwable $e) {
            return $this->sendError('', trans('course::common.not_found'));
        }

        $foodpoint = $this->course->getActualPrice($user->courses()->pluck('course_id'));
        if ($foodpoint > 0 && !$user->canWithdraw($foodpoint)) {
            return $this->sendError(['link' => ['url' => $link, 'text' => 'Shop']], trans('course::common.insufficient_funds'), ResponseAlias::HTTP_PAYMENT_REQUIRED);
        }

        try {
            $this->setupDurationDates($request->startDate);
        } catch (\Throwable $e) {
            logError($e);
            return $this->sendError('', trans('course::common.date_parsing_error'));
        }

        try {
            $user->courses()
                ->attach(
                    $this->course,
                    [
                        'start_at' => $this->startAt,
                        'ends_at'  => $this->endsAt
                    ]
                );
        } catch (\Throwable $e) {
            logError($e); //TODO: investigate
        }

        // TODO: maybe here should be a correct condition to use this
        $message = trans('common.success');
        try {
            $this->maybeWithdrawFromUser($user, $foodpoint, $this->course->id);
            $this->notifyAdmin($user, $this->course->title, $foodpoint);
        } catch (ExceptionInterface $e) {
            logError($e);
            $message = $e->getMessage();
        }

        return $this->sendResponse(null, $message);
    }

    private function getCourse(int $challengeId, User $user): void
    {
        $this->course = $user->_prepareCoursesForUser(collect([Course::findOrFail($challengeId)]))->first();
        if (is_null($this->course)) {
            throw new ModelNotFoundException('');
        }
    }

    private function setupDurationDates(string $startDate): void
    {
        $startAt = Carbon::parse($startDate);
        if (!empty($this->course->minimum_start_at) && $startAt->lt($this->course->minimum_start_at)) {
            $startAt = $this->course->minimum_start_at;
        }
        $this->startAt = $startAt instanceof Carbon ? $startAt->startOfDay() : Carbon::parse($startAt)->startOfDay();
        $this->endsAt  = $this->startAt->copy()->addDays($this->course->duration)->startOfDay();
    }

    private function notifyAdmin(User $user, string $courseTitle, int $foodpoint): void
    {
        $startAtDateForNotification = $this->startAt->copy()->format('d.m.Y');

        send_raw_admin_email(
            "User $user->email (#$user->id) has Added the $courseTitle Challenge.\nStart date: $startAtDateForNotification \nFoodpoints: $foodpoint",
            'Challenge has been Added!'
        );
    }

    /**
     * @throws ExceptionInterface
     */
    private function maybeWithdrawFromUser(User $user, int $foodpoint, int $courseId): void
    {
        if ($foodpoint > 0) {
            $user->withdraw($foodpoint, ['description' => "Purchase of Challenge #$courseId"]);
        }
    }
}
