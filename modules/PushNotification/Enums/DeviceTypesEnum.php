<?php

namespace Modules\PushNotification\Enums;

use App\Http\Traits\EnumToArray;

enum DeviceTypesEnum: string
{
    use EnumToArray;

    case ANDROID = 'android';
    case IOS     = 'ios';
}
