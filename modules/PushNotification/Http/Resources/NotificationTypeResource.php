<?php

declare(strict_types=1);

namespace Modules\PushNotification\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of a Notification type.
 *
 * @property-read \Modules\PushNotification\Models\NotificationType $resource
 *
 * @used-by \Modules\PushNotification\Http\Resources\NotificationResource::toArray()
 * @package Modules\PushNotification
 */
final class NotificationTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->resource->id,
            'slug'         => $this->resource->slug,
            'name'         => $this->resource->name,
            'is_important' => $this->resource->is_important,
            'icon'         => asset($this->resource->icon->url()),
        ];
    }
}
