<?php

namespace App\Enums\Admin\Client\Filters;

use App\Http\Traits\EnumToArray;

/**
 * Enum for client Consultant filtering.
 *
 * @package App\Enums\Admin\Client
 */
enum ClientConsultantFilterEnum: string
{
    use EnumToArray;

    case NOT_PRESENT = 'missing';

    case PRESENT = 'any';
}
