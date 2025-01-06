<?php

declare(strict_types=1);

namespace Modules\Chargebee\Enums;

use App\Http\Traits\EnumToArray;

/**
 * Enum for client ChargebeeSubscription filtering.
 *
 * @package App\Enums\Admin\Client
 */
enum ClientChargebeeSubscriptionFilter: int
{
    use EnumToArray;

    case MISSING = 0;

    case EXIST           = 1;
    case MULTIPLE_ACTIVE = 2;
}
