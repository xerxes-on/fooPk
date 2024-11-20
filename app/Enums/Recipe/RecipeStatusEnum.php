<?php

declare(strict_types=1);

namespace App\Enums\Recipe;

use App\Http\Traits\EnumNameToString;
use App\Http\Traits\EnumToArray;

/**
 * Enum determining recipe status.
 *
 * @package App\Enums\Recipe
 */
enum RecipeStatusEnum: int
{
    use EnumToArray;
    use EnumNameToString;

    case DRAFT    = 0;
    case ACTIVE   = 1;
    case OUTDATED = 2;

    public function isDraft(): bool
    {
        return $this === self::DRAFT;
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isOutdated(): bool
    {
        return $this === self::OUTDATED;
    }
}
