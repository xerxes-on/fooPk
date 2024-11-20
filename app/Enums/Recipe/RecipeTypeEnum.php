<?php

namespace App\Enums\Recipe;

use App\Http\Traits\EnumNameToString;
use App\Http\Traits\EnumToArray;
use App\Models\CustomRecipe;
use App\Models\Recipe;
use InvalidArgumentException;
use Modules\FlexMeal\Models\FlexmealLists;

/**
 * Enum determining recipe types.
 *
 * @package App\Enums
 */
enum RecipeTypeEnum: int
{
    use EnumToArray;
    use EnumNameToString;

    case ORIGINAL = 1;

    case CUSTOM = 2;

    case FLEXMEAL = 3;

    public static function tryFromClass(string $recipeInstanceClass): RecipeTypeEnum
    {
        return match ($recipeInstanceClass) {
            Recipe::class        => self::ORIGINAL,
            CustomRecipe::class  => self::CUSTOM,
            FlexmealLists::class => self::FLEXMEAL,
            default              => throw new InvalidArgumentException('Unknown Mealtime'),
        };
    }
}
