<?php

declare(strict_types=1);

namespace Modules\PushNotification\Http\Controllers\API;

use App\Http\Controllers\API\APIBase;
use Illuminate\Http\{JsonResponse, Request};
use Modules\PushNotification\Http\Requests\API\NotificationStatusRequest;
use Modules\PushNotification\Http\Resources\NotificationResource;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * API controller for users push notifications.
 *
 * @package Modules\PushNotification\API
 */
final class PushNotificationApiController extends APIBase
{
    /**
     * Mark notification as read
     *
     * @route POST /api/v1/user-notification/read
     */
    public function setReadStatus(NotificationStatusRequest $request): JsonResponse
    {
        // Update only if not updated already
        if (!$request->notification->is_read) {
            $request->notification->is_read = true;
            $request->notification->save();
            return $this->sendResponse(null, trans('common.success'));
        }

        return $this->sendError(
            [
                'id'              => $request->notification->id,
                'notification_id' => $request->notification->notification_id,
                'status'          => $request->notification->is_read
            ],
            trans('common.error'),
            ResponseAlias::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    /**
     * Mark all notifications as read
     *
     * @route POST /api/v1/user-notification/read-all
     */
    public function setReadStatusToAll(Request $request): JsonResponse
    {
        if ($request->user()?->pushNotifications()->update(['is_read' => true])) {
            return $this->sendResponse(null, trans('common.success'));
        }

        return $this->sendError(message: trans('common.no_data'));
    }

    /**
     * List all of notifications
     *
     * @route GET /api/v1/user-notification/list
     */
    public function getList(Request $request): JsonResponse
    {
        $collection = NotificationResource::collection(
            $request
                ->user()
                ?->notificationsContent()
                ->with('type')
                ->select(
                    'notification.*',
                    'users_notifications.id as notification_record_id',
                    'users_notifications.user_id as laravel_through_key',
                    'users_notifications.is_read',
                    'users_notifications.created_at as dispatched_at',
                )
                ->orderBy('is_read')
                ->orderByDesc('dispatched_at')
                ->get()
        );

        return $collection->count() > 0 ?
            $this->sendResponse($collection, trans('common.success')) :
            $this->sendResponse([], trans('common.no_data'));
    }
}
