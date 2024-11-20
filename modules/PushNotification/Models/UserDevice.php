<?php

declare(strict_types=1);

namespace Modules\PushNotification\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserDevice Model.
 *
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property string $type Device types
 * @property string $os_version
 * @property string $app_version
 * @property string $fingerprint Device unique identifier/fingerprint
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\UserDeviceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|UserDevice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserDevice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserDevice query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserDevice whereAppVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDevice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDevice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDevice whereOsVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDevice whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDevice whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDevice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDevice whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDevice whereFingerprint($value)
 * @mixin \Eloquent
 */
final class UserDevice extends Model
{
    use HasFactory;
    use Prunable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_devices';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'user_id',
        'token',
        'type',
        'os_version',
        'app_version',
        'fingerprint',
    ];

    /**
     * @return Builder<UserDevice>
     */
    public function prunable(): Builder
    {
        return self::where('updated_at', '<', now()->subMonths(2));
    }

    /**
     * Notification type relation
     *
     * @return BelongsTo<User,UserDevice>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
