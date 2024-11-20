<?php

namespace Modules\PushNotification\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Notification model.
 *
 * @property int $id
 * @property int $type_id
 * @property string|null $link Link to some custom page
 * @property bool $dispatched Notification dispatch status
 * @property array|null $report Notification dispatch status report
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\PushNotification\Models\NotificationTranslation|null $translation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Modules\PushNotification\Models\NotificationTranslation> $translations
 * @property-read int|null $translations_count
 * @property-read \Modules\PushNotification\Models\NotificationType|null $type
 * @method static \Database\Factories\NotificationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Notification listsTranslations(string $translationField)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification notTranslatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification orWhereTranslation(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification orWhereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification orderByTranslation(string $translationField, string $sortMethod = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|Notification query()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification translated()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification translatedIn(?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereDispatched($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereReport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereTranslation(string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereTranslationLike(string $translationField, $value, ?string $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification withTranslation()
 * @mixin \Eloquent
 */
final class Notification extends Model implements TranslatableContract
{
    use HasFactory;
    use Translatable;

    /**
     * Related translatable attributes.
     */
    public array $translatedAttributes = ['title', 'content', 'link_title'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notification';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['type_id', 'link', 'dispatched'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'dispatched' => 'boolean',
        'report'     => 'array',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['translations'];

    /**
     * Notification type relation
     *
     * @return HasOne<NotificationType>
     */
    public function type(): HasOne
    {
        return $this->hasOne(NotificationType::class, 'id', 'type_id');
    }
}
