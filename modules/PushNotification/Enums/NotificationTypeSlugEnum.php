<?php

namespace Modules\PushNotification\Enums;

use App\Http\Traits\EnumToArray;

enum NotificationTypeSlugEnum: string
{
    use EnumToArray;

    case  FOODPOINTS_DISTRIBUTION_WEEKLY = 'foodpoints_weekly';
    case  FOODPOINTS_DISTRIBUTION_MONTHLY = 'foodpoints_monthly';

}
