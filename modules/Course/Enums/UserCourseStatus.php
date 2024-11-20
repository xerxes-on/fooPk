<?php

declare(strict_types=1);

namespace Modules\Course\Enums;

use App\Http\Traits\EnumNameToString;
use App\Http\Traits\EnumToArray;

enum UserCourseStatus: int
{
    use EnumNameToString;
    use EnumToArray;

    case IN_PROGRESS   = 1;
    case FINISHED      = 2;
    case NOT_STARTED   = 3;
    case NOT_PURCHASED = 4;
}
