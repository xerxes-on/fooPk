<?php

namespace App\Enums\Admin\Client\Filters;

use App\Http\Traits\EnumToArray;

/**
 * Enum for client formular filtering.
 *
 * @package App\Enums\Admin\Client
 */
enum ClientFormularFilterEnum: int
{
    use EnumToArray;

    case MISSING = -1;

    case NOT_APPROVED = 0;

    case APPROVED = 1;
}
