<?php

declare(strict_types=1);

namespace Modules\Chargebee\Enums;

/**
 * @see https://apidocs.chargebee.com/docs/api/subscriptions
 */
enum ChargebeeSubscriptionStatus: string
{
    case FUTURE       = 'future';
    case IN_TRIAL     = 'in_trial';
    case ACTIVE       = 'active';
    case NON_RENEWING = 'non_renewing';
    case PAUSED       = 'paused';
    case CANCELLED    = 'cancelled';
    case TRANSFERRED  = 'transferred';

    public static function renewableStatus(): array
    {
        return [
            self::FUTURE->value,
            self::IN_TRIAL->value,
            self::ACTIVE->value,
            self::PAUSED->value,
        ];
    }

    public static function potentiallyActiveStatus(): array
    {
        return [
            self::FUTURE->value,
            self::IN_TRIAL->value,
            self::ACTIVE->value,
            self::NON_RENEWING->value,
            self::CANCELLED->value,
        ];
    }
}
