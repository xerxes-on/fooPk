<?php

namespace Modules\Chargebee\Services\EventHandler;

class SubscriptionReactivatedEventHandler extends SubscriptionBaseEventHandler
{
    public function handle()
    {
        $this->service->handleSubscriptionCreated($this->eventData, false);
    }
}