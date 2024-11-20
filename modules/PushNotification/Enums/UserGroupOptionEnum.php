<?php

namespace Modules\PushNotification\Enums;

enum UserGroupOptionEnum: string
{
    public const NAME    = 'user_group';
    public const DEFAULT = self::DE;

    case ALL = 'all';
    case EN  = 'en';
    case DE  = 'de';
}
