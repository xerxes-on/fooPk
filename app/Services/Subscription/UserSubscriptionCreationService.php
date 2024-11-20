<?php

declare(strict_types=1);

namespace App\Services\Subscription;

use App\Enums\ChargeBeeSubscriptionStatusEnum;
use Modules\Chargebee\Models\ChargebeeSubscription;
use App\Models\User;
use App\Models\UserSubscription;
use Carbon\Carbon;

final class UserSubscriptionCreationService
{
    public function create(User $model): void
    {
        $this->close($model);

        UserSubscription::create(
            [
                'user_id'      => $model->id,
                'active'       => true,
            ]
        );
    }

    /**
     * Create new subscription for user with specific conditions.
     *
     * New subscription will be created only if user has no active subscription or subscription is outdated
     * or will be outdated by the end of the current day.
     */
    public function maybeCreate(User $model): void
    {
        $activeSubscription = $model->subscription;
        $endsToday          = (bool)$activeSubscription?->ends_at?->lt(now()->endOfDay());

        try {
            $chargebee = $model->chargebeeSubscriptions()
                ->where(function ($query) {
                    $query->whereJsonContains('data->status', ChargeBeeSubscriptionStatusEnum::FUTURE->value)
                        ->orWhereJsonContains('data->status', ChargeBeeSubscriptionStatusEnum::IN_TRIAL->value)
                        ->orWhereJsonContains('data->status', ChargeBeeSubscriptionStatusEnum::ACTIVE->value)
                        ->orWhereJsonContains('data->status', ChargeBeeSubscriptionStatusEnum::NON_RENEWING->value);

                })->get(['data'])->map(function (ChargebeeSubscription $item) {
                    if (isset($item['next_billing_at'])) {
                        return strtotime($item['next_billing_at']);
                    }
                })->sort()->last();
            $endInFuture = (bool)$activeSubscription?->ends_at?->lt(Carbon::parse($chargebee)->endOfDay());
        } catch (\Throwable) {
            $endInFuture = false;
        }

        if (empty($activeSubscription) || $endsToday || $endInFuture) {
            $this->create($model);
        }
    }

    private function close(User $user): void
    {
        $subscription = $user->subscription;

        if (is_null($subscription)) {
            return;
        }
        $subscription->ends_at = now();
        $subscription->active  = false;
        $subscription->save();
    }
}
