<?php

declare(strict_types=1);

namespace Modules\Chargebee\Models;

use App\Models\User;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\Chargebee\Enums\ChargebeeSubscriptionType;


/**
 * Modules\Chargebee\Models\ChargebeeSubscription
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $assigned_user_id
 * @property array $data
 * @property array|null $payment_method
 * @property string|null $uuid
 * @property string|null $status
 * @property string|null $processed Flag to indicate that item processed and corrected after 20231229
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $assignedClient
 * @property-read string|null $subscription_name
 * @property-read User $owner
 * @method static Builder|ChargebeeSubscription newModelQuery()
 * @method static Builder|ChargebeeSubscription newQuery()
 * @method static Builder|ChargebeeSubscription query()
 * @method static Builder|ChargebeeSubscription whereAssignedUserId($value)
 * @method static Builder|ChargebeeSubscription whereCreatedAt($value)
 * @method static Builder|ChargebeeSubscription whereData($value)
 * @method static Builder|ChargebeeSubscription whereId($value)
 * @method static Builder|ChargebeeSubscription wherePaymentMethod($value)
 * @method static Builder|ChargebeeSubscription whereProcessed($value)
 * @method static Builder|ChargebeeSubscription whereStatus($value)
 * @method static Builder|ChargebeeSubscription whereUpdatedAt($value)
 * @method static Builder|ChargebeeSubscription whereUserId($value)
 * @method static Builder|ChargebeeSubscription whereUuid($value)
 * @mixin Eloquent
 */
final class ChargebeeSubscription extends Model
{
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
        'data'           => 'array',
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

    public function getSubscriptionNameAttribute(): ?string
    {
        $data = data_get($this->data, 'plan_id');
        if (is_string($data) && !empty($data)) {
            return $data;
        }

        $data = data_get($this->data, 'subscription_items'); // should be a type of array or null
        if (empty($data)) {
            return null;
        }

        $data = is_array($data) ? $data : (array)$data;
        $data = array_filter($data, static fn(array $item) => data_get($item, 'item_type') === ChargebeeSubscriptionType::PLAN->value);

        if (empty($data[0]['item_price_id'])) {
            return null;
        }

        return (string)$data[0]['item_price_id'];
    }
}
