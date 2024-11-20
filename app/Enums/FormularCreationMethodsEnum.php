<?php

namespace App\Enums;

use App\Http\Traits\EnumNameToString;
use App\Http\Traits\EnumToArray;

/**
 * Enum determining methods of formular creation.
 *
 * @package App\Enums\Admin\Client
 */
enum FormularCreationMethodsEnum: int
{
    use EnumToArray;
    use EnumNameToString;

    public const DEFAULT = self::UNKNOWN;

    case UNKNOWN = 0;

    case FREE = 1;

    case PAID = 2;
}
