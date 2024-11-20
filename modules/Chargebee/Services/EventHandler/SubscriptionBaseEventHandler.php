<?php

namespace Modules\Chargebee\Services\EventHandler;

use Exception;
use Modules\Chargebee\Services\ChargebeeService;

class SubscriptionBaseEventHandler extends BaseEventHandler
{

    public function __construct($eventData)
    {

        parent::__construct($eventData);

        $subscription = data_get($this->eventData, 'content.subscription');
        if (empty($subscription)) {
            throw new Exception('Chargebee event failed '.self::class.', empty subscription, eventId:' . $this->eventData['id']);
        }
    }

    public function handle()
    {
        $this->service->syncSubscriptionsData($this->eventData);
    }
}