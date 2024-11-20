<?php

namespace Modules\Chargebee\Services\EventHandler;

use ChargeBee\ChargeBee\Environment as ChargeBee_Environment;
use Exception;
use Log;
use Modules\Chargebee\Services\ChargebeeService;


class BaseEventHandler
{

    public $eventData;
    public $eventType;
    public $eventId;

    public ChargebeeService $service;

    public function __construct($eventData)
    {

        $this->configureEnvironment();

        $this->eventData = $eventData;
        //Validate type, id, check duplication
        if (!$this->eventType = $this->eventData['event_type'] ?? false) {
            throw new Exception('No chargebee event type provided');
        }
        if (!$this->eventId = $this->eventData['id'] ?? false) {
            throw new Exception('No chargebee event id provided');
        }

        $this->service = app(ChargebeeService::class);
    }

    /**
     * Configure chargebee API access credentials
     * @throws Exception
     */
    public function configureEnvironment()
    {
        if (config('app.chargebee.site') && config('app.chargebee.auth_user')) {
            ChargeBee_Environment::configure(config('app.chargebee.site'), config('app.chargebee.auth_user'));
        } else {
            Log::error('Chargebee API access credentials does not configured');
            throw new Exception('Chargebee API access credentials does not configured');
        }
    }

    public function handle()
    {
    }
}