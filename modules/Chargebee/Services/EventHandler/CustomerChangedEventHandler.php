<?php

namespace Modules\Chargebee\Services\EventHandler;

use App\Models\User;
use Modules\Chargebee\Services\ChargebeeService;
class CustomerChangedEventHandler extends BaseEventHandler
{
    public function handle()
    {
        $customerEmail = data_get($this->eventData, 'content.customer.email');
        $this->user = !empty($customerEmail) ? User::ofEmail($customerEmail)->first() : null;
        if (!empty($this->user)){
            // TODO:: refactor, syncSubscriptionsData set $this->user
//            $this->service->refreshUserSubscriptionData($this->user);
//            $this->service->syncSubscriptionsData($this->eventData);
            $this->service->subscriptionChangedEvent($this->user);

        }

    }
}