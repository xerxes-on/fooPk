<?php

declare(strict_types=1);

namespace Modules\Ingredient\Enums;

use App\Http\Traits\EnumToArray;

enum IngredientUnitType: int
{
    use EnumToArray;

    case PRIMARY   = 1;
    case SECONDARY = 2;

    public static function getNameFor(int $value): string
    {
        return match ($value) {
            self::PRIMARY->value   => trans('ingredient::admin.type.' . strtolower(self::PRIMARY->name)),
            self::SECONDARY->value => trans('ingredient::admin.type.' . strtolower(self::SECONDARY->name)),
            default                => ''
        };
    }
}
