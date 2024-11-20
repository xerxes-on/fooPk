<?php

declare(strict_types=1);

namespace Modules\PushNotification\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of a Notification.
 * It uses data of aggregated table, be aware of data structure.
 *
 * @property int $id
 * @property int $notification_record_id Record id in users_notifications table
 * @property-read \Modules\PushNotification\Models\NotificationType $type
 * @property string $title
 * @property string $content
 * @property string|null $link
 * @property string|null $link_title
 * @property int $is_read
 * @property string $dispatched_at
 * @property-read \Modules\PushNotification\Models\Notification $notification
 *
 * @used-by \Modules\PushNotification\Http\Controllers\API\PushNotificationApiController::getList()
 * @package Modules\PushNotification
 */
final class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->notification_record_id,
            'notification_id' => $this->id,
            'type'            => new NotificationTypeResource($this->type),
            'title'           => $this->title,
            'content'         => $this->content,
            'link'            => $this->link ?
                [
                    'url'   => $this->link,
                    'title' => $this->link_title
                ] :
                null,
            'is_read'       => (bool)$this->is_read,
            'dispatched_at' => Carbon::parse($this->dispatched_at)->timestamp,
        ];
    }
}
