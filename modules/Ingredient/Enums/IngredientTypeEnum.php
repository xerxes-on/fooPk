<?php

namespace Modules\Ingredient\Enums;

use App\Http\Traits\EnumToArray;

enum IngredientTypeEnum: string
{
    use EnumToArray;

    case FIXED    = 'fixed';
    case VARIABLE = 'variable';
}
