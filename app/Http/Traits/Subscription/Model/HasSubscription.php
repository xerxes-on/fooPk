<?php

declare(strict_types=1);

namespace App\Http\Traits\Subscription\Model;

use App\Models\UserSubscription;
use App\Services\Subscription\UserSubscriptionCreationService;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasSubscription
{
    /**
     * Get all user subscriptions
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Get user  last subscription
     */
    public function getLatestSubscription(): ?UserSubscription
    {
        return $this->subscriptions()->latest()->first();
    }

    /**
     * Get active user subscriptions
     */
    public function activeSubscriptions(): HasMany
    {
        return $this->subscriptions()->where('active', true);
    }

    /**
     * Get Subscription
     */
    public function getSubscriptionAttribute(): ?UserSubscription
    {
        return $this->relationLoaded('activeSubscriptions') ?
            $this->getRelation('activeSubscriptions')->first() :
            $this->activeSubscriptions()->first();
    }

    /**
     * Create user new Subscription
     */
    public function createSubscription(): void
    {
        app(UserSubscriptionCreationService::class)->create($this);
    }

    /**
     * Create user new Subscription with specific conditions
     */
    public function maybeCreateSubscription(): void
    {
        app(UserSubscriptionCreationService::class)->maybeCreate($this);
    }
}
