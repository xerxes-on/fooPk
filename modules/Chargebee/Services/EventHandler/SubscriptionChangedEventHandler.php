<?php

namespace Modules\Chargebee\Services\EventHandler;

class SubscriptionChangedEventHandler extends SubscriptionBaseEventHandler
{

    public function handle()
    {
        // TODO:: refactor, syncSubscriptionsData set $this->user
//        $this->service->syncSubscriptionsData($this->eventData);
        $this->user = $this->service->getUserBySubscriptionData($this->eventData);
        if (!empty($this->user)){
//            $this->service->refreshUserSubscriptionData($this->user);
            $this->service->subscriptionChangedEvent($this->user);
        }
    }

}