<?php

declare(strict_types=1);

namespace Modules\Course\Enums;

enum CourseStatus: int
{
    case DRAFT  = 0;
    case ACTIVE = 1;

    public static function forSelect(): array
    {
        return [
            self::DRAFT->value  => trans('common.draft'),
            self::ACTIVE->value => trans('common.active'),
        ];
    }
}
