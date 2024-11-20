<?php

namespace Modules\Chargebee\Services\EventHandler;

class SubscriptionCancelledEventHandler extends SubscriptionBaseEventHandler
{
    public function handle()
    {
        $this->service->handleSubscriptionCanceled($this->eventData);
    }
}