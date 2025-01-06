<?php

declare(strict_types=1);

namespace Modules\Course\Enums;

use App\Http\Traits\EnumToArray;

enum CourseId: int
{
    use EnumToArray;

    case TBR              = 1;
    case TBF              = 6;
    case QUICK_GUIDE_DE   = 9;
    case TBR2             = 15;
    case TBR2022          = 19;
    case QUICK_GUIDE_EN   = 30;
    case TBR2023          = 24;
    case BOOTCAMP         = 17;
    case SUGAR_DETOX_2021 = 16;
    case SUGAR_DETOX      = 20;
    case HAPPY_BELLY      = 22;
    case LONGEVITY        = 26;
    case TBR2024_DE       = 27;
    case TBR2024_EN       = 28;
    case SPORT            = 29;
    case SEELENHUNGER     = 33;
    case DE_30_DAYS       = 2;
    case EN_30_DAYS       = 25;

    public static function getFirstTimeChallengeId(?string $lang = null): int
    {
        return $lang === 'en' ? self::QUICK_GUIDE_EN->value : self::QUICK_GUIDE_DE->value;
    }

    public static function isGuide(int $courseId): bool
    {
        return match ($courseId) {
            self::QUICK_GUIDE_DE->value, self::QUICK_GUIDE_EN->value => true,
            default => false,
        };
    }

    public static function getTBRDiscountRange(): array
    {
        return [self::TBR->value, self::TBR2->value, self::TBR2022->value, self::TBR2023->value, self::TBR2024_EN->value];
    }
}
