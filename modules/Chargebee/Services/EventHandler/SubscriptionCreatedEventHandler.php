<?php

namespace Modules\Chargebee\Services\EventHandler;
class SubscriptionCreatedEventHandler extends SubscriptionBaseEventHandler
{
    public function handle()
    {
        $this->service->handleSubscriptionCreated($this->eventData);
    }
}