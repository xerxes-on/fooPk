<?php

declare(strict_types=1);

namespace Modules\PushNotification\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\{JsonResponse, RedirectResponse};
use Modules\Course\Enums\UserCourseStatus;
use Modules\Course\Models\Course;
use Modules\PushNotification\DTO\PushNotificationOptions;
use Modules\PushNotification\Enums\UserGroupOptionEnum;
use Modules\PushNotification\Exceptions\NoUsersForSelectedCriteria;
use Modules\PushNotification\Http\Requests\Admin\{NotificationRequest};
use Modules\PushNotification\Http\Requests\Admin\NotificationConfigRequest;
use Modules\PushNotification\Jobs\SendPushNotifications;
use Modules\PushNotification\Models\Notification as NotificationModel;

/**
 * Controller for Notification.
 *
 * @package Modules\PushNotification\Admin
 */
final class NotificationAdminController extends Controller
{
    public function store(NotificationRequest $request): RedirectResponse
    {
        NotificationModel::updateOrCreate(['id' => $request->id], $request->validated());

        $message = is_null($request->id) ? 'record_created_successfully' : 'record_updated_successfully';
        return redirect()
            ->back()
            ->with('success_message', trans('common.' . $message));
    }

    /**
     * Notification configuration view for SweetAlert via Ajax request
     * @throws \Throwable
     */
    public function getConfigForm(int $notificationId): string
    {
        return view('PushNotification::admin.config', [
            'id'           => $notificationId,
            'courses'      => Course::active()->get(['id'])->pluck('title', 'id'),
            'courseStatus' => UserCourseStatus::forSelect()
        ])->render();
    }

    /**
     * Dispatch notification job.
     */
    public function sendNotification(NotificationConfigRequest $request): JsonResponse
    {
        try {
            $this->dispatch(
                new SendPushNotifications(
                    $request->id,
                    new PushNotificationOptions(
                        $request->params[UserGroupOptionEnum::NAME],
                        [],
                        $request->input('params.course.id') > 0 ? (int)$request->params['course']['id'] : null,
                        $request->input('params.course.status') > 0 ? (int)$request->params['course']['status'] : null
                    )
                )
            );
            NotificationModel::whereId($request->id)->update(['dispatched' => true]);
        } catch (NoUsersForSelectedCriteria $e) {
            return $this->sendError(null, $e->getMessage());
        }

        return $this->sendResponse(null, trans('PushNotification::admin.notification_dispatched_success'));
    }
}
