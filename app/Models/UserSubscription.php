<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * User Subscription model.
 *
 * @property int $id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $ends_at
 * @property bool $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription whereEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSubscription whereUserId($value)
 * @mixin \Eloquent
 */
final class UserSubscription extends Model
{

    protected $table = 'user_subscriptions';

    protected $fillable = [
        'user_id',
        'ends_at',
        'active',
    ];

    protected $casts = [
        'ends_at' => 'datetime',
        'active'  => 'boolean',
    ];


    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id');
    }

    /**
     * Disable subscription for user
     */
    public function stopSubscription(bool $setCurrentDate = true): void
    {
        if ($setCurrentDate) {
            $this->ends_at = Carbon::now();
        }
        $this->active = false;
        $this->save();
    }
}
