<?php

namespace Modules\PushNotification\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Notification Translation model.
 *
 * @property int $id
 * @property int $notification_id
 * @property string $locale
 * @property string $title
 * @property string $content
 * @property string|null $link_title Title of the link
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTranslation whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTranslation whereLinkTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTranslation whereNotificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTranslation whereTitle($value)
 * @mixin \Eloquent
 */
final class NotificationTranslation extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['title', 'content', 'link_title'];
}
