<?php

namespace Modules\Chargebee\Enums\Admin\Client\Filters;

use App\Http\Traits\EnumToArray;

/**
 * Enum for client ChargebeeSubscription filtering.
 *
 * @package App\Enums\Admin\Client
 */
enum ClientChargebeeSubscriptionFilterEnum: int
{
    use EnumToArray;

    case MISSING = 0;

    case EXIST = 1;
    case MULTIPLE_ACTIVE = 2;
}
