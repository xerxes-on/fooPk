<?php

use Modules\Chargebee\Services\EventHandler\PaymentRefundedEventHandler;
use Modules\Chargebee\Services\EventHandler\PaymentSucceededEventHandler;
use Modules\Chargebee\Services\EventHandler\SubscriptionActivatedEventHandler;
use Modules\Chargebee\Services\EventHandler\SubscriptionActivatedWithBackdatingEventHandlerEventHandler;
use Modules\Chargebee\Services\EventHandler\SubscriptionCanceledWithBackdatingEventHandler;
use Modules\Chargebee\Services\EventHandler\SubscriptionCancelledEventHandler;
use Modules\Chargebee\Services\EventHandler\SubscriptionChangedEventHandler;
use Modules\Chargebee\Services\EventHandler\SubscriptionChangedWithBackdatingEventHandler;
use Modules\Chargebee\Services\EventHandler\SubscriptionCreatedEventHandler;
use Modules\Chargebee\Services\EventHandler\SubscriptionCreatedWithBackdatingEventHandler;
use Modules\Chargebee\Services\EventHandler\SubscriptionDeletedEventHandler;
use Modules\Chargebee\Services\EventHandler\SubscriptionMovedInEventHandler;
use Modules\Chargebee\Services\EventHandler\SubscriptionPausedEventHandler;
use Modules\Chargebee\Services\EventHandler\SubscriptionReactivatedEventHandler;
use Modules\Chargebee\Services\EventHandler\SubscriptionReactivatedWithBackdatingEventHandler;
use Modules\Chargebee\Services\EventHandler\SubscriptionRenewedEventHandler;
use Modules\Chargebee\Services\EventHandler\SubscriptionResumedEventHandler;
use Modules\Chargebee\Services\EventHandler\SubscriptionStartedEventHandler;
use Modules\Chargebee\Services\EventHandler\CustomerChangedEventHandler;

return [
    'handlers' => [

        // withdraw foodpoints for refund payment action
        'payment_refunded' => [
            'handler' => PaymentRefundedEventHandler::class,
            'delay' => 120,
        ],
        // assigning foodpoints to user
        'payment_succeeded' => [
            'handler' => PaymentSucceededEventHandler::class,
            'delay' => 10,
        ],

        'subscription_activated' => [
            'handler' => SubscriptionActivatedEventHandler::class,
            'delay' => 10,
        ],

        'subscription_activated_with_backdating' => [
            'handler' => SubscriptionActivatedWithBackdatingEventHandlerEventHandler::class,
            'delay' => 10,
        ],

        'subscription_canceled_with_backdating' => [
            'handler' => SubscriptionCanceledWithBackdatingEventHandler::class,
            'delay' => 20,
        ],
        'subscription_cancelled' => [
            'handler' => SubscriptionCancelledEventHandler::class,
            'delay' => 20,
        ],
        'subscription_changed' => [
            'handler' => SubscriptionChangedEventHandler::class,
            'delay' => 120,
        ],
        'subscription_changed_with_backdating' => [
            'handler' => SubscriptionChangedWithBackdatingEventHandler::class,
            'delay' => 120,
        ],
        'subscription_created' => [
            'handler' => SubscriptionCreatedEventHandler::class,
            'delay' => 0,
        ],
        'subscription_created_with_backdating' => [
            'handler' => SubscriptionCreatedWithBackdatingEventHandler::class,
            'delay' => 0,
        ],

        'subscription_deleted' => [
            'handler' => SubscriptionDeletedEventHandler::class,
            'delay' => 5,
        ],

        'subscription_moved_in' => [
            'handler' => SubscriptionMovedInEventHandler::class,
            'delay' => 120,
        ],

        'subscription_reactivated' => [
            'handler' => SubscriptionReactivatedEventHandler::class,
            'delay' => 0,
        ],
        'subscription_reactivated_with_backdating' => [
            'handler' => SubscriptionReactivatedWithBackdatingEventHandler::class,
            'delay' => 0,
        ],
        'subscription_renewed' => [
            'handler' => SubscriptionRenewedEventHandler::class,
            'delay' => 10,
        ],

        'subscription_started' => [
            'handler' => SubscriptionStartedEventHandler::class,
            'delay' => 20,
        ],
        'subscription_paused' => [
            'handler' => SubscriptionPausedEventHandler::class,
            'delay' => 20,
        ],
        'subscription_resumed' => [
            'handler' => SubscriptionResumedEventHandler::class,
            'delay' => 20,
        ],
        'customer_changed'=>[
            'handler' => CustomerChangedEventHandler::class,
            'delay' => 30,

        ]
    ],

    'create_user_plan' => array_values(json_decode(env('CHARGEBEE_USER_PLANS_NEW', '[]'), true)??[]),
    'create_silent_plan' => array_values(json_decode(env('CHARGEBEE_SILENT_PLANS_NEW', '[]'), true)??[]),
    'adminNotificationEmails' => explode(',', env('CHARGEBEE_IMPORT_NOTIFICATION_EMAIL', 'info@foodpunk.de')),
    'challenges_config' => json_decode(env('CHARGEBEE_CHALLENGES_CONFIG_JSON', ''), true),
    'foodpoints' => json_decode(env('CHARGEBEE_FOODPOINTS_CONFIG_JSON', ''), true),

];