<?php

namespace Modules\Ingredient\Enums;

use App\Http\Traits\EnumToArray;

enum IngredientCategoryEnum: int
{
    use EnumToArray;

    case SPICES = 1;

    case SEASON = 79;
}
