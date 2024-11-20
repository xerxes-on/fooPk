<?php

namespace Modules\Chargebee\Services\EventHandler;

class SubscriptionCreatedWithBackdatingEventHandler extends SubscriptionBaseEventHandler
{
    public function handle()
    {
        $this->service->handleSubscriptionCreated($this->eventData);
    }
}