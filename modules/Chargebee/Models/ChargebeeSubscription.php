<?php

namespace Modules\Chargebee\Models;

use App\Models\User;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Modules\Chargebee\Models\ChargebeeSubscription
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $assigned_user_id
 * @property array $data
 * @property array|null $payment_method
 * @property string $uuid
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $assignedClient
 * @property-read User $owner
 * @method static Builder|ChargebeeSubscription newModelQuery()
 * @method static Builder|ChargebeeSubscription newQuery()
 * @method static Builder|ChargebeeSubscription query()
 * @method static Builder|ChargebeeSubscription whereAssignedUserId($value)
 * @method static Builder|ChargebeeSubscription whereCreatedAt($value)
 * @method static Builder|ChargebeeSubscription whereData($value)
 * @method static Builder|ChargebeeSubscription whereId($value)
 * @method static Builder|ChargebeeSubscription wherePaymentMethod($value)
 * @method static Builder|ChargebeeSubscription whereUpdatedAt($value)
 * @method static Builder|ChargebeeSubscription whereUserId($value)
 * @method static Builder|ChargebeeSubscription whereUuid($value)
 * @mixin Eloquent
 */
final class ChargebeeSubscription extends Model
{

    public const PROCESSED = true;
    public const NOT_PROCESSED = false;

    public const STATUS_FUTURE = 'future';
    public const STATUS_IN_TRIAL = 'in_trial';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_NON_RENEWING = 'non_renewing';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_CANCELED = 'canceled';
    public const STATUS_TRANSFERRED = 'transferred';
    /**
     * Key used to save next billing amount to the chargebee subscription data
     *
     * @var string
     */
    public const NEXT_BILLING_AMOUNT_KEY = 'next_billing_amount';

    protected $fillable = [
        'data',
        'uuid',
        'user_id',
        'assigned_user_id',
        'payment_method',
    ];

    protected $casts = [
        'data' => 'array',
        'payment_method' => 'array',
    ];

    /**
     * User which has chargebee account to which belongs this subscription
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * User to which this subscription was manually assigned
     */
    public function assignedClient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }
}
