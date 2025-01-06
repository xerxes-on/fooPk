<?php

declare(strict_types=1);

namespace Modules\Ingredient\Enums;

use App\Http\Traits\EnumToArray;

enum IngredientAlternativeUnit: int
{
    use EnumToArray;

    case TEASPOON   = 7;
    case TABLESPOON = 8;
    case PIECES     = 9;
    case TUBS       = 10;
    case CANS       = 11;
    case SLICES     = 12;
    case PACKAGES   = 13;
}
