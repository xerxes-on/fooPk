<?php

namespace Modules\PushNotification\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Neko\Stapler\ORM\EloquentTrait;
use Neko\Stapler\ORM\StaplerableInterface;

/**
 * NotificationType Model.
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string|null $icon_file_name
 * @property int|null $icon_file_size
 * @property string|null $icon_content_type
 * @property string|null $icon_updated_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property boolean $is_important Flag marking the notification as important to be dispatched
 * @property-read \Modules\PushNotification\Models\Notification|null $notification
 * @property-read \Neko\Stapler\Attachment $icon
 * @method static \Database\Factories\NotificationTypeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationType query()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationType whereIconContentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationType whereIconFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationType whereIconFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationType whereIconUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationType whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationType whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationType whereIsImportant($value)
 * @mixin \Eloquent
 */
final class NotificationType extends Model implements StaplerableInterface
{
    use HasFactory;
    use EloquentTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notification_types';

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_important' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'slug',
        'name',
        'icon',
        'is_important',
    ];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile(
            'icon',
            [
                'styles' => [
                    'icon'  => '100x100',
                    'thumb' => '150x150#',
                ],
                'url'         => '/uploads/notifications/:id/:style/:filename',
                'default_url' => '/images/icons/logo_icon.png'
            ]
        );

        parent::__construct($attributes);
    }

    /**
     * Get all current attributes of the model.
     *
     * Allows to correctly obtain attributes for STAPLER package as it collects
     *
     * @see https://github.com/CodeSleeve/laravel-stapler/issues/64#issuecomment-338445440
     * @note Must not be removed.
     * @return array
     */
    public function getAttributes()
    {
        return parent::getAttributes();
    }

    /**
     * Relation to Notification model.
     *
     * @return BelongsTo<Notification, NotificationType>
     */
    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }
}
