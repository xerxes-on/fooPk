<?php

declare(strict_types=1);

namespace Modules\Chargebee\Enums;

enum ChargebeeSubscriptionType: string
{
    case PLAN   = 'plan';
    case CHARGE = 'charge';
}
