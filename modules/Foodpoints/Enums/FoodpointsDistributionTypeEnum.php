<?php


namespace Modules\Foodpoints\Enums;

use App\Http\Traits\EnumToArray;

enum FoodpointsDistributionTypeEnum: int
{
    use EnumToArray;

    case REGULAR_WEEKLY = 1;
    case REGULAR_MONTHLY = 2;
}
