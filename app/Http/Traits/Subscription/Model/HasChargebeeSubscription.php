<?php

declare(strict_types=1);

namespace App\Http\Traits\Subscription\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Modules\Chargebee\Models\ChargebeeSubscription;
use Modules\Chargebee\Services\ChargebeeService;

trait HasChargebeeSubscription
{
    /**
     * relation get chargebeeSubscriptions
     */
    public function chargebeeSubscriptions(): HasMany
    {
        return $this->hasMany(ChargebeeSubscription::class, 'user_id');
    }

    /**
     * relation get assigned chargebeeSubscriptions
     */
    public function assignedChargebeeSubscriptions(): HasMany
    {
        return $this->hasMany(ChargebeeSubscription::class, 'assigned_user_id');
    }

    /**
     * Return last Chargebee subscription
     * TODO: move to SubscriptionManager service
     */
    public function getLastChargebeeSubscriptionItem(): null|ChargebeeSubscription
    {
        $subscriptions = $this->assignedChargebeeSubscriptions;

        if ($subscriptions->isEmpty()) {
            return null;
        }

        return $subscriptions
            ->filter(
                fn($item) => empty($item->data['cancelled_at'])
            )
            ->map(
                function ($item) {
                    $item->activated_at = null;
                    $item->started_at   = null;
                    if (!empty($item->data['activated_at'])) {
                        $item->activated_at = Carbon::parse($item->data['activated_at']);
                    }
                    if (!empty($item->data['started_at'])) {
                        $item->started_at = Carbon::parse($item->data['started_at']);
                    }
                    return $item;
                }
            )
            // TODO:: review, could be possible issue
            ->sortBy('started_at')
            ->last();
    }

    /**
     * Return last chargebee plan id
     * TODO: move to SubscriptionManager service
     * TODO:: @NickMost refactor that ASAP
     * TODO: need to define typehint
     */
    public function getLastChargebeePlanId()
    {
        $chargebeePlanId           = false;
        $lastChargebeeSubscription = $this->getLastChargebeeSubscriptionItem();
        if (
            !is_null($lastChargebeeSubscription)
            &&
            isset($lastChargebeeSubscription->data) &&
            (
                !empty($lastChargebeeSubscription->data['plan_id'])
                ||
                !empty($lastChargebeeSubscription->data['subscription_items'])
            )
        ) {
            $chargebeePlanId = ChargebeeService::getChargebeePlanIdFromSubscriptionData($lastChargebeeSubscription->data);
        }
        return $chargebeePlanId;
    }

    /**
     * Method for setup custom price for challenge for user based on chargebee plan id etc.
     *
     * TODO:: move to UserService
     */
    public function _prepareCoursesForUser(Collection $courses): Collection
    {
        $userChargebeePlanId = $this->getLastChargebeePlanId();

        if (empty($userChargebeePlanId) || $courses->isEmpty()) {
            return $courses;
        }

        //TODO:: simplify it @NickMost, related to special challenge costs and prices
        $challengesConfig = config('course.special_costs');
        if (empty($challengesConfig) && !is_array($challengesConfig)) {
            return $courses;
        }
        $specialPricesForChallenges = array_keys($challengesConfig);

        return $courses->map(
            function ($challenge, $key) use ($specialPricesForChallenges, $challengesConfig, $userChargebeePlanId) {
                // exists special price for challenge config
                if (
                    in_array($challenge->id, $specialPricesForChallenges) &&
                    !empty($challengesConfig[$challenge->id]['chargebee_plans'][$userChargebeePlanId])
                ) {
                    // exists special price for user with selected chargebeePlanId
                    $challenge->foodpoints = $challengesConfig[$challenge->id]['chargebee_plans'][$userChargebeePlanId];
                }

                return $challenge;
            }
        );
    }
}
