<?php

namespace Modules\Chargebee\Services\EventHandler;

class SubscriptionReactivatedWithBackdatingEventHandler extends SubscriptionBaseEventHandler
{
    public function handle()
    {
        $this->service->handleSubscriptionCreated($this->eventData, false);
    }
}