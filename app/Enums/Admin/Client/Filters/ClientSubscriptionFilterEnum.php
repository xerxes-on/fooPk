<?php

namespace App\Enums\Admin\Client\Filters;

use App\Http\Traits\EnumToArray;

/**
 * Enum for client Subscription filtering.
 *
 * @package App\Enums\Admin\Client
 */
enum ClientSubscriptionFilterEnum: int
{
    use EnumToArray;

    case MISSING = 0;

    case EXIST = 1;
}
