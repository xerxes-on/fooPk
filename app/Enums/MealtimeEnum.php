<?php

namespace App\Enums;

use App\Http\Traits\EnumNameToString;
use App\Http\Traits\EnumToArray;
use InvalidArgumentException;

/**
 * Enum determining available mealtime types.
 *
 * @package App\Enums
 */
enum MealtimeEnum: int
{
    use EnumToArray;
    use EnumNameToString;

    case BREAKFAST = 1;

    case LUNCH = 2;

    case DINNER = 3;

    case SNACK = 4; // Exist in DB, but not used right now

    public static function tryToGetLowerNameFromValue(string|int $mealtime): string
    {
        return self::tryFromValue($mealtime)->lowerName();
    }

    public static function getExchangeableTypes(): array
    {
        return [self::LUNCH->value, self::DINNER->value];
    }

    public static function tryFromValue(string|int $mealtime): MealtimeEnum
    {
        if (is_int($mealtime)) {
            return match ($mealtime) {
                self::BREAKFAST->value => self::BREAKFAST,
                self::LUNCH->value     => self::LUNCH,
                self::DINNER->value    => self::DINNER,
                self::SNACK->value     => self::SNACK,
                default                => throw new InvalidArgumentException('Unknown Mealtime'),
            };
        }
        return match (strtolower($mealtime)) {
            'breakfast' => self::BREAKFAST,
            'lunch'     => self::LUNCH,
            'dinner'    => self::DINNER,
            'snack'     => self::SNACK,
            default     => throw new InvalidArgumentException('Unknown Mealtime'),
        };
    }
}
