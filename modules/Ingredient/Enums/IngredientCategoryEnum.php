<?php

namespace Modules\Ingredient\Enums;

use App\Http\Traits\EnumToArray;

enum IngredientCategoryEnum: int
{
    use EnumToArray;

    case SPICES = 1; // TODO: spices is duplicated, 80 is duplicated

    case SEASON = 79;
}
