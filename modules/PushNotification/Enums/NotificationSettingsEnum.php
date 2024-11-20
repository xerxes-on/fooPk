<?php

namespace Modules\PushNotification\Enums;

use App\Http\Traits\EnumToArray;

enum NotificationSettingsEnum: string
{
    use EnumToArray;

    case ALL       = 'all';
    case IMPORTANT = 'important';
    case DISABLE   = 'off';
}
