<?php

namespace App\Enums\User;

enum UserStatusEnum: int
{
    case ACTIVE = 1;

    case INACTIVE = 0;
}
