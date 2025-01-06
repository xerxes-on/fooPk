<?php

namespace Modules\Chargebee\Services\EventHandler;

use Exception;
use Modules\Chargebee\Services\ChargebeeService;

class SubscriptionBaseEventHandler extends BaseEventHandler
{

    public $user;
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
        // TODO:: refactor, syncSubscriptionsData set $this->user
        $this->service->syncSubscriptionsData($this->eventData);
        $this->user = $this->service->getUserBySubscriptionData($this->eventData);
    }
}